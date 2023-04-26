<?php
namespace MyCore\GraphQL\CustomType;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

/**
 * Class ArrayObjectType
 * @package MyCore\GraphQL\CustomType
 * @see https://webonyx.github.io/graphql-php/type-system/scalar-types/#writing-custom-scalar-types
 * @author DaiDP
 * @since Mar, 2020
 */
class ArrayObjectType extends ScalarType
{
    // Note: name can be omitted. In this case it will be inferred from class name
    // (suffix "Type" will be dropped)
    public $name = 'ArrayObject';

    public function __construct(array $config = [])
    {
        $this->name .= uniqid();
        parent::__construct($config);
    }

    /**
     * Serializes an internal value to include in a response.
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws Error
     */
    public function serialize($value)
    {
        // TODO: Implement serialize() method.
        return $value;
    }

    /**
     * Parses an externally provided value (query variable) to use as an input
     *
     * In the case of an invalid value this method must throw an Exception
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws Error
     */
    public function parseValue($value)
    {
        // TODO: Implement parseValue() method.
        return $value;
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input
     *
     * In the case of an invalid node or value this method must throw an Exception
     *
     * @param Node $valueNode
     * @param mixed[]|null $variables
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        //$b = new \GraphQL\Language\AST\NodeList();

        //$a = new \GraphQL\Language\AST\ListValueNode();
        //$a->toArray()
        //$a->values->getIterator();
        //var_dump($valueNode->values);die;
        // TODO: Implement parseLiteral() method.
        return $valueNode->toArray();
    }
}