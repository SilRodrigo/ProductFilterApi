/* 
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2022 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_Base
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