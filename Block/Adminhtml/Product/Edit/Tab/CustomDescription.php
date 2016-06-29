<?php

namespace Snowdog\CustomDescription\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Block\Widget;

/**
 * Class CustomDescription
 *
 * @package Snowdog\CustomDescription\Block\Adminhtml\Product\Edit\Tab
 */
class CustomDescription extends Widget
{
    /**
     * @var string
     */
    protected $_template = 'Snowdog_CustomDescription::catalog/product/edit/tab/custom-description.phtml';

    /**
     * @return Widget
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'add_button',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Add New Description'), 'class' => 'add', 'id' => 'add_new_custom_description']
        );

        $this->addChild('custom_description_box', 'Snowdog\CustomDescription\Block\Adminhtml\Product\Edit\Tab\CustomDescription\Description');

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return string
     */
    public function getCustomDescriptionBoxHtml()
    {
        return $this->getChildHtml('custom_description_box');
    }
}
