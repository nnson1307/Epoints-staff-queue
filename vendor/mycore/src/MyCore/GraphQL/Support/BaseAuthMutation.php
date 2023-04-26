<?php
namespace MyCore\GraphQL\Support;

use MyCore\GraphQL\Helper\CheckAuthTrait;
use Rebing\GraphQL\Support\Mutation;

/**
 * Class BaseAuthMutation
 * @package MyCore\GraphQL\Support
 * @author DaiDP
 * @since Dec, 2019
 */
abstract class BaseAuthMutation extends Mutation
{
    use CheckAuthTrait;
}