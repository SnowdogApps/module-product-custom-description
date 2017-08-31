<?php

namespace Snowdog\CustomDescription\Plugin\Adminhtml;

use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\App\ObjectManager;
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

/**
 * Class ProductSave
 * 
 * @package Snowdog\CustomDescription\Plugin\Adminhtml
 *
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
     * ProductSave constructor.
     *
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
    }

    /**
     * @param Save $subject
     * @param $result
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function afterExecute(Save $subject, $result)
    {
        $params = $this->request->getParams();
        $product = $this->registry->registry('current_product');

        if ($product) {
            $productId = $product->getId();
            $customDescData = isset($params['product']['descriptions']) ? $params['product']['descriptions'] : false;

            if (is_array($customDescData) && !empty($productId)) {
                foreach ($customDescData as $detDesc) {
                    if ($this->validateCustomDescData($detDesc)) {
                        if (isset($detDesc['entity_id']) && $detDesc['entity_id']) {
                            $item = $this->customDescRepo->get($detDesc['entity_id']);

                            if (isset($detDesc['is_delete']) && $detDesc['is_delete']) {
                                try {
                                    $this->removeImageFromItem($item);
                                    $this->customDescRepo->delete($item);
                                } catch (\Exception $e) {
                                    $this->messageManager
                                        ->addErrorMessage(__("Couldn't remove item correctly " . $e->getMessage()));
                                }
                                continue;
                            }
                        } else {
                            /* @var $customDescription \Snowdog\CustomDescription\Model\CustomDescription */
                            $customDescription = $this->customDescriptionFactory->create();
                            $item = $customDescription;
                        }

                        $file = isset($detDesc['file'][0]['file']) ? $detDesc['file'][0]['file'] : false;

                        if (isset($detDesc['file']) || $item->getId()) {
                            $sortOrder = isset($detDesc['position']) ? $detDesc['position'] : 0;
                            $item->setData('description', $detDesc['description']);
                            $item->setData('title', $detDesc['title']);
                            $item->setData('product_id', $productId);
                            $item->setData('position', $sortOrder);

                            if ($file) {
                                $item->setData('image', $file);
                            }

                            try {
                                $this->customDescRepo->save($item);
                            } catch (\Exception $e) {
                                $this->messageManager
                                    ->addErrorMessage(__("Couldn't save changes on custom description " . $e->getMessage()));
                            }
                        } else {
                            $this->messageManager
                                ->addErrorMessage(__("Couldn't save description {$detDesc['description_id']}. Image upload failed."));
                        }
                    }
                }
            }
        }

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
}
