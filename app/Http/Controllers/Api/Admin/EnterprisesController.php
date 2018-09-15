<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Http\Models\Enterprises;
use Tymon\JWTAuth\Facades\JWTAuth;
class EnterprisesController extends Controller
{
    use Helpers;
    //
    public function index(){

        $condition = [
            'name'=>trim(request()->input('name','')),
        ];
        $enterList = Enterprises::search($condition)->paginate(10);

        return $this->response->array($enterList);
    }

    public function add_enter(){

        $rules=[
            'enterprise_name' => 'required|unique:enterprises,enterprise_name',
            'enterprise_phone'=>  'required|mobile|unique:enterprises,enterprise_phone',
            'content' =>'required'
        ];
        $messages = [
            'enterprise_name.required' => '企业名称不能为空',
            'enterprise_name.unique' => '企业名称已存在',
            'enterprise_phone.required'=>'电话不能为空',
            'enterprise_phone.mobile'=>'电话格式错误',
            'enterprise_phone.unique'=>'电话已经存在',
            'content.required'=>'企业简介不能为空',

        ];

        $validator = Validator::make(request()->all(), $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=["message"=>$v[0],"status"=>0];
                return $this->response->array($msg_array);
            }
        }
        $enter = new Enterprises;
        $enter->enterprise_name=request()->input('enterprise_name');
        $enter->enterprise_phone=request()->input('enterprise_phone');
        $enter->content=request()->input('content');
        if(request()->hasFile('enterprise_image')) {
            $files = request()->file("enterprise_image");
            $enter->enterprise_image =imgupload($files);
        }

        $enter->createid=JWTAuth::parseToken()->authenticate()->id;
        $enter->create_time=date('Y-m-d H-i-s');

        if($enter->save()){
            $enter->seq=$enter->id;
            $enter->save();
            setAdminLog("企业管理/添加/名称".$enter->enterprise_name);
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

    public function enter_info($id){

        $enter =Enterprises::where('id',$id)->first();
        if(isset($enter->id)){
            return $this->response->array([
                "id"=>$enter->id,
                "name" => $enter->enterprise_name,
                "phone"=>$enter->enterprise_phone,
                "content"=>$enter->content,
                "sta"=>$enter->status,
                "status" =>1,
            ]);
        }else{
            return $this->response->array([
                "message" => "数据错误",
                "status" =>0,
            ]);
        }
    }

    public function edit_enter($id,Request $request){

        $rules=[
            'enterprise_name' => 'required|unique:enterprises,enterprise_name,'.$id,
            'enterprise_phone'=>  'required|mobile|unique:enterprises,enterprise_phone,'.$id,
            'content' =>'required'
        ];
        $messages = [
            'enterprise_name.required' => '企业名称不能为空',
            'enterprise_name.unique' => '企业名称已存在',
            'enterprise_phone.required'=>'电话不能为空',
            'enterprise_phone.mobile'=>'电话格式错误',
            'enterprise_phone.unique'=>'电话已经存在',
            'content.required'=>'企业简介不能为空',

        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=["message"=>$v[0],"status"=>0];
                return $this->response->array($msg_array);
            }
        }
        $enter = Enterprises::where('id',$id)->first();
        if(isset($enter->id)){

            $enter->enterprise_name=request()->input('enterprise_name');
            $enter->enterprise_phone=request()->input('enterprise_phone');
            $enter->content=request()->input('content');
            if(request()->hasFile('enterprise_image')) {
                $files = request()->file("enterprise_image");
                $enter->enterprise_image =imgupload($files);
            }
            $enter->update_time=date('Y-m-d H-i-s');
            if($enter->save()){
                setAdminLog("企业管理/修改/名称".$enter->enterprise_name);
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

        }else{
            return $this->response->array([
                "message" => "数据错误",
                "status" =>0,
            ]);
        }
    }

    public  function del_enter($id){

        $enter=Enterprises::where('id',$id)->first();
        if(isset($enter->id)){
            if($enter->delete()){
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

    public function enter_release($id){
        $enter=Enterprises::where('id',$id)->first();
        if(isset($enter->id)){
            $status=request()->input('status');
            $enter->status=$status;
            if($status == 1){
                $msg_name ="发布";
            }else{
                $msg_name ="取消发布";
            }
            if($enter->save()){
                return $this->response->array([
                    "message" => "".$msg_name."成功",
                    "status" =>1,
                ]);
            }else{
                return $this->response->array([
                    "message" => "".$msg_name."失败",
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

    public function  enter_seq(){
        $id = abs(intval(request()->input('id',0)));
        $page = abs(intval(request()->input('page',1)));


        $p = trim(request()->input('p','down'));
        $detail = Enterprises::find($id);
        $seq = "desc";
        if($p == "up"){
            $seq = "asc";
        }
        $return_arr = ["page"=>$page];
        $rs = Enterprises::orderBy("seq",$seq)->get();

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
                $Editarea = Enterprises::findOrFail($ax->id);
                $Editarea->seq = $tem['seq'];
                $Editarea->save();
                $Editarea = Enterprises::findOrFail($tem['id']);
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
