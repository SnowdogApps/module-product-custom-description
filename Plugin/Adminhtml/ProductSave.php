<?php

namespace Snowdog\CustomDescription\Plugin\Adminhtml;

use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Snowdog\CustomDescription\Api\CustomDescriptionRepositoryInterface;
use Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface;
use Snowdog\CustomDescription\Helper\Data;
use Snowdog\CustomDescription\Model\CustomDescriptionFactory;
use Snowdog\CustomDescription\Model\Resource\CustomDescriptionBatchProcessor;

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
     * ProductSave constructor.
     *
     * @param CustomDescriptionBatchProcessor $descBatchProcessor
     * @param ManagerInterface $messageManager
     * @param AdapterFactory $adapterFactory
     * @param UploaderFactory $uploader
     * @param Filesystem $filesystem
     * @param RequestInterface $request
     * @param Registry $registry
     * @param CustomDescriptionFactory $customDescFactory
     * @param CustomDescriptionRepositoryInterface $customDescRepo
     * @param Data $helper
     * @param File $file
     *
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
        $this->customDescriptionFactory = $customDescFactory;
        $this->customDescRepo = $customDescRepo;
        $this->helper = $helper;
        $this->file = $file;
        $this->descriptionBatchProcessor = $descBatchProcessor;
    }

    /**
     * @param Save $subject
     * @param $result
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Save $subject, $result)
    {
        $product = $this->registry->registry('current_product');
        $params = $this->request->getParams();
        $customDescData = isset($params['product']['descriptions'])
            ? $params['product']['descriptions']
            : false;

        if (empty($product) || !is_array($customDescData)) {
            return $result;
        }

        $productId = $product->getId();

        foreach ($customDescData as $detDesc) {
            if (!$this->validateCustomDescData($detDesc)) {
                continue;
            }

            $item = $this->initItem($detDesc);

            if ($this->removeOrExcludeInvalidItem($item, $detDesc)) {
                continue;
            }

            $item = $this->setItemData($item, $detDesc, $productId);
            $this->descriptionBatchProcessor->persist($item);
        }

        $this->descriptionBatchProcessor->flush();

        return $result;
    }

    /**
     * @param CustomDescriptionInterface $item
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function removeImageFromItem(CustomDescriptionInterface $item)
    {
        if ($this->helper->isExistingImage($item->getImage())) {
            $fullPath = $this->helper->getImageFullPath($item->getImage());
            $this->file->deleteFile($fullPath);
        }
    }

    /**
     * Validate description data
     *
     * @param $customDescData
     *
     * @return bool
     */
    private function validateCustomDescData($customDescData)
    {
        return $customDescData
            && !empty($customDescData['description'])
            && !empty($customDescData['title']);
    }

    /**
     * @param $item
     * @return \Exception
     */
    private function removeItem($item)
    {
        try {
            $this->removeImageFromItem($item);
            $this->customDescRepo->delete($item);
        } catch (\Exception $e) {
            $this->messageManager
                ->addErrorMessage(__("Couldn't remove item correctly " . $e->getMessage()));
        }
    }

    /**
     * @param $item
     * @param $detDesc
     * @param $productId
     * @return \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface
     */
    private function setItemData(CustomDescriptionInterface $item, $detDesc, $productId)
    {
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

    /**
     * @param $detDesc
     * @return CustomDescriptionInterface
     */
    private function initItem($detDesc)
    {
        if (empty($detDesc['entity_id'])) {
            return $this->customDescriptionFactory->create();
        }

        return $this->customDescRepo->get($detDesc['entity_id']);
    }

    /**
     * @param CustomDescriptionInterface $item
     * @param $detDesc
     * @return bool
     */
    private function removeOrExcludeInvalidItem(CustomDescriptionInterface $item, $detDesc)
    {
        if (!empty($item->getId()) && !empty($detDesc['is_delete'])) {
            $this->removeItem($item);
            return true;
        }

        if (empty($detDesc['file']) && empty($item->getId())) {
            $this->messageManager
                ->addErrorMessage(
                    __("Couldn't save description {$detDesc['description_id']}. Image upload failed.")
                );
            return true;
        }

        return false;
    }
}
