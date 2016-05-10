<?php

namespace Snowdog\CustomDescription\Block\Adminhtml\Product\Edit\Tab\CustomDescription;

use Magento\Backend\Block\Widget;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Snowdog\CustomDescription\Model\Resource\CustomDescription;

class Description extends Widget
{
    /**
     * @var Product
     */
    protected $_productInstance;

    /**
     * @var string
     */
    protected $_template = 'snowcustomdescription/catalog/product/edit/tab/custom_description/description.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var int
     */
    protected $_itemCount = 1;

    /**
     * @var CustomDescription
     */
    protected $_customDescriptionResource;

    /**
     * Description constructor.
     *
     * @param Context $context
     * @param Product $product
     * @param Registry $registry
     * @param array $data
     * @param CustomDescription $customDescriptionResource
     */
    public function __construct(
        Context $context,
        Product $product,
        Registry $registry,
        array $data = [],
        CustomDescription $customDescriptionResource
    ) {
        $this->_product = $product;
        $this->_coreRegistry = $registry;
        $this->_customDescriptionResource = $customDescriptionResource;

        parent::__construct($context, $data);
    }

    /**
     * Get Product
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_productInstance) {
            $product = $this->_coreRegistry->registry('product');
            if ($product) {
                $this->_productInstance = $product;
            } else {
                $this->_productInstance = $this->_product;
            }
        }

        return $this->_productInstance;
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->_productInstance = $product;
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * Get custom description by the current product
     *
     * @return array
     */
    public function getCustomDescription()
    {
        $productId = $this->getRequest()->getParam('id', false);

        if ($productId) {
            $customDecription = $this->_customDescriptionResource
                ->getCustomDescriptionByProductId($productId);

            $realCustomDescription = array();

            foreach ($customDecription as $detDesc) {
                $this->setItemCount($detDesc['entity_id']);

                $detDesc['item_count'] = $this->getItemCount();

                $realCustomDescription[] = $detDesc;
            }

            return $realCustomDescription;
        }

        return [];
    }

    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->_itemCount;
    }

    /**
     * @param int $itemCount
     * @return $this
     */
    public function setItemCount($itemCount)
    {
        $this->_itemCount = max($this->_itemCount, $itemCount);
        return $this;
    }

    /**
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'product[custom_description]';
    }

    /**
     * Retrieve options field id prefix
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'product_custom_description';
    }

}
