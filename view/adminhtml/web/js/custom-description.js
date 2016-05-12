/*jshint browser:true*/
/*global alert:true*/
define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'useDefault',
    'collapsable',
    'mage/translate',
    'mage/backend/validation',
    'Magento_Ui/js/modal/modal'
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('mage.customDescription', {
        options: {
            selectionItemCount: {}
        },

        _create: function () {
            this.baseTmpl = mageTemplate('#custom-description-base-template');
            this.rowTmpl = mageTemplate('#custom-description-select-type-row-template');

            this._initDescriptionBoxes();
        },

        _initDescriptionBoxes: function () {
            if (!this.options.isReadonly) {
                this.element.sortable({
                    axis: 'y',
                    handle: '[data-role=draggable-handle]',
                    items: '#product_custom_description_container_top > div',
                    update: this._updateDescriptionBoxPositions,
                    tolerance: 'pointer'
                });
            }
            var syncDescriptionTitle = function (event) {
                var currentValue = $(event.target).val(),
                    descriptionBoxTitle = $('.admin__collapsible-title > span', $(event.target).closest('.fieldset-wrapper')),
                    newDescriptionTitle = $.mage.__('New Description');

                descriptionBoxTitle.text(currentValue === '' ? newDescriptionTitle : currentValue);
            };
            this._on({
                /**
                 * Reset field value to Default
                 */
                'click .use-default-label': function (event) {
                    $(event.target).closest('label').find('input').prop('checked', true).trigger('change');
                },

                /**
                 * Minimize description block
                 */
                'click #product_custom_description_container_top [data-target$=-content]': function () {
                    if (this.options.isReadonly) {
                        return false;
                    }
                },

                /**
                 * Remove description
                 */
                'click button[id^=product_custom_description_][id$=_delete]': function (event) {
                    var element = $(event.target).closest('#product_custom_description_container_top > div.fieldset-wrapper,tr');
                    if (element.length) {
                        $('#product_' + 'custom_' + element.attr('id').replace('product_', '') + '_is_delete').val(1);
                        element.addClass('ignore-validate').hide();
                        this.refreshSortableElements();
                    }
                },

                /**
                 * Add new description
                 */
                'click #add_new_custom_description': function (event) {
                    this.addDescription(event);
                },

                //Sync title
                'change .field-description-title > .control > input[id$="_title"]': syncDescriptionTitle,
                'keyup .field-description-title > .control > input[id$="_title"]': syncDescriptionTitle,
                'paste .field-description-title > .control > input[id$="_title"]': syncDescriptionTitle
            });
        },

        /**
         * Update description position
         */
        _updateDescriptionBoxPositions: function () {
            $(this).find('div[id^=description_]:not(.ignore-validate) .fieldset-alt > [name$="[sort_order]"]').each(function (index) {
                $(this).val(index);
            });
        },

        /**
         * Add description
         */
        addDescription: function (event) {
            var data = {},
                element = event.target || event.srcElement || event.currentTarget,
                baseTmpl;

            if (typeof element !== 'undefined') {
                data.id = this.options.itemCount;
                data.description = '';
                data.description_id = 0;
            } else {
                data = event;
                data.id = event.entity_id;
                data.description_id = event.entity_id;
                this.options.itemCount = data.item_count;
            }

            baseTmpl = this.baseTmpl({
                data: data
            });

            $(baseTmpl)
                .appendTo(this.element.find('#product_custom_description_container_top'))
                .find('.collapse').collapsable();
            
            this.refreshSortableElements();
            this.options.itemCount++;
            $('#' + this.options.fieldId + '_' + data.id + '_title').trigger('change');
        },

        refreshSortableElements: function () {
            if (!this.options.isReadonly) {
                this.element.sortable('refresh');
                this._updateDescriptionBoxPositions.apply(this.element);
            }

            return this;
        }
    });

});
