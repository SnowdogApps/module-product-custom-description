<?php

namespace Snowdog\CustomDescription\Block\Catalog\Product\View;

use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Store\Model\StoreManagerInterface;
use Snowdog\CustomDescription\Helper\Data;
use Snowdog\CustomDescription\Model\CustomDescriptionRepository;

/**
 * Class CustomDescription
 * @package Snowdog\CustomDescription\Block\Catalog\Product\View
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class CustomDescription extends AbstractView
{
    protected $_template = 'Snowdog_CustomDescription::catalog/product/view/custom-description.phtml';

    /**
     * @var CustomDescriptionRepository
     */
    private $customDescriptionRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var Data
     */
    private $helper;

    /**
     * CustomDescription constructor.
     *
     * @param CustomDescriptionRepository $customDescRepository
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param array $data
     */
    public function __construct(
        CustomDescriptionRepository $customDescRepository,
        StoreManagerInterface $storeManager,
        Data $helper,
        Context $context,
        ArrayUtils $arrayUtils,
        array $data = []
    ) {
        $this->customDescriptionRepository = $customDescRepository;

        parent::__construct($context, $arrayUtils, $data);
        $this->storeManager = $storeManager;
        $this->helper = $helper;
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
        return $this->helper->getImageUrl($image);
    }
}
