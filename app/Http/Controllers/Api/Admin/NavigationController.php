<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Http\Models\Perm;
use Tymon\JWTAuth\Facades\JWTAuth;



class NavigationController extends Controller
{
    use Helpers;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $condition = array(
            'name'=>trim(request()->input('name','')),
        );
        $coursenotesList =Perm::search($condition)->where('grade',1)->paginate(10);
        return $this->response->array($coursenotesList);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules=[
            'perm_name' => 'required|unique:perm,perm_name',
        ];
        $messages = [
            'perm_name.required' => '导航目录不能为空',
            'perm_name.unique' => '导航目录已存在',
        ];
        $date['perm_name'] =request()->input('perm_name');
        $validator = Validator::make($date, $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=["message"=>$v[0],"status"=>0];
                return $this->response->array($msg_array);
            }
        }
        $perm = new Perm;
        $perm->perm_name =$date['perm_name'];
        $perm->grade =1;
        if ($perm->save()) {
            setAdminLog("权限管理/添加/导航".$perm->perm_name);
            return $this->response->array([
                "message" => "添加成功",
                "status" =>1,
            ]);
        } else {
            $msg_array=['message'=>"添加失败",'status'=>0];
            return $this->response->array($msg_array);
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $rules=[
            'perm_name' => 'required|unique:perm,perm_name',
        ];
        $messages = [
            'perm_name.required' => '导航目录不能为空',
            'perm_name.unique' => '导航目录已存在',
        ];
        $date['perm_name'] =request()->input('perm_name');
        $validator = Validator::make($date, $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=["message"=>$v[0],"status"=>0];
                return $this->response->array($msg_array);
            }
        }
        $perm =Perm::where('id',$id)->first();
        if(isset($perm->id)){
            $perm->perm_name =$date['perm_name'];
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $perm =Perm::where('id',$id)->select('id','is_show')->first();

        if(isset($perm->id)){
            $perm->is_show =0;
            if($perm->save()){
                return $this->response->array([
                    "message" => "删除导航成功",
                    "status" =>1,
                ]);
            }else{
                return $this->response->array([
                    "message" => "删除导航失败",
                    "status" =>0,
                ]);
            }
        }else{
            return $this->response->array([
                "message" => "删除导航失败",
                "status" =>0,
            ]);
        }


    }
}
