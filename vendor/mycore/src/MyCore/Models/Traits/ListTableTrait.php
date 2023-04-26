<?php
namespace MyCore\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filter table
 * 
 * @author isc-daidp
 * @since Feb 23, 2018
 */
trait ListTableTrait
{
    /**
     * Get Table list
     * 
     * @param array $filter
     */
    public function getList(array $filter = [], array $queryCondition = [])
    {
        $select  = $this->_getList($queryCondition);
        $page    = (int) ($filter['page'] ?? 1);
        $display = (int) ($filter['display'] ?? PAGING_ITEM_PER_PAGE);
        // search term
        if (!empty($filter['search_type']) && !empty($filter['search_keyword']))
        {
            $select->where($filter['search_type'], 'like', '%' . $filter['search_keyword'] . '%');
        }
        unset($filter['search_type'], $filter['search_keyword'], $filter['page'], $filter['display']);

        // filter list
        foreach ($filter as $key => $val)
        {
            if (trim($val) == '') {
                continue;
            }

            if ($key == 'date_created') {
                $select->where($this->table. '.' .$key, 'like', $val . '%');
            }
            else if (strpos($key, '$') === false) {
                $select->where($this->table. '.' .$key, $val);
            }
            else {
                $select->where(str_replace('$', '.', $key), $val);
            }
        }

        return $select->paginate($display, $columns = ['*'], $pageName = 'page', $page);
    }


    /**
     * Xử lý phân trang
     *
     * @param Builder $oSelect
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function __processingPaging(Builder $oSelect, $params = [])
    {
        $page    = (int) ($params['page'] ?? 1);
        $display = (int) ($params['display'] ?? PAGING_ITEM_PER_PAGE);

        return $oSelect->paginate($display, $columns = ['*'], $pageName = 'page', $page);
    }
}