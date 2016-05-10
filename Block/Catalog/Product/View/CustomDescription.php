<?php

namespace Snowdog\CustomDescription\Block\Catalog\Product\View;

use Magento\Catalog\Block\Product\View\AbstractView;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\Filesystem;

class CustomDescription extends AbstractView
{

    /**
     * @var \Snowdog\CustomDescription\Model\Resource\CustomDescription
     */
    protected $ddResourceModel;

    /**
     * CustomDescription constructor.
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param array $data
     * @param \Snowdog\CustomDescription\Model\Resource\CustomDescription $customDescription
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        array $data,
        \Snowdog\CustomDescription\Model\Resource\CustomDescription $customDescription,
        Filesystem $filesystem
    ) {
        $this->ddResourceModel = $customDescription;
        $this->filesystem = $filesystem;

        parent::__construct($context, $arrayUtils, $data);
    }

    /**
     * Get custom description for the current product
     *
     * @return mixed
     */
    public function getCustomDescription()
    {
        $currentProductId = $this->getProduct()->getId();
        $customDescription = $this->ddResourceModel
            ->getCustomDescriptionByProductId($currentProductId);

        return $customDescription;
    }

}