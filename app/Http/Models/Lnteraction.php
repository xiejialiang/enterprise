<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Lnteraction extends Model
{
    //
    protected $table = 'lnteraction';

    protected $primaryKey  = 'id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['enter_name','business_img','mobile','region_id','detailed_address','title','content','add_time','uid','cate_id'];

    private $name_s='';

    private $cate_s='';
    public function scopeSearch($q,array $param){
        if (!empty($param)) {
            extract ($param ,EXTR_PREFIX_SAME ,'laravel' );
            if (isset($name)) {
                $this->name_s = $name;
            }
            if (isset($name)) {
                $this->cate_s = $cate_id;
            }

        }
        return self::namesearch()->orderSort();
    }
    public function scopeNamesearch($q){
        if($this->name_s == ""){return;}
        return $q->where('title', 'like', '%'.$this->name_s.'%');

    }

    public function scopeOrderSort($q){
        return $q->where('cate_id',$this->cate_s)->orderBy('add_time','desc');
    }
}
