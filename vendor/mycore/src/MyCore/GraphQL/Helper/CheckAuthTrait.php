<?php
namespace MyCore\GraphQL\Helper;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 19/12/2019
 * Time: 11:11 AM
 */
trait CheckAuthTrait
{
    /**
     * Override this in your queries or mutations
     * to provide custom authorization.
     *
     * @param  mixed  $root
     * @param  array  $args
     * @param  mixed  $ctx
     * @param  ResolveInfo|null  $resolveInfo
     * @param  Closure|null  $getSelectFields
     * @return bool
     */
    public function authorize($root, array $args, $ctx, ResolveInfo $resolveInfo = null, Closure $getSelectFields = null): bool
    {
        return true;
    }
}