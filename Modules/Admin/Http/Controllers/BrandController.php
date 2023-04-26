<?php


/**
 * @Author : VuND
 */

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Repositories\Brand\BrandInterface;

class BrandController extends Controller
{

    protected $repoBrand;

    public function __construct(BrandInterface $brand)
    {
        $this->repoBrand = $brand;
    }

    public function getAll(Request $request){
        $filter = $request->all();

        $arrData = $this->repoBrand->getAllBrand($filter);

        return $this->responseJson(CODE_SUCCESS, 'success',$arrData);
    }

    public function getAllBySocial(Request $request){
        $filter = $request->all();

        $arrData = $this->repoBrand->getAllBrandBySocial($filter);

        return $this->responseJson(CODE_SUCCESS, 'success',$arrData);
    }

    /**
     * Lấy ds brand bằng client key
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBrandByClient(Request $request)
    {
        $data = $this->repoBrand->getBrandByClient($request->all());

        return $this->responseJson(CODE_SUCCESS, 'success', $data);
    }

}
