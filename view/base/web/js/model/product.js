/* 
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2022 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_Base
 */

define([], function () {
    'use strict';
    /**
     *
     * @class Product
     */
    return class Product {

        /**
         * @param {Number} entity_id
         * @param {string} sku
         * @param {string} name
         * @param {Number} type_id
         * @param {string} complete_image_url
         * @param {string} complete_page_url
         * @param {Number} price
         * @param {Number} special_price
         * @param {string} description
         * @param {string} formatted_price
         * @param {string} formatted_special_price
         */
        constructor({
            entity_id, sku, name, type_id, complete_image_url, complete_page_url, price = 0,
            special_price = 0, description = '', short_description = '', formatted_price, formatted_special_price }) {
            this.entity_id = entity_id;
            this.sku = sku;
            this.name = name;
            this.type_id = type_id;
            this.complete_image_url = complete_image_url;
            this.complete_page_url = complete_page_url;
            this.price = price;
            this.special_price = special_price;
            this.formatted_price = formatted_price;
            this.formatted_special_price = formatted_special_price;
            this.description = description;
            this.short_description = short_description;
        }
    }
});