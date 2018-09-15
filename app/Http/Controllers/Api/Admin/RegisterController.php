<?php

namespace App\Http\Controllers\Api\Admin;


use App\Http\Controllers\Controller;
use App\Http\Models\Users;
use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    use RegistersUsers;
    use Helpers;

    public function register(Request $request) {

        $rules = [
            'name' => 'required|regex:/^[A-Za-z0-9_-\x{4e00}-\x{9fa5}]+$/u|unique:users,name',
            'password' => 'required|min:6|max:20',
            'email'=>'required|email|unique:users,email',
            'pwd_repeat' => 'same:password',
            'phone' =>'mobile|unique:users,phone'
        ];

        $messages = [
            'name.required' => '用户名不能为空',
            'name.regex' => '用户名格式不正确',
            'name.unique'=>'用户名已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码在6-20个字符之间',
            'password.max' => '密码在6-20个字符之间',
            'email.required'=>'邮箱不能为空',
            'email.email'=>'邮箱格式不正确',
            'email.unique'=>'邮箱已存在',
            'pwd_repeat.same'=>'两次密码不一致',
            'phone.mobile'=>'手机号码输入有误',
            'phone.unique'=>"手机号码已存在"
        ];

        $validator = Validator::make(request()->all(), $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }
        }
        $date =$request->all();
        $date['password']=bcrypt($date['password']);
        $date['add_time']=date("Y-m-d H-i-s");
        $date['update_time']=date("Y-m-d H-i-s");
        if(request()->hasFile('avatar')) {
            $file = request()->file("avatar");
            $date['avatar'] =imgupload($file);
        }
        $user =Users::create($date);

        if ($user->save()) {
            //$token = JWTAuth::fromUser($user);
            return $this->response->array([
                "message" => "添加成功",
                "status" =>1,
            ]);
        } else {
            $msg_array=['message'=>"添加失败",'status'=>0];
            return $this->response->array($msg_array);
        }
    }




}
