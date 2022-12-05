<?php

/* 
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2022 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_Base
 */

namespace Rsilva\ProductFilterApi\Api;

interface ProductsProviderInterface
{
    /**
     * @param string $filterData
     * @param string $sortOrder
     * @param string $page
     * @param string $pageSize
     * @return string
     */
    public function getProductList($filterData, $sortOrder, $page, $pageSize);
}
