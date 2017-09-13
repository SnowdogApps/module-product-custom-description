<?php

namespace Snowdog\CustomDescription\Api\Data;

/**
 * Custom Description interface
 *
 * @api
 * @SuppressWarnings(PHPMD.ShortVariableName)
 */
interface CustomDescriptionInterface
{
    const PRODUCT_ID    = 'product_id';
    const TITLE         = 'title';
    const DESCRIPTION   = 'description';
    const IMAGE         = 'image';
    const POSITION      = 'position';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getImage();

    /**
     * @param string $image
     * @return $this
     */
    public function setImage($image);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition($position);
}
