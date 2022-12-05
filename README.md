# Rsilva_ProductFilterApi

## Visão geral
 - Módulo para **Magento 2** que realiza uma chamada ***API*** retornando um ***JSON*** com os
   produtos cadastrados, suas categorias e atributos com base no filtro
   passado nos parâmetros da requisição.

## Exemplo de retorno

    {
    "collection": [
        {
            "entity_id": "1",
            "sku": "teste",
            "name": "teste",
            "type_id": "simple",
            "complete_image_url": "/media/catalog/product/p/u/image.png",
            "complete_page_url": "https://rsilva.vitrine/test.html",
            "price": "12.990000",
            "special_price": null,
            "formated_price": "US$ 12,99",
            "formated_special_price": "US$ 0,00",
            "description": ""
        }
    ],
    "total_count": 1,
    "pageSize": 9,
    "finalPage": 1,
    "currentPage": 1,
    "attributeList": [
        {
            "backend_type": "decimal",
            "frontend_input": "Price",
            "options": [
                {
                    "identifier": "price12.990000",
                    "value": "12.990000",
                    "label": "12.990000"
                }
            ],
            "name": "price"
        }
    ]
}

## Utilizando o módulo

Na sua classe JS, passe a chamada do service:

    define([
	    'jquery',
	    'Rsilva_ProductFilterApi/js/service/product-api'
	],function ($, productApiService)  {}

Chamada para pesquisar por nome:
    
    * @param  {string}  name
    * @param  {Function}  callback
    productApiService.queryProductByName(name,  callback)
