<?php

namespace Snowdog\CustomDescription\Plugin\Adminhtml;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Controller\Adminhtml\Product\Save\Interceptor;
use Magento\Backend\Model\View\Result\Redirect\Interceptor as RedirectInterceptor;
use Snowdog\CustomDescription\Model\CustomDescriptionFactory;

/**
 * Class ProductSave
 * 
 * @package Snowdog\CustomDescription\Plugin\Adminhtml
 */
class ProductSave
{

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
     * ProductSave constructor.
     *
     * @param ManagerInterface $messageManager
     * @param AdapterFactory $adapterFactory
     * @param UploaderFactory $uploader
     * @param Filesystem $filesystem
     * @param RequestInterface $request
     * @param Registry $registry
     * @param CustomDescriptionFactory $customDescriptionFactory
     */
    public function __construct(
        ManagerInterface $messageManager,
        AdapterFactory $adapterFactory,
        UploaderFactory $uploader,
        Filesystem $filesystem,
        RequestInterface $request,
        Registry $registry,
        CustomDescriptionFactory $customDescriptionFactory
    ) {
        $this->_messageManager = $messageManager;
        $this->adapterFactory = $adapterFactory;
        $this->uploader = $uploader;
        $this->filesystem = $filesystem;
        $this->request = $request;
        $this->registry = $registry;
        $this->customDescriptionFactory = $customDescriptionFactory;
    }

    /**
     * Save custom description by plugin method
     *
     * @param Interceptor $subject
     * @param RedirectInterceptor $result
     *
     * @return RedirectInterceptor
     */
    public function afterExecute(Interceptor $subject, RedirectInterceptor $result)
    {
        $params = $this->request->getParams();
        $product = $this->registry->registry('current_product');

        if ($product) {
            $productId = $product->getId();
            $customDescData = $params['product']['custom_description'];

            if (is_array($customDescData) && !empty($productId)) {
                /* @var $customDescription \Snowdog\CustomDescription\Model\CustomDescription */
                $customDescription = $this->customDescriptionFactory->create();

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

        return $result;
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
        if (isset($_FILES["product_custom_description_{$descriptionId}_image"])
            && isset($_FILES["product_custom_description_{$descriptionId}_image"]['name'])
            && strlen($_FILES["product_custom_description_{$descriptionId}_image"]['name'])
        ) {
            
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