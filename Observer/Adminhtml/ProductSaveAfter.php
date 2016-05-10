<?php

namespace Snowdog\CustomDescription\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface ;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class ProductSaveAfter implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $_objectManager;

    /**
     * @var ManagerInterface
     */
    private $_messageManager;

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
     * ProductSaveAfter constructor.
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $messageManager
     * @param AdapterFactory $adapterFactory
     * @param UploaderFactory $uploader
     * @param Filesystem $filesystem
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ManagerInterface $messageManager,
        AdapterFactory $adapterFactory,
        UploaderFactory $uploader,
        Filesystem $filesystem
    ) {
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->adapterFactory = $adapterFactory;
        $this->uploader = $uploader;
        $this->filesystem = $filesystem;
    }

    /**
     * Execute custom description saving
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customDescData = $observer->getData('data_object')->getData('custom_description');
        $productId = $observer->getProduct()->getId();
        if (is_array($customDescData) && !empty($productId)) {
            /* @var $customDescription \Snowdog\CustomDescription\Model\CustomDescription */
            $customDescription = $this->_objectManager->create('Snowdog\CustomDescription\Model\CustomDescription');

            foreach ($customDescData as $detDesc) {
                if ($this->validateCustomDescData($detDesc)) {
                    if (isset($detDesc['description_id']) && $detDesc['description_id']) {
                        $item = $customDescription->load($detDesc['description_id']);

                        if (isset($detDesc['is_delete']) && $detDesc['is_delete']) {
                            $item->delete();
                            $item->unsetData();
                            continue;
                        }
                    } else {
                        $item = $customDescription;
                    }

                    $file = $this->uploadImage($detDesc['id'], $productId);

                    if ($file || $item->getId()) {
                        $sortOrder = isset($detDesc['sort_order']) ? $detDesc['sort_order'] : 0;
                        $item->setData('description', $detDesc['description']);
                        $item->setData('title', $detDesc['title']);
                        $item->setData('product_id', $productId);
                        $item->setData('position', $sortOrder);

                        if ($file) {
                            $item->setData('image', $file);
                        }

                        try {
                            $item->save();
                            $item->unsetData();
                        } catch (\Exception $e) {
                            $this->_messageManager->addError(__("Couldn't save changes on custom description"));
                        }
                    } else {
                        $this->_messageManager->addError(__("Couldn't save description {$detDesc['description_id']}. Image upload failed."));
                    }
                }
            }
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
            && isset($customDescData['description'])
            && !empty($customDescData['description'])
            && isset($customDescData['title'])
            && !empty($customDescData['title']);

    }

    /**
     * Upload an image for a custom description
     *
     * @param $descriptionId
     * @param $productId
     *
     * @return bool|string
     */
    private function uploadImage($descriptionId, $productId)
    {
        if (isset(
            $_FILES["product_custom_description_{$descriptionId}_image"])
            && isset($_FILES["product_custom_description_{$descriptionId}_image"]['name'])
            && strlen($_FILES["product_custom_description_{$descriptionId}_image"]['name'])
        ) {
            /*
            * Save image upload
            */
            try {
                $base_media_path = 'snowdog/customdescription/images/' . $productId . "/" . $descriptionId;
                $uploader = $this->uploader->create(
                    ['fileId' => "product_custom_description_{$descriptionId}_image"]
                );

                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $imageAdapter = $this->adapterFactory->create();
                $uploader->addValidateCallback('product', $imageAdapter, 'validateUploadFile');
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $result = $uploader->save(
                    $mediaDirectory->getAbsolutePath($base_media_path)
                );

                return $base_media_path . $result['file'];
            } catch (\Exception $e) {
                if ($e->getCode() == 0) {
                    $this->_messageManager->addError($e->getMessage());
                }

                return false;
            }
        }

        return false;
    }
}