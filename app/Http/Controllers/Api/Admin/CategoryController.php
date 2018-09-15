<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Http\Models\Category;
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryController extends Controller
{
    use Helpers;
    //

    public function index(){


        $type =abs(intval(request()->input('type',1)));

        $catlist = Category::where('type',$type)->where('pid',0)->where('status',1)->orderBy('seq','desc')->get();
        if($catlist->count() > 0){
            foreach($catlist as $k=>$v){
                if($k == 0){
                    $v->first ="one";
                }else if($k == $catlist->count()-1){
                    $v->first ="end";
                }
                $v->children = Category::where('type',$type)->where('pid',$v->id)->where('status',1)->orderBy('seq','desc')->get();
                if($v->children->count() > 0){
                    foreach ($v->children as $k1=>$v1){
                        if($k1 == 0){
                            $v1->chfirst ="childone";
                        }else if($k1 == $v->children->count()-1){
                            $v1->chfirst ="childend";
                        }
                    }
                }
            }
        }
        $data=[
            'catlist'=>$catlist,
            'type'=>$type,
        ];

        return $this->response->array($data);
    }


    public function add_cate(Request $request){
        $rules = [
            'cate_name' => 'required',
        ];
        $messages = [
            'cate_name.required' => '分类不能为空',
        ];
        $date['cate_name'] =request()->input('cate_name');
        $validator = Validator::make($date, $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=["message"=>$v[0],"status"=>0];
                return $this->response->array($msg_array);
            }
        }

        $cate = new Category();
        $date['type']=$request->input('type');
        $c_name =Category::where('type',$date['type'])->where('cate_name',$date['cate_name'])->where('status',1)->select('id')->first();
        if(isset($c_name->id)){
            $msg_array=["message"=>'分类名称已存在',"status"=>0];
            return $this->response->array($msg_array);
        }
        if(request()->hasFile('cate_image')) {
            $file = request()->file("cate_image");
            $cate->cate_image =imgupload($file);

        }
        if(request()->hasFile('cate_banner')) {
            $files = request()->file("cate_banner");
            $cate->cate_banner =imgupload($files);
        }
        $date['cate_content']=$request->input('cate_content');
        $date['pid'] =$request->input('pid');

        $cate->cate_name =$date['cate_name'];
        $cate->type =$date['type'];
        $cate->cate_content =$date['cate_content'];
        $cate->pid =$date['pid'];
        $cate->create_id =JWTAuth::parseToken()->authenticate()->id;
        $cate->create_time =date("Y-m-d H-i-s");
        $cate->update_time =date("Y-m-d H-i-s");
        $cate->status =1;

        if($cate->save()){
            $cate->seq =$cate->id;
            $cate->save();
            setAdminLog("分类管理/添加/名称".$cate->cate_name);
            return $this->response->array([
                "message" => "添加成功",
                "status" =>1,
            ]);
        }else{
            return $this->response->array([
                "message" => "添加失败",
                "status" =>0,
            ]);
        }

    }

    public function  edit_cate($id){

        $rules = [
            'cate_name' => 'required|unique:cagetory,cate_name,'.$id,
        ];
        $messages = [
            'cate_name.required' => '分类不能为空',
            'cate_name.unique' => '分类名称已存在',
        ];
        $date['cate_name'] =request()->input('cate_name');

        $validator = Validator::make($date, $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=["message"=>$v[0],"status"=>0];
                return $this->response->array($msg_array);
            }
        }

        /*$catewhere =Category::where('id','<>',$id)->where('cate_name',request()->input('cate_name'))->first();
        if(isset($catewhere->id)){
            $msg_array=['message'=>'分类名称已存在','status'=>0];
            return $this->response->array($msg_array);
        }*/
        $cate =Category::where('id',$id)->first();
        $date['type']=request()->input('type');
        if(request()->hasFile('cate_image')) {
            $file = request()->file("cate_image");
            $cate->cate_image =imgupload($file);

        }
        if(request()->hasFile('cate_banner')) {
            $files = request()->file("cate_banner");
            $cate->cate_banner =imgupload($files);
        }
        $date['cate_content']=request()->input('cate_content');
        $date['pid'] =request()->input('pid');

        $cate->cate_name =$date['cate_name'];
        $cate->type =$date['type'];
        $cate->cate_content =$date['cate_content'];
        $cate->pid =$date['pid'];
        $cate->update_time =date("Y-m-d H-i-s");

        if($cate->save()){
            setAdminLog("分类管理/修改/名称".$cate->cate_name);
            return $this->response->array([
                "message" => "修改成功",
                "status" =>1,
            ]);
        }else{
            return $this->response->array([
                "message" => "修改失败",
                "status" =>0,
            ]);
        }


    }
    //添加、修改分类列表
    public function cate_list(){
        $Category =new Category;
        $type=request()->input('type');
        $id=request()->input('id');
        $catelist =Category::where('pid',0)->where('type',$type)->where('status',1)->select('id','cate_name','pid')->orderBy('create_time','asc')->orderBy('seq','asc')->get();
        $cate = $Category::where('id',$id)->first();
        if(isset($cate->id)){
            $result=['cate_name'=>$cate->cate_name,'catelist'=>$catelist,'id'=>$id,'pid'=>$cate->pid,'cate_image'=>asset($cate->cate_image),'cate_banner'=>asset($cate->cate_banner),'cate_content'=>$cate->cate_content,'type'=>$cate->type];
            return $this->response->array($result);
        }else{
            return $this->response->array($catelist);
        }
    }
    //删除分类
    public function del_cate($id){

            $cate =Category::where('id',$id)->where('status',1)->select('id','pid','cate_name')->first();
            if(isset($cate->id)){
                $catcount=Category::where("pid","=",$id)->where('status',1)->count();
                if($catcount > 0){
                    return $this->response->array([
                        "message" => "删除失败，请先删除其子类",
                        "status" =>0,
                    ]);
                }else{
                    $cate->status =0;
                    if($cate->save()){
                        return $this->response->array([
                            "message" => "删除分类成功",
                            "status" =>1,
                        ]);
                    }else{
                        return $this->response->array([
                            "message" => "删除分类失败",
                            "status" =>0,
                        ]);
                    }
                }
            }else{
                return $this->response->array([
                    "message" => "数据错误",
                    "status" =>0,
                ]);
            }


    }

    public function  cate_type(){
        $cate_type=['1'=>'资讯分类','政企互动'];
        $data=[
            'cate_type'=>$cate_type,
        ];

        return $this->response->array($data);
    }
    public function  category_seq(){
        $id = abs(intval(request()->input('id',0)));
        $page = abs(intval(request()->input('page',1)));


        $p = trim(request()->input('p','down'));
        $Category = Category::find($id);
        $seq = "desc";
        if($p == "up"){
            $seq = "asc";
        }
        $return_arr = ["page"=>$page];
        $rs = Category::orderBy("seq",$seq)->get();

        $tem =[];
        $tem['tag']='';
        $tem['id']='';
        $tem['seq']='';
        foreach ($rs as $ax){
            if($id == $ax->id){
                $tem['tag']='ok';
                $tem['id']=$ax->id;
                $tem['seq']=$ax->seq;
                continue;
            }

            if($tem['tag'] == 'ok'){
                $Category = Category::findOrFail($ax->id);
                $Category->seq = $tem['seq'];
                $Category->save();
                $Category = Category::findOrFail($tem['id']);
                $Category->seq = $ax->seq;
                $Category->save();
                break;
            }
        }

        return $this->response->array([
            "return_arr" => $return_arr,
            "status" =>1,
        ]);
    }
}
