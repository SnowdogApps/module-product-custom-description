<?php

namespace Snowdog\CustomDescription\Model\Resource;

use Magento\Framework\App\ResourceConnection;
use Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface;

/**
 * Class CustomDescriptionBatchProcessor
 * @package Snowdog\CustomDescription\Model\Resource
 */
class CustomDescriptionBatchProcessor
{
    const SNOWDOG_CUSTOM_DESCRIPTION_TABLE = 'snowdog_custom_description';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $entities = [];

    /**
     * CustomDescriptionBatchProcessor constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param $entity
     */
    public function persist(CustomDescriptionInterface $entity)
    {
        $this->entities[] = $entity;
    }

    /**
     * @return array
     */
    public function getEntities() : array
    {
        return $this->entities;
    }

    /**
     * @return int
     */
    public function flush()
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName(self::SNOWDOG_CUSTOM_DESCRIPTION_TABLE);

        foreach ($this->entities as $entity) {
            $item = $entity->getData();
            $connection->insertOnDuplicate($tableName, $item);
        }
    }
}
