<?php

namespace Snowdog\CustomDescription\Model;

use Magento\Framework\Model\AbstractModel;
use Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface;

/**
 * Class CustomDescription
 * @package Snowdog\CustomDescription\Model
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class CustomDescription extends AbstractModel implements CustomDescriptionInterface
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(\Snowdog\CustomDescription\Model\Resource\CustomDescription::class);
    }

    /**
     * Get custom description list from a given product id
     * @param $productId
     * @return array
     */
    public function getCustomDescriptionByProductId($productId)
    {
        return $this
            ->_getResource()
            ->getCustomDescriptionByProductId($productId);
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->_getData(self::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->_getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->_getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getImage()
    {
        return $this->_getData(self::IMAGE);
    }

    /**
     * @inheritdoc
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->_getData(self::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }
}
