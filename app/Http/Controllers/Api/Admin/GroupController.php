<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Perm;
use App\Http\Models\Group;
use Illuminate\Support\Facades\Validator;
use Dingo\Api\Routing\Helpers;
use Tymon\JWTAuth\Facades\JWTAuth;

class GroupController extends Controller
{
    use Helpers;
    //
    public function index(){
        $condition = [
            'name'=>trim(request()->input('name',''))
        ];
        $userList = Group::search($condition)->paginate(10);
        return $this->response->array($userList);
    }

    public  function  add_group(){

        $rules=[
            'group_name'=>'required|unique:group,group_name',
        ];
        $messages = [
            'group_name.required'=>'名称不能为空',
            'group_name.unique'=>'名称不能重复',
        ];
        $date['group_name']=request()->input('group_name');

        $validator = Validator::make($date, $rules, $messages);

        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }
        }
        $group = new Group();
        $group->group_name=$date['group_name'];
        $group->create_id=JWTAuth::parseToken()->authenticate()->id;
        $group->group_perm =implode(',',request()->input('group_perm'));
        if($group->save()){
            setAdminLog("用户管理/添加/角色".$group->group_name);
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

    public function edit_group($id){
        $rules=[
            'group_name'=>'required',
        ];
        $messages = [
            'group_name.required'=>'名称不能为空',
        ];
        $date['group_name']=request()->input('group_name');
        $validator = Validator::make($date, $rules, $messages);

        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }
        }
        $groupwhere =Group::where('group_id','<>',$id)->where('group_name',request()->input('group_name'))->first();
        if(isset($groupwhere->group_id)){
            $msg_array=['message'=>'角色名称已存在','status'=>0];
            return $this->response->array($msg_array);
        }
        $group =Group::where('group_id',$id)->first();

        if(isset($group->group_id)){
            $group->group_name=$date['group_name'];
            $group->group_perm =implode(',',request()->input('group_perm'));
            if($group->save()){
                setAdminLog("用户管理/修改/角色".$group->group_name);
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
                "message" => "修改失败",
                "status" =>0,
            ]);
        }

    }



    public function group_info(){
        $id=request()->input('id');
        $group=Group::where('group_id',$id)->first();
        $perm=Perm::where('perm_type',1)->where('is_show',1)->where('grade',2)->select('id','perm_name','perm')->get();

        if(isset($group->group_id)){
            return $this->response->array([
                "id"   => $group->group_id,
                "name" => $group->group_name,
                "group_perm" =>$group->group_perm,
                "perm" =>$perm,
                "status" =>1,
            ]);
        }else{
            return $this->response->array([
                "perm" => $perm,
            ]);
        }
    }

}
