<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Tymon\JWTAuth\Contracts\JWTSubject;



class Users extends Authenticatable implements JWTSubject
{
    //

    use Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    protected $primaryKey  = 'id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['nickname','name','email','password','phone','avatar','sign','roleid','status','last_ip','add_time','update_time','last_login'];

    protected $hidden = ['password'];

    public function group(){
        return $this->belongsTo('App\Http\Models\Group','roleid','group_id');
    }
    /*
    * 名称
    * @var string
    */
    private $name_s = '';

    public function getJWTIdentifier(){
        return $this->getKey();
    }

    public function getJWTCustomClaims(){
        return [];
    }


    public function scopeSearch($q,array $param){
        if (!empty($param)) {
            extract ($param ,EXTR_PREFIX_SAME ,'laravel' );
            if (isset($name)) {
                $this->name_s = $name;
            }

        }
        return self::namesearch()->orderSort();
    }

    public function scopeNamesearch($q){
        if($this->name_s == ""){return;}
        return $q->where('name', 'like', '%'.$this->name_s.'%');

    }

    public function scopeOrderSort($q){
        return $q->with('group')->orderBy('id','desc');
    }

}
