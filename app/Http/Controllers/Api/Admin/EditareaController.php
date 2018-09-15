<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Http\Models\Editarea;
use Tymon\JWTAuth\Facades\JWTAuth;

class EditareaController extends Controller
{
    use Helpers;
    private $max = 5;
    //
    public function index(){
        $type =request()->input('type',1);
        $editareaList = Editarea::where('type',$type)->orderBy('seq','desc')->paginate(10);
        if(!empty($editareaList)){
           foreach ($editareaList as $k=>$v){
               $v->img =asset($v->imgpath);
           }
        }

        return $this->response->array($editareaList);
    }

    public function add_editarea(){

        if(request()->input('url')){
            $rules = [
                'url' => 'url',
            ];
            $messages = [
                'url.url' => 'url格式错误',
            ];
            $date['url']=request()->input('url');
            $validator = Validator::make($date, $rules, $messages);
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $v){
                    $msg_array=["message"=>$v[0],"status"=>0];
                    return $this->response->array($msg_array);
                }
            }
        }

        $count = Editarea::count();
        if($count >= $this->max){
            $msg_array=["message"=>"轮播图已满","status"=>0];
            return $this->response->array($msg_array);
        }
        $editarea = new Editarea();
        if(request()->hasFile('imgpath')) {
            $files = request()->file("imgpath");
            $editarea->imgpath =imgupload($files);
        }else{
            $msg_array=["message"=>"轮播图不能为空","status"=>0];
            return $this->response->array($msg_array);
        }
        $editarea->title=request()->input('title');
        $editarea->type =request()->input('type',1);
        $editarea->url =request()->input('url');
        $editarea->carete_id=JWTAuth::parseToken()->authenticate()->id;
        $editarea->create_time=date('Y-m-d H-i-s');
        $editarea->update_time=date('Y-m-d H-i-s');
        if($editarea->save()){
            $editarea->seq=$editarea->id;
            $editarea->save();
            setAdminLog("轮播图管理/添加/名称".$editarea->title);
            return $this->response->array([
                "message" => "添加成功",
                "status" =>1,
            ]);
        }else{
            return $this->response->array([
                "message" => "添加失败",
                "status" =>1,
            ]);
        }
    }

    public function edit_editarea($id){
        if(request()->input('url')){
            $rules = [
                'url' => 'url',
            ];
            $messages = [
                'url.url' => 'url格式错误',
            ];
            $date['url']=request()->input('url');
            $validator = Validator::make($date, $rules, $messages);
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $v){
                    $msg_array=["message"=>$v[0],"status"=>0];
                    return $this->response->array($msg_array);
                }
            }
        }


        $editarea =Editarea::where('id',$id)->first();
        if(isset($editarea->id)){
            if(request()->hasFile('imgpath')) {
                $files = request()->file("imgpath");
                $editarea->imgpath =imgupload($files);
            }
            $editarea->title=request()->input('title');
            $editarea->type =request()->input('type',1);
            $editarea->url =request()->input('url');
            $editarea->update_time=date('Y-m-d H-i-s');
            if($editarea->save()){

                setAdminLog("轮播图管理/修改/名称".$editarea->title);
                return $this->response->array([
                    "message" => "修改成功",
                    "status" =>1,
                ]);
            }else{
                return $this->response->array([
                    "message" => "修改失败",
                    "status" =>1,
                ]);
            }
        }else{
            return $this->response->array([
                "message" => "数据错误",
                "status" =>0,
            ]);
        }

    }

    public function del_editarea($id){
        $editarea =Editarea::where('id',$id)->first();
        if(isset($editarea->id)){
            if($editarea->delete()){
                return $this->response->array([
                    "message" => "删除成功",
                    "status" =>0,
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

    public function editaera_isshow($id){
        $editarea =Editarea::where('id',$id)->first();
        $status = request()->input('status');

        if(isset($editarea->id)){
            if($status == 1){
                $name ="显示";
            }else{
                $name ="隐藏";
            }
            $editarea->status=$status;
            if($editarea->save()){
                return $this->response->array([
                    "message" => "轮播图".$name."成功",
                    "status" =>0,
                ]);
            }else{
                return $this->response->array([
                    "message" => "轮播图".$name."失败",
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

    public function editaera_type(){
        $editaera_type=['1'=>'首页轮播'];
        return $this->response->array($editaera_type);
    }

    public function editaera_info(){
        $id=request()->input('id');
        $editarea =Editarea::where('id',$id)->first();
        return $this->response->array([
            "id"=>$editarea->id,
            "title" => $editarea->title,
            "imgpath" =>asset($editarea->imgpath),
        ]);
    }

    public function editarea_seq(){
        $id = abs(intval(request()->input('id',0)));
        $page = abs(intval(request()->input('page',1)));


        $p = trim(request()->input('p','down'));
        $editarea = Editarea::find($id);
        $seq = "desc";
        if($p == "up"){
            $seq = "asc";
        }
        $return_arr = ["page"=>$page];
        $rs = Editarea::orderBy("seq",$seq)->get();

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
                $Editarea = Editarea::findOrFail($ax->id);
                $Editarea->seq = $tem['seq'];
                $Editarea->save();
                $Editarea = Editarea::findOrFail($tem['id']);
                $Editarea->seq = $ax->seq;
                $Editarea->save();
                break;
            }
        }

        return $this->response->array([
            "return_arr" => $return_arr,
            "status" =>1,
        ]);
    }
}
