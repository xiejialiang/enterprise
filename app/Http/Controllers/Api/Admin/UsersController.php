<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Users;
use Illuminate\Support\Facades\Validator;
use Dingo\Api\Routing\Helpers;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsersController extends Controller
{

    use Helpers;
    //用户列表
    public function index(){


        $condition = [
            'name'=>trim(request()->input('name',''))
        ];
        $userList = Users::search($condition)->paginate(10);

        return $this->response->array($userList);
    }

    public function add_user(){
        $rules = [
            'name' => 'required|regex:/^[A-Za-z0-9_-\x{4e00}-\x{9fa5}]+$/u|unique:users,name',
            'password' => 'required|min:6|max:20',
            'pwd_repeat' => 'same:password',
        ];

        $messages = [
            'name.required' => '用户名不能为空',
            'name.regex' => '用户名格式不正确',
            'name.unique'=>'用户名已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码在6-20个字符之间',
            'password.max' => '密码在6-20个字符之间',

        ];

        $validator = Validator::make(request()->all(), $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }
        }
        $user = new Users();
        $user->name =request()->input('name');
        $user->password =bcrypt(request()->input('password'));
        $user->createor_id =JWTAuth::parseToken()->authenticate()->id;
        $user->add_time =date('Y-m-d H-i-s');
        $user->update_time =date('Y-m-d H-i-s');
        $user->update_time =date('Y-m-d H-i-s');
        $user->status =1;
        if($user->save()){
            setAdminLog("用户管理/添加/用户".$user->group_name);
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

    public function  edit_user($id,Request $request){
        $rules = [
            'nickname' => 'required|unique:users,nickname,'.$id,
            'email'=>'email|unique:users,email,'.$id,
            'phone' =>'required|mobile|unique:users,phone,'.$id
        ];
        $messages = [
            'nickname.required' => '昵称不能为空',
            'nickname.unique' => '昵称已存在',
            'email.email'=>'邮箱格式不正确',
            'email.unique'=>'邮箱已存在',
            'phone.required'=>'手机号码不能为空',
            'phone.mobile'=>'手机号码输入有误',
            'phone.unique'=>'手机号码已存在',

        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }
        }

        $user =Users::where('id',$id)->first();

        if($user->id){
            $user->nickname =$request->input('nickname');
            $user->email =$request->input('email');
            $user->phone =$request->input('phone');
            $user->sex =$request->input('sex');
            $user->sign =$request->input('sign');
            $user->update_time =date("Y-m-d H-i-s");
            if(request()->hasFile('avatar')) {
                $file = request()->file("avatar");
                $user->avatar=imgupload($file);
            }
            if($user->save()){
                setAdminLog("用户管理/编辑/用户".$user->group_name);
                return $this->response->array([
                    "message" => "编辑成功",
                    "status" =>1,
                ]);
            }
        }else{
            return $this->response->array([
                "message" => "编辑失败",
                "status" =>0,
            ]);
        }
    }


    public function edit_password($id,Request $request){
        $rules = [
            'password' => 'required|min:6|max:20',
            'pwd_repeat' => 'same:password',
        ];
        $messages = [
            'password.required' => '密码不能为空',
            'password.min' => '密码在6-20个字符之间',
            'password.max' => '密码在6-20个字符之间',
            'pwd_repeat.same'=>'两次密码不一致',

        ];
        $validator = Validator::make(request()->all(), $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }
        }

        $user =Users::where('id',$id)->first();
        if(isset($user->id)){
            $user->password=bcrypt(request()->input('password'));
            $user->update_time =date("Y-m-d H-i-s");
            if($user->save()){
                setAdminLog("用户管理/修改/密码".$user->name);
                return $this->response->array([
                    "message" => "修改密码成功",
                    "status" =>1,
                ]);

            }
        }else{
            return $this->response->array([
                "message" => "修改密码失败",
                "status" =>0,
            ]);
        }

    }

    public function closure_user($id,Request $request){
        $status =$request->input('status');
        if($status == 1){
            $s_name ="封禁解除";
        }else{
            $s_name ="封禁";
        }
        $user =Users::where('id',$id)->first();
        if(isset($user->id)){
            $user->status =$status;
            if($user->save()){
                setAdminLog("用户管理/用户/".$s_name.$user->name);
                return $this->response->array([
                    "message" => "".$s_name."成功",
                    "status" =>1,
                ]);
            }
        }else{
            return $this->response->array([
                "message" => "".$s_name."失败",
                "status" =>0,
            ]);
        }
    }
    public function  setup_user($id,Request $request){
        $group_id =$request->input('group_id');
        $user =Users::where('id',$id)->first();
        if(isset($user->id)){
            $user->roleid =$group_id;
            $user->update_time =date("Y-m-d H-i-s");
            if($user->save()){
                return $this->response->array([
                    "message" => "设置角色成功",
                    "status" =>1,
                ]);
            }
        }else{
            return $this->response->array([
                "message" => "设置角色失败",
                "status" =>0,
            ]);
        }
    }


    public function user_info($id){
        $user =Users::where('id',$id)->first();
        if(isset($user->id)){
            return $this->response->array([
                "name" => $user->name,
                "nickname" => $user->nickname,
                "email"    => $user->email,
                "phone"    => $user->phone,
                "avatar"   => asset($user->avatar),
                "sex"      => $user->sex,
                "sign"     => $user->sign,
                "group_id"     => $user->roleid,
                "status" =>1,
            ]);

        }else{
            return $this->response->array([
                "message" => "数据错误",
                "status" =>0,
            ]);
        }
    }

    public  function  user_group(){
        $group =Group::all();
        return $this->response->array($group);
    }
}
