<?php

namespace Snowdog\CustomDescription\Model\Resource\CustomDescription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Snowdog\CustomDescription\Model\Resource\CustomDescription
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Collection extends AbstractCollection
{
    /**
     * Define model and resource model
     */
    protected function _construct()
    {
        $this->_init(
            \Snowdog\CustomDescription\Model\CustomDescription::class,
            \Snowdog\CustomDescription\Model\Resource\CustomDescription::class
        );
    }
}
