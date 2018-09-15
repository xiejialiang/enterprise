<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table = 'cagetory';

    protected $primaryKey  = 'id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['cate_name','cate_image','cate_banner','pid','cate_content','type','carete_time','update_time','status','seq','lv'];


    public function user(){
        return $this->belongsTo('App\Http\Models\Users','uid');
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
        return self::orderSort();
    }


    //默认排序
    public function scopeOrderSort($q){
        return $q->where('status',1)->orderBy('seq');
    }
    //分类
    public  static function CategoryList($type =1,$pid = 0){

        $res = Category::where('pid',$pid)->where('type',$type)->where('status',1)->select('id','cate_name','type')->orderBy('seq','asc')->get()->toArray();

       return $res;
    }

    //添加分类列表
    public static function infinites($type=1,$pid=0,$default=null,$children_id_limit=""){
        $type = abs(intval($type));
        $default = abs(intval($default));
        $children_option_str ='';
        if(empty($default)){
            $children_option_str = '<option  value="0"  selected="selected">请选择</option>';
        }
        //查出一级分类到id和名称
        $data = Category::where('pid',$pid)->where('type',$type)->where('status',1)->select('id','cate_name','pid')->orderBy('seq','asc')->get();
        if(!empty($data)){
            foreach($data as $k=>$v ){
                $children_option_str .='<option  value="'.$v['id'].'"'.((!empty($default) && $default == $v['id'])?' selected="selected"':'').($pid>0?' parent="'.$pid.'"':'').'>'.$children_id_limit.$v['cate_name'].'</option>';
            }
        }
        return $children_option_str;
    }
}
