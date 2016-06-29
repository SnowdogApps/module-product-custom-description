<?php

namespace Snowdog\CustomDescription\Block\Catalog\Product\View;

use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\ArrayUtils;
use Snowdog\CustomDescription\Model\CustomDescriptionRepository;

/**
 * Class CustomDescription
 * 
 * @package Snowdog\CustomDescription\Block\Catalog\Product\View
 */
class CustomDescription extends AbstractView
{

    protected $customDescriptionRepository;

    /**
     * CustomDescription constructor.
     *
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param array $data
     * @param CustomDescriptionRepository $customDescriptionRepository
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        array $data,
        CustomDescriptionRepository $customDescriptionRepository
    ) {
        $this->customDescriptionRepository = $customDescriptionRepository;

        parent::__construct($context, $arrayUtils, $data);
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

}