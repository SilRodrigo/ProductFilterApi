<?php
/* 
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2022 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_Base
 */

namespace Rsilva\ProductFilterApi\Model\Api;

use Magento\Framework\Api\SearchCriteriaInterface as SearchCriteria;
use Magento\Framework\Api\SortOrder;

class AbstractProductsProvider
{

    const DEFAULT_PAGE_SIZE = 9;
    const DEFAULT_FILTER_GROUP = [];

    /**
     *  @var SearchCriteria
     */
    private $_searchCriteria;

    /**
     *  @var array
     */
    private $_categoryList;

    /**
     * @var int
     */
    private $_pageSize = ProductsProvider::DEFAULT_PAGE_SIZE;

    /**
     * @var int
     */
    private $_currentPage = 1;

    /**
     * @var int
     */
    private $_finalPage = 1;

    public function getSearchCriteria(): SearchCriteria
    {
        return $this->_searchCriteria;
    }

    public function setSearchCriteria(SearchCriteria $value)
    {
        $this->_searchCriteria = $value;
    }

    public function getCategoryList(): array
    {
        return $this->_categoryList;
    }
    
    public function setCategoryList(array $value)
    {
        $this->_categoryList = $value;
    }

    public function getPageSize(): int
    {
        return $this->_pageSize;
    }
    
    public function setPageSize(int $value)
    {
        $this->_pageSize = $value;
    }

    public function getCurrentPage(): int
    {
        return $this->_currentPage;
    }
    
    public function setCurrentPage(int $value)
    {
        $this->_currentPage = $value;
    }

    public function getFinalPage(): int
    {
        return $this->_finalPage;
    }
    
    public function setFinalPage(int $value)
    {
        $this->_finalPage = $value;
    }
}
