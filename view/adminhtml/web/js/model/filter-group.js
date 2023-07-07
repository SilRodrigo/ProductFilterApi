/* 
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2023 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_ProductFilterApi
 */

define([], function () {
    'use strict';
    /**
     *
     * @class FilterGroup
     */
    return class FilterGroup {

        static TYPE = {
            NAME: 'NAME',
            CATEGORY: 'CATEGORY',
            ATTRIBUTE: 'ATTRIBUTE'
        }

        #CONFIG = {
            MESSAGE: {
                INVALID_CONSTRUCTOR_PARAMS: 'Invalid data passed on constructor'
            }
        }

        /**
         * @param {string} label
         * @param {Array<Filter>} filters
         * @param {FilterGroup.TYPE} type
         */
        constructor(label, filters = [], type = FilterGroup.TYPE.NAME) {
            this.label = label; // name
            this.filters = filters;
            this.type = FilterGroup.TYPE[type];
            if (!this.label || !this.type || (this.filters.length > 1 && this.filters.find(filter => filter.field != this.label))) {
                throw this.#CONFIG.MESSAGE.INVALID_CONSTRUCTOR_PARAMS;
            }
            
        }

        /**
         * @param {Filter} filter
         * @param {FilterGroup.TYPE} groupType
         * @param {boolean} unMerge Creates a separate group even if FilterGroup already contains 
         * @returns {Array<FilterGroup.filters>}
         */
        addFilter(filter) {
            if (filter?.constructor.name !== 'Filter' || this.label !== filter.field) return;
            if (!this.filters.find(stored_filter => stored_filter.value === filter.value)) this.filters.push(filter);
            return this;
        }
    }
});