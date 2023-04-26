<?php
namespace MyCore\Repository;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 11/19/2018
 * Time: 3:17 PM
 */
trait PagingTrait
{
    /**
     * Paging data
     *
     * @param LengthAwarePaginator $paginator
     * @return array
     */
    protected function toPagingData(LengthAwarePaginator $paginator, \Closure $callback = null)
    {
        if ($callback) {
            $data = $callback($paginator);
        }
        else {
            $data = $paginator->items();
        }

        return [
            'PageInfo' => $paginator->total() ? $this->_calc_paging($paginator->currentPage(), $paginator->lastPage(), $paginator->perPage(), $paginator->firstItem(), $paginator->lastItem(), $paginator->total()) : new \stdClass(),
            'Items'    => $data ?: []
        ];
    }

    /**
     * Generate the URL to a named routes.
     *
     * @param  int  $curPage
     * @param  int  $numPage
     * @param  int  $perPage
     * @return array
     */
    private function _calc_paging($curPage, $numPage, $perPage, $from, $to, $totalItem)
    {
        $show  = 4;
        $mid   = $show / 2;

        if ($numPage - $curPage > $mid)
        {
            $start = $curPage - $mid;
            $start = $start > 1 ? $start : 1;
            $show  = $show - ($curPage - $start);
            $end   = $curPage + $show;
            $end   = $end < $numPage ? $end : $numPage;
        }
        else
        {
            $end = $curPage + $mid;
            $end = $end < $numPage ? $end : $numPage;
            $show  = $show - ($end - $curPage);
            $start = $curPage - $show;
            $start = $start > 1 ? $start : 1;
        }

        if ($end < $start) {
            $end = $start;
        }

        return (object) [
            'total'        => $totalItem,
            'itemPerPage'  => $perPage,
            'from'         => $from,
            'to'           => $to,
            'currentPage'  => $curPage,
            'firstPage'    => 1,
            'lastPage'     => $numPage,
            'previousPage' => $curPage > 1 ? $curPage - 1 : null,
            'nextPage'     => $curPage < $numPage ? $curPage + 1 : null,
            'pageRange'    => range($start, $end)
        ];
    }
}
