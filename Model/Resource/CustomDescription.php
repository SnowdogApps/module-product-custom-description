<?php

namespace Snowdog\CustomDescription\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Snowdog\CustomDescription\Model\Resource\CustomDescription\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class CustomDescription
 *
 * @package Snowdog\CustomDescription\Model\Resource
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class CustomDescription extends AbstractDb
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Define main table and key
     */
    protected function _construct()
    {
        $this->_init('snowdog_custom_description', 'entity_id');
    }

    /**
     * CustomDescription constructor.
     *
     * @param Context $context
     * @param $collectionFactory $categoryCollectionFactory
     * @param null|string $connectionName
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);

        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get custom description list form a given product id
     *
     * @param $productId
     * @return \Snowdog\CustomDescription\Model\Resource\CustomDescription\Collection
     */
    public function getCustomDescriptionByProductId($productId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'product_id',
            (int)$productId
        );

        return $collection;
    }
}
