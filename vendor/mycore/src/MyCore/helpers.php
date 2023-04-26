<?php

if (! function_exists('dec2money')) {
    /**
     * Convert dec string in mysql to number
     *
     * @param $var
     * @return number
     */
    function dec2money($var)
    {
        return floatval($var);
    }
}