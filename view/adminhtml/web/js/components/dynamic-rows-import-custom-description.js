define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
    'underscore',
    'mageUtils'
], function (DynamicRows, _, utils) {
    'use strict';

    var maxId = 0,

        /**
         * @param {Array} data - array with records data
         */
        initMaxId = function (data) {
            if (data && data.length) {
                maxId = _.max(data, function (record) {
                    return parseInt(record['entity_id'], 10) || 0;
                })['entity_id'];
                maxId = parseInt(maxId, 10) || 0;
            }
        };

    return DynamicRows.extend({
        defaults: {
            mappingSettings: {
                enabled: false,
                distinct: false
            },
            update: true,
            map: {
                'entity_id': 'entity_id'
            },
            identificationProperty: 'entity_id',
            identificationDRProperty: 'entity_id'
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            initMaxId(this.recordData());

            return this;
        },

        /** @inheritdoc */
        processingInsertData: function (data) {
            var customDescriptions = [],
                currentDescription;

            if (!data) {
                return;
            }
            data.each(function (item) {
                if (!item.customDescriptions) {
                    return;
                }
                item.customDescriptions.each(function (description) {
                    currentDescription = utils.copy(description);

                    if (currentOption.hasOwnProperty('position')) {
                        delete currentOption['position'];
                    }
                    currentOption['entity_id'] = ++maxId;
                    customDescriptions.push(currentOption);
                });
            });

            if (!customDescriptions.length) {
                return;
            }
            this.cacheGridData = options;
            customDescriptions.each(function (desc) {
                this.mappingValue(desc);
            }, this);

            this.insertData([]);
        },

        /**
         * Set empty array to dataProvider
         */
        clearDataProvider: function () {
            this.source.set(this.dataProvider, []);
        },

        /** @inheritdoc */
        processingAddChild: function (ctx, index, prop) {
            if (ctx && !_.isNumber(ctx['entity_id'])) {
                ctx['entity_id'] = ++maxId;
            } else if (!ctx) {
                this.showSpinner(true);
                this.addChild(ctx, index, prop);

                return;
            }

            this._super(ctx, index, prop);
        },

        /**
         * Mutes parent method
         */
        updateInsertData: function () {
            return false;
        }
    });
});
