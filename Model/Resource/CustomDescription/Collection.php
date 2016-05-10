<?php

namespace Snowdog\CustomDescription\Model\Resource\CustomDescription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model and resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Snowdog\CustomDescription\ModelCustomDescription',
            'Snowdog\CustomDescription\Model\Resource\CustomDescription'
        );
    }
}