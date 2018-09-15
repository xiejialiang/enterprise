<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Perm extends Model
{
    //
    protected $table = 'perm';

    protected $primaryKey  = 'id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['perm_type','perm_name','perm','parent_id','grade','is_show'];


    /*
    * 权限名称
    * @var string
    */
    private $name_s = '';

    public function p_name(){
        return $this->belongsTo('App\Http\Models\Perm','parent_id','id')->select('id','perm_name');
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

    //用户名查询
    public function scopeNamesearch($q){

        if($this->name_s == ""){return;}
        return  $q->where('perm_name','like','%'.$this->name_s.'%')->where('is_show',1);

    }


    //默认排序
    public function scopeOrderSort($q){
        return $q->with('p_name')->orderBy('id', 'desc');
    }


}
