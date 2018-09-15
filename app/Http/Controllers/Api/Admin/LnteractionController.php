<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Http\Models\Lnteraction;
use App\Http\Models\Category;


class LnteractionController extends Controller
{
    use Helpers;
    //
     public  function  index(){
         $condition = [
             'name'=>trim(request()->input('name','')),
             'cate_id'=>trim(request()->input('cate_id',''))
         ];
         $lnteraction = Lnteraction::search($condition)->paginate(10);

         return $this->response->array($lnteraction);
     }

     public function lnteract_cate(){
         $cate=Category::where('type',2)->where('status',1)->where('pid',0)->orderBy('seq','desc')->select('id','cate_name','type')->get()->toArray();
         return $this->response->array($cate);
     }

     public function  del_lnteract($id){
        $lnter =Lnteraction::where('id',$id)->first();
        if(isset($lnter->id)){
            if($lnter->delete()){
                return $this->response->array([
                    "message" => "删除成功",
                    "status" =>1,
                ]);
            }else{
                return $this->response->array([
                    "message" => "删除失败",
                    "status" =>0,
                ]);
            }
        }else{
            return $this->response->array([
                "message" => "数据错误",
                "status" =>0,
            ]);
        }
     }

    public function lnteract_info($id){
        $lnter =Lnteraction::where('id',$id)->first();
        if(isset($lnter->id)){
            return $this->response->array($lnter);
        }else{
            return $this->response->array([
                "message" => "数据错误",
                "status" =>0,
            ]);
        }

    }
}
