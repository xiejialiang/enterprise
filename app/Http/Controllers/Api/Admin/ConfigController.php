<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Config;
use Illuminate\Support\Facades\Validator;
use Dingo\Api\Routing\Helpers;
use Tymon\JWTAuth\Facades\JWTAuth;

class ConfigController extends Controller
{
    use Helpers;
    //
    public function index(){
        $condition = [
            'name'=>trim(request()->input('name',''))
        ];
        $configList = Config::search($condition)->paginate(10);

        return $this->response->array($configList);
    }

    public  function add_config(Request $request){

        $add_type =$request->input('add_type');

        if($add_type == 1){

            $rules =['name'=>'required|unique:config,name'];
            $messages = [
                'name.required'=>'名称不能为空',
                'name.unique'=>'名称不能重复',
            ];
            $date['name']=request()->input('name');

            $validator = Validator::make($date, $rules, $messages);

            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $v){
                    $msg_array=['message'=>$v[0],'status'=>0];
                    return $this->response->array($msg_array);
                }
            }

            $config =new Config();
            $config->name =$date['name'];
            $config->is_show =1;
            if($config->save()){
                setAdminLog("网站配置/添加/菜单".$config->name);
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
        }else{
            $rules =['name'=>'required|unique:config,name',
                    'parent_id'=>'required',
                    'type'=>'required|unique:config,type',
                    'dom_type'=>'required'
                    ];
            $messages = [
                'name.required'=>'名称不能为空',
                'name.unique'=>'名称不能重复',
                'parent_id.required'=>'所属菜单不能为空',
                'type.required'=>'配置类型不能为空',
                'type.unique'=>'配置类型不能重复',
                'dom_type'=>'标签类型不能为空'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $v){
                    $msg_array=['message'=>$v[0],'status'=>0];
                    return $this->response->array($msg_array);
                }
            }
            $date['name']=request()->input('name');
            $config =new Config();
            $config->name =request()->input('name');
            $config->type =request()->input('type');
            $config->parent_id =request()->input('parent_id');
            $config->dom_type =request()->input('dom_type');
            $config->msg =request()->input('msg');
            $config->is_show =1;
            if($config->save()){
                setAdminLog("网站配置/添加/子菜单".$config->name);
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
    }


    public function config_info(Request $request){
        $id=$request->input('id');
        $p_config=Config::where('parent_id',0)->where('is_show',1)->select('id','name')->get();
        $config_info=Config::where('id',$id)->first();
        $add_type =$request->input('add_type');
        $d_type =['1'=>'文本','2'=>'单选按钮','3'=>'上传图片','4'=>'文本域','5'=>'编辑器'];
        if(isset($config_info->id)){
            if($add_type == 1){
                return $this->response->array([
                    "p_config"=>$p_config,
                    "id" => $config_info->id,
                    "name" => $config_info->name,
                    "status" =>1,
                ]);

            }else{
                return $this->response->array([
                    "p_config"=>$p_config,
                    "id" => $config_info->id,
                    "name" => $config_info->name,
                    "type" => $config_info->type,
                    "dom_type"=>$config_info->dom_type,
                    "msg"=>$config_info->msg,
                    "d_type"=>$d_type,
                    "status" =>1,
                ]);
            }

        }else{
            return $this->response->array([
                "p_config" => $p_config,
                "d_type"=>$d_type,
                "status" =>1,
            ]);
        }
    }


    public function edit_config($id,Request $request)
    {
        $add_type = $request->input('add_type');
        $config=Config::where('id',$id)->first();
        if ($add_type == 1) {

            $rules = ['name' => 'required|unique:config,name,'.$id];
            $messages = [
                'name.required' => '名称不能为空',
                'name.unique' => '名称不能重复',
            ];
            $date['name'] = request()->input('name');

            $validator = Validator::make($date, $rules, $messages);

            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $v) {
                    $msg_array = ['message' => $v[0], 'status' => 0];
                    return $this->response->array($msg_array);
                }
            }
            $config->name =$date['name'];

            if($config->save()){
                setAdminLog("网站配置/修改/菜单".$config->name);
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
            $rules =['name'=>'required|unique:config,name,'.$id,
                'type'=>'required|unique:config,type,'.$id,
                'dom_type'=>'required'
            ];
            $messages = [
                'name.required'=>'名称不能为空',
                'name.unique'=>'名称不能重复',
                'type.required'=>'配置类型不能为空',
                'type.unique'=>'配置类型不能重复',
                'dom_type'=>'标签类型不能为空'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $v){
                    $msg_array=['message'=>$v[0],'status'=>0];
                    return $this->response->array($msg_array);
                }
            }

            $config->name =request()->input('name');
            $config->type =request()->input('type');
            $config->dom_type =request()->input('dom_type');
            $config->msg =request()->input('msg');
            if($config->save()){
                setAdminLog("网站配置/修改/子菜单".$config->name);
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
    }

    public function config_isshow($id,Request $request){
        $config_info=Config::where('id',$id)->first();
        $is_show =abs(intval($request->input('is_show')));
        if(isset($config_info->id)){
            $config_info->is_show=$is_show;
            if($is_show == 1){
                $s_name="显示";
            }else{
                $s_name="隐藏";
            }
            if($config_info->save()){
                setAdminLog("网站配置/".$s_name."/子菜单".$config_info->name);
                return $this->response->array([
                    "message" => "配置菜单".$s_name."成功",
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

    public function del_config($id){
        $config_info=Config::where('id',$id)->first();
        if(isset($config_info->id)){

            if($config_info->delete()){
                setAdminLog("网站配置/删除/菜单".$config_info->name);
                return $this->response->array([
                    "message" => "配置菜单删除成功",
                    "status" =>1,
                ]);
            }else{
                return $this->response->array([
                    "message" => "配置菜单删除失败",
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


    public function sonconfig_list(){
        $sonconfig=Config::where('parent_id','<>',0)->where('is_show',1)->get();
        return $this->response->array($sonconfig);
    }

    public function add_sonconfig(){
        $date =request()->all();
        if(count($date)){
            foreach ($date as $k=>$v ){
                if(request()->hasFile($k)) {
                    $files = request()->file($k);
                    $image =imgupload($files);
                    Config::where('type',$k)->update(['value'=>$image]);
                }else{
                    Config::where('type',$k)->update(['value'=>$v]);
                }
            }
        }
        return $this->response->array([
            "message" => "修改成功",
            "status" =>1,
        ]);
    }
}
