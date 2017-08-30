<?php

namespace Snowdog\CustomDescription\Plugin\Adminhtml;

use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Snowdog\CustomDescription\Api\CustomDescriptionRepositoryInterface;
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
     * @var CustomDescriptionRepositoryInterface
     */
    private $customDescRepo;

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
     * @param CustomDescriptionRepositoryInterface $customDescRepo
     */
    public function __construct(
        ManagerInterface $messageManager,
        AdapterFactory $adapterFactory,
        UploaderFactory $uploader,
        Filesystem $filesystem,
        RequestInterface $request,
        Registry $registry,
        CustomDescriptionFactory $customDescriptionFactory,
        CustomDescriptionRepositoryInterface $customDescRepo
    ) {
        $this->_messageManager = $messageManager;
        $this->adapterFactory = $adapterFactory;
        $this->uploader = $uploader;
        $this->filesystem = $filesystem;
        $this->request = $request;
        $this->registry = $registry;
        $this->customDescriptionFactory = $customDescriptionFactory;
        $this->customDescRepo = $customDescRepo;
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
                                $item->delete();
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
                                $this->_messageManager
                                    ->addErrorMessage(__("Couldn't save changes on custom description " . $e->getMessage()));
                            }
                        } else {
                            $this->_messageManager
                                ->addErrorMessage(__("Couldn't save description {$detDesc['description_id']}. Image upload failed."));
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
            && !empty($customDescData['description'])
            && !empty($customDescData['title']);
    }
}
