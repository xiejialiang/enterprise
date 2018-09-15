<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Models\Perm;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class PermsController extends Controller
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
            'name'=>trim(request()->input('name')),
        );

        $coursenotesList =Perm::search($condition)->paginate(10);
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
            'parent_id'=>'required',
            'perm_name'=>'required|unique:perm,perm_name',
            'perm'=>'required|unique:perm,perm',
            'perm_type'=>'required'
        ];
        $messages = [
            'parent_id.required'=>'子菜单不能为空',
            'perm_name.required'=>'名称不能为空',
            'perm_name.unique'=>'名称不能重复',
            'perm.required'=>'标识代码不为空',
            'perm.unique'=>'标识代码不能重复',
            'perm_type.required'=>'类型不为空'
        ];

        $validator = Validator::make(request()->all(), $rules, $messages);

        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }
        }

        $perm = new Perm();

        $perm->perm_name =$request->input('perm_name');
        $perm->perm =$request->input('perm');
        $perm->perm_type =$request->input('perm_type');
        $perm->parent_id =$request->input('parent_id');
        $perm->grade =2;
        if($perm->save()){
            setAdminLog("权限管理/添加/子导航".$perm->perm_name);
            return $this->response->array([
                "message" => "添加子菜单成功",
                "status" =>1,
            ]);
        }else{
            return $this->response->array([
                "message" => "添加子菜单失败",
                "status" =>0,
            ]);
        }

    }

    public function  permlist(){
        $group =JWTAuth::parseToken()->authenticate()->group->toArray();

        $permlist =Perm::where('parent_id',0)->where('is_show',1)->where('grade',1)->get();

        if(!empty($permlist)){
            foreach ($permlist as $k=>$v){
                $en_perm =Perm::where('parent_id',$v['id'])->where('is_show',1)->get()->toArray();
                $en_perms=[];
                foreach ($en_perm as $v1){
                    if(in_array($v1['perm'], explode(",",$group['group_perm']))){
                        $en_perms[]=$v1;
                    }
                }
                $v->zrperm =$en_perms;
            }
            $permlist=$permlist->toArray();
        }
        return  $this->response()->array($permlist);

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
        $perm=Perm::where('id',$id)->first();
        //
        if(isset($perm->id)){
            $is_show=request()->input('is_show');
            $perm->is_show=$is_show;
            if($is_show == 1){
                $msg_name ="显示";
            }else{
                $msg_name ="隐藏";
            }
            if($perm->save()){
                return $this->response->array([
                    "message" => "菜单".$msg_name."成功",
                    "status" =>1,
                ]);
            }else{
                return $this->response->array([
                    "message" => "菜单".$msg_name."失败",
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
            'parent_id'=>'required',
            'perm_name'=>'required|unique:perm,perm_name,'.$id,
            'perm'=>'required|unique:perm,perm,'.$id,
            'perm_type'=>'required'
        ];
        $messages = [
            'parent_id.required'=>'子菜单不能为空',
            'perm_name.required'=>'名称不能为空',
            'perm_name.unique'=>'名称不能重复',
            'perm.required'=>'标识代码不能为空',
            'perm.unique'=>'标识代码不能重复',
            'perm_type.required'=>'类型不为空'
        ];

        $validator = Validator::make(request()->all(), $rules, $messages);

        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }
        }
        /*$permorwhere =Perm::where('id','<>',$id)->where(function($query) use($request){
            $query->where('perm_name',$request->input('perm_name'))->orWhere('perm_name',$request->input('perm'));
        })->first();
        if(isset($permorwhere->id)){
            $msg_array=['message'=>'名称或标识代码不能重复','status'=>0];
            return $this->response->array($msg_array);
        }*/
        $perm =Perm::where('id',$id)->first();
        if(isset($perm->id)){
            $perm->perm_name =$request->input('perm_name');
            $perm->perm =$request->input('perm');
            $perm->perm_type =$request->input('perm_type');
            $perm->parent_id =$request->input('parent_id');
            if($perm->save()){

                setAdminLog("权限管理/修改/子导航".$perm->perm_name);
                return $this->response->array([
                    "message" => "修改成功",
                    "status" =>1,
                ]);
            }else{
                return $this->response->array([
                    "message" => "修改子菜单失败",
                    "status" =>0,
                ]);
            }
        }else{
            return $this->response->array([
                "message" => "修改子菜单失败",
                "status" =>0,
            ]);
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
                    "message" => "删除菜单成功",
                    "status" =>1,
                ]);
            }else{
                return $this->response->array([
                    "message" => "删除菜单失败",
                    "status" =>0,
                ]);
            }
        }else{
            return $this->response->array([
                "message" => "删除菜单失败",
                "status" =>0,
            ]);
        }

    }


    public function perm_info(){

        $p_name=Perm::where('is_show',1)->where('parent_id',0)->select('id','perm_name')->get();

        $p_type =['1'=>'限制权限','2'=>'公共权限'];
        $id =request()->input('id');
        $perm =Perm::where('is_show',1)->where('id',$id)->select('id','perm_name')->get();
        if(isset($perm->id)){
            return $this->response->array([
                "p_name" => $p_name,
                "p_type" =>$p_type,
                "id"=>$id,
                "perm_name"=>$perm->name,
                "perm_type"=>$perm->perm_type,
                "perm"=>$perm->perm,
                "parent_id"=>$perm->parent_id,
            ]);
        }else{
            return $this->response->array([
                "p_name" => $p_name,
                "p_type" =>$p_type,
            ]);
        }
    }
}
