<?php
namespace MyCore\Entities;

/**
 * Class JobMessageEntity
 * @package MyCore\Entities
 * @author DaiDP
 * @since Aug, 2019
 */
abstract class JobMessageEntity
{
    /**
     * JobMessageModel constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    /**
     * Convert message to array
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}