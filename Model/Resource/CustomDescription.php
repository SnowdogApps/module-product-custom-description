<?php

namespace Snowdog\CustomDescription\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomDescription extends AbstractDb
{

    /**
     * Define main table and key
     */
    protected function _construct()
    {
        $this->_init('snowdog_custom_description', 'entity_id');
    }

    /**
     * Get custom description list form a given product id
     *
     * @param $productId
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomDescriptionByProductId($productId)
    {
        $select = $this->getConnection()
            ->select()
            ->from([
                'cd' => $this->getMainTable()
            ])
            ->where('cd.product_id = ?', $productId)
            ->order('position ASC');

        return $this
            ->getConnection()
            ->fetchAssoc($select);
    }

}