<?php


/**
 * @Author : VuND
 */

namespace Modules\Admin\Models\Backoffice;


use Illuminate\Database\Eloquent\Model;

class BrandTable extends Model
{
    protected $table = 'brand';
    protected $primaryKey = 'brand_id';

    public function getAll($filter = []){
        $oSelect = $this
            ->where('is_published', 1)
            ->where('is_activated', 1)
            ->where('is_deleted', 0);
        if(isset($filter['brand_id']) && $filter['brand_id'] != ''){
            $oSelect->where('brand_id', $filter['brand_id']);

            return $oSelect->first()->toArray();
        }

        if($oSelect->count()){
            return $oSelect->get()->toArray();
        }

        return [];
    }

    public function getAllBySocial($filter = []){
        $oSelect = $this->select('brand.*', 'channel_social_id')
            ->join('channel_master', 'channel_master.tenant_id', '=', 'brand.tenant_id')
            ->where('is_published', 1)
            ->where('is_activated', 1)
            ->where('is_deleted', 0);
        if(isset($filter['brand_id']) && $filter['brand_id'] != ''){
            $oSelect->where('brand_id', $filter['brand_id']);

            return $oSelect->first()->toArray();
        }

        if($oSelect->count()){
            return $oSelect->get()->toArray();
        }

        return [];
    }

    /**
     * Lấy ds brand bằng client key
     *
     * @param array $filter
     * @return mixed
     */
    public function getBrandByClient($filter = [])
    {
        return $this
            ->where("is_published", 1)
            ->where("is_activated", 1)
            ->where("is_deleted", 0)
            ->where("client_key", $filter['client_key'])
            ->get();
    }
}
