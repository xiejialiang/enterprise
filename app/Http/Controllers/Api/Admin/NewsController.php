<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Models\Category;
use App\Http\Models\Enterprises;
use DemeterChain\C;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Http\Models\News;
use Tymon\JWTAuth\Facades\JWTAuth;

class NewsController extends Controller
{

    use Helpers;
   //资讯列表
    public function index(){

        $condition = [
            'name'=>trim(request()->input('name','')),
            'status'=>request()->input('status'),
        ];

        $newList = News::search($condition)->paginate(10);
        return $this->response->array($newList);

    }

    public function add_news(){
        $rules = [
            'title' => 'required',
            'content' => 'required',
        ];
        $messages = [
            'title.required' => '标题不能为空',
            'content.required' => '内容不能为空',
        ];


        $validator = Validator::make(request()->all(), $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }
        }
        $News = new News();

        if (request()->hasFile('img_path')) {
            $file = request()->file("img_path");

            $News->img_path = imgupload($file,920,518,[],3);
        }else{
            return $this->response->array([
                "message" => "封面图不能为空",
                "status" =>0,
            ]);
        }
        $News->title = trim(request()->input('title'));
        $News->content = trim(request()->input('content'));
        $News->cateid = abs(intval(request()->input('cateid')));
        $News->carete_time = date('Y-m-d H-i-s');
        $News->update_time =  date('Y-m-d H-i-s');;
        $News->status = 1;
        $News->uid =JWTAuth::parseToken()->authenticate()->id;;

        if($News->save()){
            setAdminLog("资讯管理/添加/资讯". $News->title);
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

    public function edit_news($id,Request $request){
        $rules = [
            'title' => 'required',
            'content' => 'required',
        ];
        $messages = [
            'title.required' => '标题不能为空',
            'content.required' => '内容不能为空',
        ];

        $validator = Validator::make(request()->all(), $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $v){
                $msg_array=['message'=>$v[0],'status'=>0];
                return $this->response->array($msg_array);
            }
        }
        $News =News::where('id',$id)->first();
        if (request()->hasFile('img_path')) {
            $file = request()->file("img_path");
            $News->img_path = imgupload($file,500,350,[],3);
        }

        $News->title = trim(request()->input('title'));
        $News->content = trim(request()->input('content'));
        $News->cateid = abs(intval(request()->input('cateid')));
        $News->enterid = abs(intval(request()->input('enterid')));
        $News->update_time =  date('Y-m-d H-i-s');;

        if($News->save()){
            setAdminLog("资讯管理/修改/资讯". $News->title);
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

    public function news_info(){

        $id =request()->input('id');
        $news =News::where('id',$id)->first();
        $cate =Category::where('type',1)->where('status',1)->select('id','cate_name')->get()->toArray();


        if(isset($news->id)){
            return $this->response->array([
                "cate" => $cate,
                "status" =>1,
                "id"=>$id,
                "cateid"=>$news->cateid,
                "title"=>$news->title,
                "content"=>$news->content,
                "img_path"=>asset($news->img_path)
            ]);
        }else{
            return $this->response->array([
                "cate" => $cate,
                "status" =>1,
            ]);
        }


    }


    public function news_release($id){
        $status =request()->input('status');
        $news=News::where('id',$id)->first();
        if(isset($news->id)){
            $news->status =$status;
           if($status == 1){
               $p_name="发布";
           }else{
               $p_name="取消发布";
           }
            if($news->save()){
                return $this->response->array([
                    "message" => "资讯".$p_name."成功",
                    "status" =>0,
                ]);
            }else{
                return $this->response->array([
                    "message" => "资讯".$p_name."失败",
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

    public function news_istop($id){
        $istop =request()->input('istop');
        $news=News::where('id',$id)->first();
        if(isset($news->id)){
            $news->istop =$istop;
            if($istop == 1){
                $p_name="推荐";
            }else{
                $p_name="取消推荐";
            }
            if($news->save()){
                return $this->response->array([
                    "message" => "资讯".$p_name."成功",
                    "status" =>0,
                ]);
            }else{
                return $this->response->array([
                    "message" => "资讯".$p_name."失败",
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
    public function del_news($id){

        $news=News::where('id',$id)->first();
        if(isset($news->id)){
            if($news->delete()){
                return $this->response->array([
                    "message" => "资讯删除成功",
                    "status" =>0,
                ]);
            }else{
                return $this->response->array([
                    "message" => "资讯删除失败",
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



}
