<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    //
    protected $table = 'advertisement';

    protected $primaryKey  = 'id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['title','price','ad_url','add_time'];

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

    //用户名查询
    public function scopeNamesearch($q){
        if($this->name_s == ""){return;}
        return  $q->where('title','like','%'.$this->name_s.'%');
    }

    //默认排序
    public function scopeOrderSort($q){
        return $q->orderBy('add_time');
    }

}
