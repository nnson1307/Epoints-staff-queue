<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17/03/2018
 * Time: 2:45 PM
 */

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use MyCore\Models\Traits\ListTableTrait;

/**
 * User Model
 *
 * @author isc-daidp
 * @since Feb 23, 2018
 */
class MemberLevelTable extends Model
{

    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'member_levels';
    protected $primaryKey = "member_level_id";


    protected $fillable = [
        'member_level_id', 'name', 'slug', 'code', 'point', 'discount',
        'description', 'is_actived', 'created_by', 'updated_by', 'created_at',
        'updated_at', 'is_deleted'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    protected function _getList()
    {
        $oSelect = $this->select(
            'member_level_id',
            'name',
            'point',
            'is_actived',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
            'discount'
        )
            ->where('is_deleted', '=', 0);
        return $oSelect;
    }


    /**
     * Remove user
     *
     * @param number $id
     */
    public function remove($id)
    {
        return $this->where($this->primaryKey, $id)->update(['is_deleted' => 1]);

    }

    /**
     * Insert user to database
     *
     * @param array $data
     * @return number
     */
    public function add(array $data)
    {
        $oUser = $this->create($data);

        return $oUser->id;
    }

    public function edit(array $data, $id)
    {
        return $this->where($this->primaryKey, $id)->update($data);
    }

    /**
     * Function get item
     */
    public function getItem($id)
    {
        return $this->where($this->primaryKey, $id)->first();
    }

    /**
     * Function get option member level
     */
    public function getOptionMemberLevel()
    {
        return $this->select('member_level_id', 'name', 'point')->get();
    }

    //function bat trung ten
    public function testName($name, $id)
    {
        return $this->where('slug', $name)->where('member_level_id', '<>', $id)->where('is_deleted', 0)->first();
    }

    /**
     * @param $point
     * @return mixed
     */
    public function rankByPoint($point)
    {
        $ds = $this
            ->select(
                'member_level_id',
                'name',
                'slug',
                'code',
                'point',
                'discount'
            )
            ->where('point', '<=', $point)
            ->where('is_deleted', 0)
            ->orderBy('point', 'desc')
            ->get();
        return $ds;
    }
    public function getAll()
    {
        $oSelect = $this->select(
            'member_level_id',
            'name'
        )
            ->where('is_deleted', '=', 0)
            ->where('is_actived', '=', 1);
        return $oSelect->get()->toArray();
    }
}