/* 
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2022 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_Base
 */

define([
    'jquery',
    'Rsilva_ProductFilterApi/js/model/filter-group',
    'Rsilva_ProductFilterApi/js/model/filter',
    'Rsilva_ProductFilterApi/js/model/sort-order',
    'Rsilva_ProductFilterApi/js/model/product',
], function ($, FilterGroup, Filter, SortOrder, Product) {
    'use strict';
    /**
     *
     * @class ProductApi
     */
    return new class ProductApi {

        #CONFIG = {
            ENDPOINT: '/rest/V1/rsilva/product/list',
            HEADERS: {
                'content-type': 'application/json'
            },
            METHOD: 'POST'
        }

        isRequesting = false;

        /**
         * @type {Array<FilterGroup>}
         */
        #filter_groups = [];

        /**
         * @type {SortOrder}
         */
        #sort_order = new SortOrder();

        /**
         * @type {Number}
         */
        #page = 1;

        /**
         * @type {Number}
         */
        #page_size = 9;

        get filter_groups() {
            return this.#filter_groups;
        }

        /**
         * @param {string} field
         * @param {string} direction
         */
        set sort_order({ field, direction }) {
            if (field) this.#sort_order.field = field;
            if (direction) this.#sort_order.direction = direction;
        }

        /**
         * @param {Number} value
         */
        set page(value) {
            if (Number.isInteger(value) && value > 0) this.#page = value;
        }

        /**
         * @param {Number} value
         */
        set page_size(value) {
            if (Number.isInteger(value) && value > 1) this.#page_size = value;
        }

        getFilterConditions() {
            return Filter.CONDITIONS;
        }

        createFilter(field, condition_type, value, group_type) {
            try {
                return new Filter(field, condition_type, value, group_type);
            } catch (error) {
                console.warn(error);
            }
        }

        /**
         * @param {string} name
         * @returns Object
         */
        prepareFilterByName(name) {
            return this.createFilter('name', this.getFilterConditions().LIKE, `%${name}%`, FilterGroup.TYPE.NAME)
        }

        /**
         * @param {Filter} filter
         * @param {FilterGroup} specificGroup
         * @returns {FilterGroup}
         */
        addFilterToGroup(filter, specificGroup) {
            if (filter?.constructor.name !== 'Filter') return;
            let match = this.filter_groups.find(filter_group => {
                if ((specificGroup && Object.is(filter_group, specificGroup))
                    || (!specificGroup && filter_group.label === filter.field)) {
                    return filter_group.addFilter(filter);
                }
            });
            if (!match) match = this.createFilterGroup(filter.field, [filter], filter.group_type);
            return match;
        }

        createFilterGroup(label, filters, group_type) {
            this.#filter_groups.push(new FilterGroup(label, filters, group_type));
            return this.filter_groups;
        }

        clearFilterGroups() {
            this.#filter_groups = [];
        }

        /**
         * @param {string} name 
         * @param {Function} callback          
         */
        queryProductByName(name, callback, no_load) {
            if (this.isRequesting) return;            
            this.clearFilterGroups();
            this.addFilterToGroup(this.prepareFilterByName(name));
            this.request(no_load).then(response => {
                if (callback) callback(response);
                $('body').trigger('processStop');
            });
        }

        /**
         * @param {Number} id
         * @returns Object
         */
        getProductById(id, no_load) {
            this.clearFilterGroups();
            this.addFilterToGroup(this.createFilter('entity_id', this.getFilterConditions().EQUAL, id, FilterGroup.TYPE.ATTRIBUTE));
            return this.request(no_load);
        }

        /*  */

        getRequestBody() {
            return JSON.stringify({
                filterData: JSON.stringify(this.#filter_groups),
                sortOrder: JSON.stringify(this.#sort_order),
                page: this.#page,
                pageSize: this.#page_size
            })
        }

        async request(no_load) {
            if (this.isRequesting && !no_load) return;
            this.isRequesting = true;
            if (!no_load) $('body').trigger('processStart');
            let response = await fetch(this.#CONFIG.ENDPOINT, {
                method: this.#CONFIG.METHOD,
                headers: this.#CONFIG.HEADERS,
                body: this.getRequestBody()
            });

            this.isRequesting = false;
            if (response.status !== 200) {
                return alert('Error requesting products, please try again');
            }
            try {
                response = JSON.parse(await response.json());
                if (response.collection) {
                    const productCollection = [];
                    response.collection.forEach(product => productCollection.push(new Product(product)));
                    response.collection = productCollection;
                }
                return response;
            } catch (error) {
                console.warn(error);
            }
        }
    }
});