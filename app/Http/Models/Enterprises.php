<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Enterprises extends Model
{
    //
    protected $table = 'enterprises';

    protected $primaryKey  = 'id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['enterprise_name','enterprise_phone','content','createid','business_time','create_time','status','update_time','enterprise_image','seq','person_information','is_hot'];


    public function user(){
        return $this->belongsTo('App\Http\Models\Users','create_id')->select('id','name');
    }

    private $name_s='';

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
        return $q->where('enterprise_name', 'like', '%'.$this->name_s.'%');

    }

    public function scopeOrderSort($q){
        return $q->with('user')->orderBy('seq','desc')->orderBy('add_time','desc');
    }
}
