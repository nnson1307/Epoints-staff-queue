<?php
namespace MyCore\Api\Helper;

/**
 * Fetch option kết quả từ API
 *
 * @package MyCore\Api\Helper
 */
trait FetchOptionTrait
{
    /**
     * Fetch option kết quả từ API
     *
     * @param $data
     * @param $keyFieldName
     * @param $valFieldName
     * @return array
     */
    protected function fetchOption($data, $keyFieldName, $valFieldName)
    {
        $option = ['' => 'Tất cả'];

        if (($data['Result']['ErrorCode'] ?? 1)) {
            return $option;
        }

        foreach ($data['Result']['Data'] as $item) {
            $option[ $item[$keyFieldName] ] = $item[$valFieldName];
        }

        return $option;
    }
}
