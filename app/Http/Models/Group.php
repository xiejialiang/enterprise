<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    //
    protected $table = 'group';

    protected $primaryKey  = 'group_id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['group_name','group_perm','create_id'];

    public function user(){
        return $this->belongsTo('App\Http\Models\Users','create_id')->select('id','name');
    }

    /*
    * 权限名称
    * @var string
    */
    private $name_s = '';


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
        return $q->whereHas('user',function($q){
            $q->where('group_name', 'like', '%'.$this->name_s.'%');
        });
    }

    public function scopeOrderSort($q){
        return $q->with('user')->orderBy('group_id','desc');
    }
}
