<?php
namespace MyCore\Models\Traits;

/**
 * Trait RawPrefixTrait
 * @package MyCore\Models\Traits
 * @author DaiDP
 * @since Sep, 2019
 */
trait RawPrefixTrait
{
    /**
     * Add prefix
     *
     * @param $name
     * @return string
     */
    protected function pf($name)
    {
        return $this->getConnection()->getTablePrefix() . $name;
    }
}