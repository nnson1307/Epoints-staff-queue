<?php
namespace MyCore\SMS;

/**
 * Class Configable
 * @package MyCore\SMS
 * @author DaiDP
 * @since Aug, 2019
 */
abstract class Configable
{
    /**
     * Configable constructor.
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }
}