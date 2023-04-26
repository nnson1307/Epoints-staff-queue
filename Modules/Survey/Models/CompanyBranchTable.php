<?php


namespace Modules\Survey\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use MyCore\Models\Traits\ListTableTrait;

class CompanyBranchTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'company_branch';
    protected $primaryKey = 'company_branch_id';
    protected $fillable = [
        'company_branch_id',
        'company_id',
        'company_branch_code',
        'company_branch_name',
        'country_id',
        'province_id',
        'district_id',
        'ward_id',
        'address',
        'phone',
        'erp_code',
        'tax',
        'longitude',
        'latitude',
        'is_dms',
        'is_active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];
    const IS_ACTIVE = 1;
    public function getListCore(&$filters){
        $oSelect = $this
            ->join('company','company.company_id',$this->table.'.company_id')
            ->select($this->table.'.*', 'company.company_name', 'company.company_code');
        return $oSelect->orderBy('company_branch_id','DESC');
    }

    public function checkCode($code,$id = null) {
        $oSelect = $this->where('company_branch_code',$code);
        if ($id != null){
            $oSelect = $oSelect->where('company_branch_id','<>',$id);
        }
        return $oSelect->get();
    }

    public function createCompanyBranch($data){
        $oSelect = $this->insertGetId($data);
        return $oSelect;
    }

    public function updateCompanyBranch($data,$id) {
        $oSelect = $this->where('company_branch_id',$id)->update($data);
        return $oSelect;
    }

    public function getDetail($id)
    {
        $oSelect = $this
            ->select($this->table.'.*', 'company.company_name')
            ->join('company','company.company_id', $this->table . '.company_id');
        if (is_array($id)) {
            $oSelect->whereIn($this->table . '.company_branch_id', $id);
            return $oSelect->get();
        } else {
            $oSelect->where($this->table . '.company_branch_id', $id);
            return $oSelect->first();
        }
    }
    public function getAll(){
        $oSelect = $this
            ->join('company','company.company_id', $this->table.'.company_id')
            ->where($this->table . '.is_active',self::IS_ACTIVE)
            ->orderBy('company_branch_id','DESC')->get();
        return $oSelect;
    }
    public function getListCompanyBranch($filter){
        $select = $this
            ->where('is_active',self::IS_ACTIVE);
//        if ($filter) {
//            if (isset($filter['keyword_company_branch$company_branch_code']) && $filter['keyword_company_branch$company_branch_code']) {
//                $select = $select->where('company_branch_code', 'like', '%' . $filter['keyword_company_branch$company_branch_code'] . '%');
//            }
//            if (isset($filter['keyword_company_branch$company_branch_name']) && $filter['keyword_company_branch$company_branch_name']) {
//                $select = $select->where('company_branch_name', 'like', '%' . $filter['keyword_company_branch$company_branch_name'] . '%');
//            }
//        };
//
//        unset($filter['keyword_company_branch$company_branch_code']);
//        unset($filter['keyword_company_branch$company_branch_name']);
        return $select->get();

    }

}
