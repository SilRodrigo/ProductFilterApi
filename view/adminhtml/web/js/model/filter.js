/* 
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2022 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_Base
 */

define([], function () {
    'use strict';
    /**
     *
     * @class Filter
     */
    return class Filter {

        /**
         * @type {JSON}
         */
        static CONDITIONS = {
            EQUAL: 'eq',
            NOTEQUAL: 'neq',
            LIKE: 'like',
            FROMTO: (from, to) => `from ${from} to ${to}`,
            EQUALORGREATER: 'gteq',
            EQUALORLESS: 'lteq'
        }

        /**
         * @param {string} field
         * @param {string} condition_type
         * @param {string} value
         * @param {FilterGroup.TYPE} group_type
         */
        constructor(field, condition_type = 'eq', value, group_type) {
            if (!field, !value) throw 'Required constructor params are empty!';
            this.field = field; // name
            this.conditionType = condition_type; // eq
            this.value = value; // shirt
            this.group_type = group_type; // 'NAME'
        }
    }
});