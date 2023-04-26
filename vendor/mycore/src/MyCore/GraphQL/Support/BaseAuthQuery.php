<?php
namespace MyCore\GraphQL\Support;

use MyCore\GraphQL\Helper\CheckAuthTrait;
use Rebing\GraphQL\Support\Query;

/**
 * Class BaseAuthQuery
 * @package MyCore\GraphQL\Support
 * @author DaiDP
 * @since Dec, 2019
 */
abstract class BaseAuthQuery extends Query
{
    use CheckAuthTrait;
}