<?php

namespace Snowdog\CustomDescription\Block\Catalog\Product\View;

use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Snowdog\CustomDescription\Model\CustomDescriptionRepository;
use Snowdog\CustomDescription\Controller\Adminhtml\File\Upload;

/**
 * Class CustomDescription
 * 
 * @package Snowdog\CustomDescription\Block\Catalog\Product\View
 */
class CustomDescription extends AbstractView
{

    /**
     * @var CustomDescriptionRepository
     */
    private $customDescriptionRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CustomDescription constructor.
     *
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param array $data
     * @param CustomDescriptionRepository $customDescriptionRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        array $data,
        CustomDescriptionRepository $customDescriptionRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->customDescriptionRepository = $customDescriptionRepository;

        parent::__construct($context, $arrayUtils, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve custom description for the current product
     *
     * @return \Snowdog\CustomDescription\Model\Resource\CustomDescription\Collection
     */
    public function getCustomDescription()
    {
        $currentProductId = $this->getProduct()->getId();
        $customDescription = $this->customDescriptionRepository
            ->getCustomDescriptionByProductId($currentProductId);

        return $customDescription;
    }

    /**
     * @param $image
     * @return string
     */
    public function getImageSrc($image)
    {
        $mediaUrl = $this->storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . Upload::IMAGES_UPLOAD;

        return $mediaUrl . $image;
    }
}
