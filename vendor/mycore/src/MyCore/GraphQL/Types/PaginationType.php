<?php
namespace MyCore\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

/**
 * Class PaginationType
 * @package MyCore\GraphQL\Types
 * @author DaiDP
 * @since Mar, 2020
 */
class PaginationType extends GraphQLType
{
    protected $attributes = [
        'name'          => 'PaginationType',
        'description'   => 'Paging data'
    ];

    public function fields(): array
    {
        return [
            'total' => [
                'type' => Type::int(),
                'description' => 'Total item'
            ],
            'itemPerPage' => [
                'type' => Type::int(),
                'description' => 'Item in a page'
            ],
            'from' => [
                'type' => Type::int(),
                'description' => 'Start from index'
            ],
            'to' => [
                'type' => Type::int(),
                'description' => 'End index of page'
            ],
            'currentPage' => [
                'type' => Type::int(),
                'description' => 'Current page'
            ],
            'firstPage' => [
                'type' => Type::int(),
                'description' => 'First page'
            ],
            'lastPage' => [
                'type' => Type::int(),
                'description' => 'Last page'
            ],
            'previousPage' => [
                'type' => Type::int(),
                'description' => 'Previous page'
            ],
            'nextPage' => [
                'type' => Type::int(),
                'description' => 'Next page'
            ],
            'pageRange' => [
                'type' => Type::listOf(Type::int()),
                'description' => 'Page range'
            ]
        ];
    }
}