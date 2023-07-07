/* 
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2023 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_ProductFilterApi
 */

define([], function () {
    'use strict';
    /**
     *
     * @class SortOrder
     */
    return class SortOrder {

        /**
         * @param {string} field
         * @param {string} direction
         */
        constructor(field = 'entity_id', direction = 'asc') {
            this.field = field;
            this.direction = direction;
        }
    }
});