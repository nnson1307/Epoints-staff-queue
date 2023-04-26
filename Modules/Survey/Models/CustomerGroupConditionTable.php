<?php
/**
 * Created by PhpStorm.
 * User: SonVeratti
 * Date: 3/17/2018
 * Time: 1:26 PM
 */

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerGroupConditionTable extends Model
{   
    protected $connection = BRAND_CONNECTION;
    protected $table = 'customer_group_condition';
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'description', 'created_at', 'updated_at'
    ];


    public function add(array $data)
    {
        $oSelect = $this->create($data);
        return $oSelect->id;
    }

    public function getAll($data)
    {
        $oSelect = $this->select('id', 'name')
            ->whereNotIn('id', $data)
            ->get();
        return $oSelect;
    }
}