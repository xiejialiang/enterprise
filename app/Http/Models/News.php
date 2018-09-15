<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    //

    protected $table = 'news';

    protected $primaryKey  = 'id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['title','content','cateid','uid','istop','status','carete_time','update_time','type','enterid'];


    public function user(){
        return $this->belongsTo('App\Http\Models\Users','create_id')->select('id','name');
    }

    private $name_s='';

    private $status_s='';

    public function scopeSearch($q,array $param){
        if (!empty($param)) {
            extract ($param ,EXTR_PREFIX_SAME ,'laravel' );
            if (isset($name)) {
                $this->name_s = $name;
            }
            if (isset($status)) {
                $this->status_s = $status;
            }

        }
        return self::namesearch()->statusearch()->orderSort();
    }
    public function scopeNamesearch($q){
        if($this->name_s == ""){return;}
        return $q->where('title', 'like', '%'.$this->name_s.'%');

    }

    public function scopeStatusearch($q){
        if($this->status_s == ""){return;}
        return $q->where('status',$this->status_s);

    }

    public function scopeOrderSort($q){
        return $q->with('user')->orderBy('carete_time','desc');
    }
}
