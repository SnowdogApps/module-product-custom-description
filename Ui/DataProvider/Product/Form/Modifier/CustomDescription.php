<?php

namespace Snowdog\CustomDescription\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Textarea;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Media;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Snowdog\CustomDescription\Api\CustomDescriptionRepositoryInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;

/**
 * Class CustomDescription
 * @package Snowdog\CustomDescription\Ui\DataProvider\Product\Form\Modifier
 */
class CustomDescription extends AbstractModifier
{
    /**#@+
     * Group values
     */
    const GROUP_CUSTOM_DESCRIPTION_NAME = 'custom_descriptions';
    const GROUP_CUSTOM_DESCRIPTION_SCOPE = 'data.product';
    const GROUP_CUSTOM_DESCRIPTION_PREVIOUS_NAME = 'search-engine-optimization';
    const GROUP_CUSTOM_DESCRIPTION_DEFAULT_SORT_ORDER = 31;
    /**#@-*/

    /**#@+
     * Button values
     */
    const BUTTON_ADD = 'button_add';
    /**#@-*/

    /**#@+
     * Container values
     */
    const CONTAINER_HEADER_NAME = 'container_header';
    const CONTAINER_OPTION = 'container_description';
    const CONTAINER_COMMON_NAME = 'container_common';
    const CONTAINER_TYPE_STATIC_NAME = 'container_type_static';
    /**#@-*/

    /**#@+
     * Grid values
     */
    const GRID_DESCRIPTION_NAME = 'descriptions';
    const GRID_TYPE_SELECT_NAME = 'values';
    /**#@-*/

    /**#@+
     * Field values
     */
    const FIELD_ENABLE = 'affect_product_custom_description';
    const FIELD_DESCRIPTION_ID = 'entity_id';
    const FIELD_TITLE_NAME = 'title';
    const FIELD_DESCRIPTION_NAME = 'description';
    const FIELD_SORT_ORDER_NAME = 'position';
    const FIELD_IMAGE_NAME = 'image';
    const FIELD_IS_DELETE = 'is_delete';
    /**#@-*/

    /**#@+
     * Import options values
     */
    const CUSTOM_DESCRIPTION_LISTING = 'product_custom_description_listing';
    /**#@-*/

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var CustomDescriptionRepositoryInterface
     */
    private $customDescRepo;

    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     * @param CustomDescriptionRepositoryInterface $customDescRepo
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        CustomDescriptionRepositoryInterface $customDescRepo
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
        $this->customDescRepo = $customDescRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $descriptions = [];
        $productId = $this->locator->getProduct()->getId();
        $productCustomDesc = $this->customDescRepo->getCustomDescriptionByProductId($productId);
        $customDescriptions = $productCustomDesc ?: [];

        /** @var \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface $description */
        foreach ($customDescriptions as $index => $description) {;
            $data = $description->getData() ?: [];
            $logger = ObjectManager::getInstance()->create('Psr\Log\LoggerInterface');
            $logger->debug(print_r($data, true));
            $descriptions[$index] = $data;
        }

        return array_replace_recursive(
            $data,
            [
                $productId => [
                    static::DATA_SOURCE_DEFAULT => [
                        static::FIELD_ENABLE => 1,
                        static::GRID_DESCRIPTION_NAME => []
                    ]
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->createCustomOptionsPanel();

        return $this->meta;
    }

    /**
     * Create "Customizable Options" panel
     *
     * @return $this
     */
    protected function createCustomOptionsPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                static::GROUP_CUSTOM_DESCRIPTION_NAME => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Detailed Description'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::GROUP_CUSTOM_DESCRIPTION_SCOPE,
                                'collapsible' => true,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $this->meta,
                                    static::GROUP_CUSTOM_DESCRIPTION_PREVIOUS_NAME,
                                    static::GROUP_CUSTOM_DESCRIPTION_DEFAULT_SORT_ORDER
                                ),
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                        static::FIELD_ENABLE => $this->getEnableFieldConfig(20),
                        static::GRID_DESCRIPTION_NAME => $this->getOptionsGridConfig(30)
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * Get config for header container
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
            'children' => [
                static::BUTTON_ADD => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Add Option'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => 'ns = ${ $.ns }, index = ' . static::GRID_DESCRIPTION_NAME,
                                        'actionName' => 'processingAddChild',
                                    ]
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for the whole grid
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getOptionsGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Detailed Description'),
                        'componentType' => DynamicRows::NAME,
//                        'component' => 'Snowdog_CustomDescription/js/components/dynamic-rows-import-custom-description',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => false,
                        'collapsibleHeader' => true,
                        'sortOrder' => $sortOrder,
                        'dataProvider' => static::CUSTOM_DESCRIPTION_LISTING,
                        'imports' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('New Detailed Description'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::CONTAINER_OPTION . '.' . static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_OPTION => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Fieldset::NAME,
                                        'label' => null,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(40),
                                static::CONTAINER_COMMON_NAME => $this->getCommonContainerConfig(10)
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get config for hidden field responsible for enabling custom options processing
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getEnableFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_ENABLE,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for container with common fields for any type
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getCommonContainerConfig($sortOrder)
    {
        $commonContainer = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_DESCRIPTION_ID => $this->getOptionIdFieldConfig(10),
                static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(20),
                static::FIELD_DESCRIPTION_NAME => $this->getDescriptionFieldConfig(30),
                static::FIELD_IMAGE_NAME => $this->getImageFieldConfig(40)
            ]
        ];

        return $commonContainer;
    }

    /**
     * Get config for hidden id field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getOptionIdFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_DESCRIPTION_ID,
                        'sortOrder' => $sortOrder,
                        'visible' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Title" fields
     *
     * @param int $sortOrder
     * @param array $options
     * @return array
     */
    protected function getTitleFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Title'),
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataScope' => static::FIELD_TITLE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'validation' => [
                                'required-entry' => true
                            ],
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    /**
     * @param $sortOrder
     * @param array $options
     * @return array
     */
    protected function getDescriptionFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Description'),
                            'componentType' => Field::NAME,
                            'formElement' => Textarea::NAME,
                            'dataScope' => static::FIELD_DESCRIPTION_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'validation' => [
                                'required-entry' => true
                            ],
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    /**
     * @param $sortOrder
     * @return array
     */
    protected function getImageFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Image'),
                        'formElement' => 'fileUploader',
                        'componentType' => 'fileUploader',
                        'component' => 'Magento_Ui/js/form/element/file-uploader',
                        'elementTmpl' => 'Magento_Downloadable/components/file-uploader',
                        'fileInputName' => 'image',
                        'uploaderConfig' => [
                            'url' => $this->urlBuilder->addSessionParam()->getUrl(
                                'snowcustomdescription/file/upload',
                                ['_secure' => true]
                            ),
                        ],
                        'allowedExtensions' => 'jpg jpeg gif png svg',
                        'dataScope' => 'file',
                        'validation' => [
                            'required-entry' => true,
                        ],
                        'notice' => __('Allowed file types: jpeg, gif, png.'),
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for sorting
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getPositionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for removing rows
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getIsDeleteFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'fit' => true,
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }
}
