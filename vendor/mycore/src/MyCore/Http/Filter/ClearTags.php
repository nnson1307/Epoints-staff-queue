<?php
namespace MyCore\Http\Filter;


trait ClearTags
{
    /**
     * Clean tags in content
     *
     * @param $dirty
     * @param array $exclude
     * @return array|string
     */
    protected function clean($dirty, array $exclude = [])
    {
        if (is_array($dirty)) {
            array_walk($dirty, function (&$item, $key) use ($exclude) {
                if (! in_array($key, $exclude)) {
                    $item = $this->clean($item, $exclude);
                }
            });

            return $dirty;
        }

        return strip_tags($dirty);
    }
}