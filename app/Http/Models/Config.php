<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    //
    protected $table = 'config';

    protected $primaryKey  = 'id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['name','type','value','parent_id','dom_type','msg','is_show'];



    public function Pname(){
        return $this->belongsTo('App\Http\Models\Config','parent_id','id')->select('id','name');
    }

    /*
   * 名称
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
        return $q->where('name', 'like', '%'.$this->name_s.'%');

    }

    public function scopeOrderSort($q){
        return $q->with('Pname')->orderBy('id');
    }
}
