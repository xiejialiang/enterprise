<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    //
    protected $table = 'action_log';

    protected $primaryKey  = 'id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['uid','log_info','ip_address','log_type','add_time'];

    private $name_s = '';


    public function user(){
        return $this->belongsTo('App\Http\Models\Users','uid')->select('id','name');
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

    public function  scopeNamesearch($q){
        if($this->name_s = ''){ return; }
        return $q->whereHas('user',function($q){
            $q->where('name', 'like', '%'.$this->name_s.'%');
        });
    }

    public function scopeOrderSort($q){

       $q->with('user')->orderBy('id');
    }


}

