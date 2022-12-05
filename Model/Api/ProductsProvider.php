<?php
/* 
 * @author Rodrigo Silva
 * @copyright Copyright (c) 2022 Rodrigo Silva (https://github.com/SilRodrigo)
 * @package Rsilva_Base
 */

namespace Rsilva\ProductFilterApi\Model\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaInterface as SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollection;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class ProductsProvider extends AbstractProductsProvider
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryHelp
     */
    protected $CategoryHelper;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchBuilder;

    /**
     *  @var \Magento\Framework\Api\SortOrder
     */
    private $sortOrder;

    /**
     * @var Configurable
     */
    protected $configurableProductType;

    /**
     *  @var JsonHelper
     */
    private $jsonHelper;

    /**
     *  @var PriceCurrencyInterface
     */
    private $priceCurrency;


    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryHelper $categoryHelper,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrder $sortOrder,
        Configurable $configurableProductType,
        JsonHelper $jsonHelper,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->categoryHelper = $categoryHelper;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->searchBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->sortOrder = $sortOrder;
        $this->configurableProductType = $configurableProductType;
        $this->jsonHelper = $jsonHelper;
        $this->priceCurrency = $priceCurrency;

        $this->setCategoryList($this->getCategoryData());

        $this->prepareDefaultFilters();
    }

    private function prepareDefaultFilters()
    {
        $filterGroupStatus = $this->filterGroupBuilder
            ->addFilter($this->filterBuilder
                ->setField('status')
                ->setConditionType('eq')
                ->setValue('1')
                ->create())
            ->create();
        $filterGroupVisible = $this->filterGroupBuilder
            ->addFilter($this->filterBuilder
                ->setField('visibility')
                ->setConditionType('eq')
                ->setValue('4')
                ->create())
            ->create();
        $filterGroup = [
            $filterGroupStatus,
            $filterGroupVisible,
            /* $filterGroupEnabled */
        ];

        $this->sortOrder = $this->sortOrder->setField('entity_id')->setDirection('asc');
        $this->setSearchCriteria($this->searchBuilder
            ->setFilterGroups($filterGroup)
            ->setPageSize($this->getPageSize())
            ->setCurrentPage($this->getCurrentPage())
            ->setSortOrders([$this->sortOrder])
            ->create());
    }

    /**
     * @param int|string $productId
     * @return string     
     */
    private function getParentProduct($productId)
    {
        return $this->configurableProductType->getParentIdsByChild($productId);
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @return ProductCollection     
     */
    private function getCollection($searchCriteria = null)
    {
        return $this->productRepository->getList($searchCriteria ?? $this->getSearchCriteria());
    }

    /**
     * @param ProductCollectionInterface $collection
     * @return array
     */
    private function getAttributeToFilterData($collection)
    {
        $attributeToFilterList = [];

        foreach ($collection as $product) {
            foreach ($product->getAttributes() as $attr) {
                if ($attr->getIsFilterable() == true) {
                    $filterAttributeValue = $product[$attr->getName()];
                    if ($filterAttributeValue) {
                        if (!isset($attributeToFilterList[$attr->getName()])) {
                            $attributeToFilterList[$attr->getName()] =
                                [
                                    'backend_type' => $attr->getData('backend_type'),
                                    'frontend_input' => ucfirst($attr->getData('frontend_input')),
                                    'options' => [],
                                ];
                        }
                        $attributes = explode(',', $filterAttributeValue);
                        foreach ($attributes as $attribute) {

                            $hasMatch = false;
                            for ($i = 0; $i < count($attributeToFilterList[$attr->getName()]['options']); $i++) {
                                if ($attributeToFilterList[$attr->getName()]['options'][$i]['value'] != $attribute) continue;
                                $hasMatch = true;
                                break;
                            }

                            if (!$hasMatch) {
                                $label = $attr->getSource()->getOptionText($attribute);
                                $attributeToFilterList[$attr->getName()]['options'][] =
                                    ['identifier' => $attr->getName() . $attribute, 'value' => $attribute, 'label' =>  $label ? $label : $attribute];
                            }
                        }
                    }
                }
            }
        }

        $completeFilterList = [];
        foreach ($attributeToFilterList as $key => $filterList) {
            $filterList['name'] = $key;
            $completeFilterList[] = $filterList;
        }

        return $completeFilterList;
    }

    /**
     * @return array
     */
    private function getCategoryData()
    {
        $currentCategoryNode = $this->categoryHelper->getStoreCategories();
        $categoryTreeData = [];
        foreach ($currentCategoryNode as $node) {
            $categoryTreeData[] = $this->getCategoryNodeChildrenData($node);
        }
        return $categoryTreeData;
    }

    /**
     * @param \Magento\Framework\Data\Tree\Node\Collection  $node
     * @return array
     */
    private function getCategoryNodeChildrenData($node)
    {
        $data = array(
            'title' => $node->getData('name'),
            'url'   => $node->getData('url_key'),
            'id' => $node->getData('entity_id')
        );

        foreach ($node->getChildren() as $childNode) {
            if (!array_key_exists('children', $data)) {
                $data['children'] = array();
            }

            $data['children'][$childNode->getData('entity_id')] = $this->getCategoryNodeChildrenData($childNode);
        }
        return $data;
    }

    /**
     * @param ProductCollection $collection
     * @return array
     */
    private function inflateProductCollection($collection)
    {
        $productCollectionData = [];

        foreach ($collection as $product) {
            $image_thumbnail_path = $product->getImage();
            $final_image_path = '/media/catalog/product' . $image_thumbnail_path;
            $product->setData('complete_image_url', $final_image_path);
            $product->setData('complete_page_url', $product->getProductUrl());
            $product->setData('price', $product->getPrice());
            $product->setData('formated_price', $this->priceCurrency->format($product->getPrice(), false));
            $product->setData('special_price', $product->getSpecialPrice());
            $product->setData('formated_special_price', $this->priceCurrency->format($product->getSpecialPrice(), false));
            $productCollectionData[] = $product->getData();
        }

        return $productCollectionData;
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @return string
     */
    private function getFilteredProducts($searchCriteria = null)
    {
        try {
            $collection = $this->getCollection($searchCriteria);
            $count = $collection->getTotalCount();
            $this->setFinalPage(ceil($count / $this->getPageSize()));
            if ($this->getCurrentPage() > $this->getFinalPage()) $this->setCurrentPage($this->getFinalPage());

            $collection = $collection->getItems();
            $attributeFilterList = $this->getAttributeToFilterData($collection);
            $collection = $this->inflateProductCollection($collection);

            $productData = [
                'collection' => $collection,
                'totalCount' => $count,
                'pageSize' => $this->getPageSize(),
                'finalPage' => $this->getFinalPage(),
                'currentPage' => $this->getCurrentPage()
            ];
            if (count($attributeFilterList) > 0) $productData['attributeList'] = $attributeFilterList;
            if (count($this->getCategoryList()) > 0) $productData['categories'] = $this->getCategoryList();

            return json_encode($productData);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareFilterData($filterData, $sortOrder, $page, $pageSize)
    {
        $filterData = $this->jsonHelper->unserialize($filterData);
        $sortOrder = $this->jsonHelper->unserialize($sortOrder);
        $page = intval($page ??  $this->getCurrentPage());
        $pageSize = intval($pageSize ??  $this->getPageSize());

        if ($page < 1) $page = 1;
        if ($pageSize < 1) $pageSize = AbstractProductsProvider::DEFAULT_PAGE_SIZE;
        $this->setCurrentPage($page);
        $this->setPageSize($pageSize);

        $filterGroupList = $this->prepareFilterGroups($filterData);
        $sortOrder = $this->sortOrder->setField($sortOrder['field'] ?? 'entity_id')->setDirection($sortOrder['direction']);

        $this->searchCriteria = $this->searchBuilder
            ->setFilterGroups($filterGroupList)
            ->setPageSize($this->getPageSize())
            ->setCurrentPage($this->getCurrentPage())
            ->setSortOrders([$sortOrder])
            ->create();
    }

    /**
     * @param array $filterData
     * @return array
     */
    protected function prepareFilterGroups($filterData)
    {
        $filterGroupList = ProductsProvider::DEFAULT_FILTER_GROUP;
        foreach ($filterData as $filterParams) {
            $filterGroup = $this->filterGroupBuilder;
            $field = $filterParams['label'];
            foreach ($filterParams['filters'] as $filter) {
                $filter_query = $this->filterBuilder
                    ->setField($field)
                    ->setConditionType($filter['conditionType'] ?? 'eq')
                    ->setValue($filter['value'])
                    ->create();
                $filterGroup->addFilter($filter_query);
            }
            $filterGroupList[] = $filterGroup->create();
        }
        return $filterGroupList;
    }

    /**
     * @param string $filterData [label:string, filters:[field:string, conditionType:string, value:string], type:string]
     * @param string $sortOrder {field:string, direction:string}
     * @param string $page
     * @param string $pageSize
     * @return string
     */
    public function getProductList($filterData, $sortOrder, $page, $pageSize)
    {
        try {
            $this->prepareFilterData($filterData, $sortOrder, $page, $pageSize);
            return $this->getFilteredProducts($this->searchCriteria);
        } catch (\Throwable $th) {
            return ($th->getMessage());
        }
    }
}

/* 
    JSON request example
    {
        "filterData": "[{\"label\":\"name\",\"filters\":[{\"field\":\"name\",\"conditionType\":\"like\",\"value\":\"%scarf%\"}],\"type\":\"NAME\"}]",
        "sortOrder": "{\"field\":\"name\",\"direction\":\"asc\"}",
        "page": 1,
        "pageSize": 9
    }

    Filter builder example
    $filterOne = $this->filterBuilder
                ->setField('color')
                ->setConditionType('eq')
                ->setValue('49')
                ->create();

    $filterTwo = $this->filterBuilder
        ->setField('name')
        ->setConditionType('like')
        ->setValue('%' . 'Kangeroo' . '%')
        ->create();

    $groupOne = $this->filterGroupBuilder
        ->addFilter($filterOne)
        ->create();

    $groupTwo = $this->filterGroupBuilder
        ->addFilter($filterTwo)
        ->create();

    $sortOrder = $this->_sortOrder->setField('entity_id')->setDirection('asc');

    $searchCriteria = $this->searchBuilder
        ->setFilterGroups([$groupOne, $groupTwo])
        ->setPageSize($pageSize ?? $this->_pageSize)
        ->setCurrentPage($currentPage ?? $this->_currentPage)
        ->setSortOrders([$sortOrder])
        ->create();   
*/