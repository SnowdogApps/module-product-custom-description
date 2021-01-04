<?php

declare(strict_types=1);

namespace Snowdog\CustomDescription\Plugin\Adminhtml;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\ProductMetadataInterface;
use Snowdog\CustomDescription\Api\CustomDescriptionRepositoryInterface;
use Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface;
use Snowdog\CustomDescription\Helper\Data;
use Snowdog\CustomDescription\Model\CustomDescriptionFactory;
use Snowdog\CustomDescription\Model\Resource\CustomDescriptionBatchProcessor;
use Snowdog\CustomDescription\Model\Resource\CustomDescription\Collection as CustomDescriptionCollection;

/**
 * Class ProductSave
 * @package Snowdog\CustomDescription\Plugin\Adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductSave
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var UploaderFactory
     */
    private $uploader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var CustomDescriptionFactory
     */
    private $customDescriptionFactory;

    /**
     * @var CustomDescriptionRepositoryInterface
     */
    private $customDescRepo;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var File
     */
    private $file;

    /**
     * @var CustomDescriptionBatchProcessor
     */
    private $descriptionBatchProcessor;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CustomDescriptionBatchProcessor $descBatchProcessor,
        ManagerInterface $messageManager,
        AdapterFactory $adapterFactory,
        UploaderFactory $uploader,
        Filesystem $filesystem,
        RequestInterface $request,
        Registry $registry,
        ProductMetadataInterface $productMetadata,
        CustomDescriptionFactory $customDescFactory,
        CustomDescriptionRepositoryInterface $customDescRepo,
        Data $helper,
        File $file
    ) {
        $this->messageManager = $messageManager;
        $this->adapterFactory = $adapterFactory;
        $this->uploader = $uploader;
        $this->filesystem = $filesystem;
        $this->request = $request;
        $this->registry = $registry;
        $this->productMetadata = $productMetadata;
        $this->customDescriptionFactory = $customDescFactory;
        $this->customDescRepo = $customDescRepo;
        $this->helper = $helper;
        $this->file = $file;
        $this->descriptionBatchProcessor = $descBatchProcessor;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Save $subject, Redirect $result): Redirect
    {
        $product = $this->registry->registry('current_product');
        $params = $this->request->getParams();
        $customDescData = isset($params['product']['descriptions'])
            ? $params['product']['descriptions']
            : false;

        if (empty($product)) {
            return $result;
        }

        $productId = (int) $product->getId();
        $customDescCollection = $this->customDescRepo->getCustomDescriptionByProductId($productId);
        $customDescCollectionSize = $customDescCollection->getSize();

        if (!is_array($customDescData) && !$customDescCollectionSize) {
            return $result;
        }

        if (!is_array($customDescData)) {
            $this->removeAllItems($customDescCollection);
            return $result;
        }

        if ($customDescCollectionSize) {
            $customDescData = $this->getMappedCustomDescData($customDescData);
            $customDescCollection = $this->getMappedCustomDescCollection($customDescCollection);
            $this->removeToDeleteItems($customDescData, $customDescCollection);
        }

        foreach ($customDescData as $detDesc) {
            if (!$this->validateCustomDescData($detDesc)
                || ($customDescCollectionSize && !$this->hasItemChanged($detDesc, $customDescCollection))
            ) {
                continue;
            }

            $item = $this->initItem($detDesc);

            if ($this->excludeInvalidItem($item, $detDesc)) {
                continue;
            }

            $item = $this->setItemData($item, $detDesc, $productId);
            $this->descriptionBatchProcessor->persist($item);
        }

        $this->descriptionBatchProcessor->flush();

        return $result;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function removeImageFromItem(CustomDescriptionInterface $item): void
    {
        if ($this->helper->isExistingImage($item->getImage())) {
            $fullPath = $this->helper->getImageFullPath($item->getImage());
            $this->file->deleteFile($fullPath);
        }
    }

    /**
     * Validate description data
     */
    private function validateCustomDescData(array $customDescData): bool
    {
        return $customDescData
            && !empty($customDescData['description'])
            && !empty($customDescData['title']);
    }

    private function removeItem(CustomDescriptionInterface $item): void
    {
        try {
            $this->removeImageFromItem($item);
            $this->customDescRepo->delete($item);
        } catch (\Exception $e) {
            $this->messageManager
                ->addErrorMessage(__("Couldn't remove item correctly " . $e->getMessage()));
        }
    }

    private function setItemData(
        CustomDescriptionInterface $item,
        array $detDesc,
        int $productId
    ): CustomDescriptionInterface {
        $sortOrder = isset($detDesc['position']) ? $detDesc['position'] : 0;
        $file = isset($detDesc['file'][0]['file']) ? $detDesc['file'][0]['file'] : false;
        $item->setData(CustomDescriptionInterface::DESCRIPTION, $detDesc['description']);
        $item->setData(CustomDescriptionInterface::TITLE, $detDesc['title']);
        $item->setData(CustomDescriptionInterface::PRODUCT_ID, $productId);
        $item->setData(CustomDescriptionInterface::POSITION, $sortOrder);

        if ($file) {
            $item->setData(CustomDescriptionInterface::IMAGE, $file);
        }

        return $item;
    }

    private function initItem(array $detDesc): CustomDescriptionInterface
    {
        if (empty($detDesc['entity_id'])) {
            return $this->customDescriptionFactory->create();
        }

        return $this->customDescRepo->get($detDesc['entity_id']);
    }

    private function excludeInvalidItem(CustomDescriptionInterface $item, array $detDesc): bool
    {
        if (empty($detDesc['file']) && empty($item->getId())) {
            $this->messageManager
                ->addErrorMessage(
                    __("Couldn't save description {$detDesc['description_id']}. Image upload failed.")
                );
            return true;
        }

        return false;
    }

    private function removeAllItems(CustomDescriptionInterface $customDescriptionCollection): void
    {
        foreach ($customDescriptionCollection as $item) {
            $this->removeItem($item);
        }
    }

    private function removeToDeleteItems(array $customDescData, array $customDescriptionCollection): void
    {
        foreach ($customDescriptionCollection as $item) {
            if (!isset($customDescData[$item->getId()])) {
                $this->removeItem($item);
            }
        }
    }

    private function getMappedCustomDescData(array $customDescData): array
    {
        $data = [];
        foreach ($customDescData as $item) {
            $data[$item['entity_id']] = $item;
        }

        return $data;
    }

    private function getMappedCustomDescCollection(CustomDescriptionCollection $customDescCollection): array
    {
        $collection = [];
        foreach ($customDescCollection as $item) {
            $collection[$item->getId()] = $item;
        }

        return $collection;
    }

    private function hasItemChanged(array $item, array $customDescCollection): bool
    {
        if (empty($item['file'])) {
            return true;
        }

        $storeItem = $customDescCollection[$item['entity_id']];

        return $storeItem->getTitle() != $item['title']
            || $storeItem->getDescription() != $item['description']
            || $storeItem->getImage() != $item['file'][0]['file']
            || $storeItem->getPosition() != $item['position'];
    }
}
