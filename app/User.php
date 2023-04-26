<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'tenant_id', 'customer_code', 'ship_to_code', 'store_id', 'imei', 'outlet_id'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'updated_at'
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value;
    }


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'tenant_id' => 'Dai dep trai'
            //'firstname' => $this->first_name,
            //'lastname' => $this->last_name,
            //'email' => $this->email
        ];
    }

    /**
     * Lấy thông tin outlet
     *
     * @return array
     */
    public function getOutletInfo()
    {
        $oUser = auth()->user();

        return [
            'customer_code' => $oUser->customer_code,
            'ship_to_code'  => $oUser->ship_to_code,
            'store_id'      => $oUser->store_id,
            'outlet_id'     => $oUser->outlet_id
        ];
    }

    /**
     * Lấy thông tin thiet bi
     *
     * @return array
     */
    public function getDeviceInfo()
    {
        $oUser = auth()->user();

        return [
            'imei'      => $oUser->imei,
            'store_id'  => $oUser->store_id,
            'outlet_id' => $oUser->outlet_id
        ];
    }
}
