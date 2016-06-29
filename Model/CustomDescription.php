<?php

namespace Snowdog\CustomDescription\Model;

use Magento\Framework\Model\AbstractModel;
use Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface;

/**
 * Class CustomDescription
 * 
 * @package Snowdog\CustomDescription\Model
 */
class CustomDescription
    extends AbstractModel
    implements CustomDescriptionInterface
{

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Snowdog\CustomDescription\Model\Resource\CustomDescription');
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
        return $this
            ->_getResource()
            ->getCustomDescriptionByProductId($productId);
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->_getData('product_id');
    }

    /**
     * @inheritdoc
     */
    public function setProductId($productId)
    {
        return $this->setData('product_id', $productId);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->_getData('title');
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        return $this->setData('title', $title);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->_getData('description');
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData('description', $description);
    }

    /**
     * @inheritdoc
     */
    public function getImage()
    {
        return $this->_getData('image');
    }

    /**
     * @inheritdoc
     */
    public function setImage($image)
    {
        return $this->setData('image', $image);
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->_getData('position');
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        return $this->setData('position', $position);
    }

}