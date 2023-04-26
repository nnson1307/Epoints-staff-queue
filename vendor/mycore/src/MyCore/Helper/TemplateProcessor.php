<?php
namespace MyCore\Helper;

/**
 * Trait TemplateProcessor
 * @package MyCore\Helper
 * @author DaiDP
 * @since Aug, 2019
 */
trait TemplateProcessor
{
    /**
     * Binding data to template.
     * Format content [:attribute]
     * Format params [attribute => value]
     *
     * @param $content
     * @param array $params
     * @return mixed
     */
    protected function bindParamsTemplate($content, array $params)
    {
        $findVal    = array_keys($params);
        $replaceVal = array_values($params);

        foreach ($findVal as &$item) {
            $item = sprintf('[:%s]', $item);
        }

        return str_replace($findVal, $replaceVal, $content);
    }
}