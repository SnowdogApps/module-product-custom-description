<?php

namespace Snowdog\CustomDescription\Model;

use Magento\Framework\Model\AbstractModel;

class CustomDescription extends AbstractModel {

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Snowdog\CustomDescription\Model\Resource\CustomDescription');
    }

}