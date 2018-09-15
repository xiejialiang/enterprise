<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Models\Users;
use App\Http\Models\Config;
use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    use Helpers;
    public function login(Request $request) {

        $rules=[
            'name' => 'required|regex:/^[A-Za-z0-9_-\x{4e00}-\x{9fa5}]+$/u',
            'password' => 'required|min:6|max:20'
        ];
        $messages = [
            'name.required' => '用户名不能为空',
            'name.regex' => '用户名格式不正确',
            'password.required' => '密码不能为空',
            'password.min' => '密码在6-20个字符之间',
            'password.max' => '密码在6-20个字符之间',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }

        }
        try {
            $user = Users::where('name', $request->name)->first();

            if(siteConfig('set2') ==  1){
                $msg_array=['message'=>'该站点已关闭','status'=>1];
                return $this->response->array($msg_array);
            }
            if ($user && Hash::check($request->get('password'), $user->password)) {
                if(in_array($user->roleid,[1,2]) && $user->status ==1){
                    $token = JWTAuth::fromUser($user);
                    $user->add_time =date("Y-m-d H-i-s");
                    $user->last_login =date("Y-m-d H-i-s");
                    $user->last_ip =get_onlineip();
                    $user->save();
                    return $this->sendLoginResponse($request, $token);
                }else{
                    $msg_array=['message'=>'访问被禁止......','status'=>0];
                    return $this->response->array($msg_array);
                }

            }else{
                $msg_array=['message'=>'账号或密码错误','status'=>0];
                return $this->response->array($msg_array);
            }

        } catch (ValidatorException $e) {
            $error = '';
            foreach ($e->getMessageBag()->messages() as $v) {
                $error = $v[0];
                break;
            }
            $msg_array=['message'=>$error,'status'=>0];
            return $this->response->array($msg_array);
        }
    }

    public function sendLoginResponse(Request $request, $token) {
        $this->clearLoginAttempts($request);

        return $this->authenticated($token);
    }

    public function authenticated($token) {
        return $this->response->array([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'status_code' => 200,
            'message' => '登陆成功',
        ]);
    }

    public function sendFailedLoginResponse() {
        throw new UnauthorizedHttpException("Bad Credentials");
    }

    public function logout() {
        $this->guard()->logout();
    }
}
