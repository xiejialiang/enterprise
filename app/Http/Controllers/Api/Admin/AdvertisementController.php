<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Http\Models\Advertisement;

class AdvertisementController extends Controller
{
    use Helpers;
    //
    public function index(){
        $condition = [
            'name'=>trim(request()->input('name','')),
        ];
        $advertList = Advertisement::search($condition)->paginate(10);
        if(!empty($advertList)){
            foreach ($advertList as $v){
                $v->prices =$v->price/100;
                $v->cover=asset($v->ad_cover);
            }
        }
        return $this->response->array($advertList);
    }

    public function  add_advert(){
        if(request()->input('ad_url')){
            $rules = [
                'ad_url' => 'url',
            ];
            $messages = [
                'ad_url.url' => 'url格式错误',
            ];
            $date['ad_url']=request()->input('ad_url');
            $validator = Validator::make($date, $rules, $messages);
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $v){
                    $msg_array=["message"=>$v[0],"status"=>0];
                    return $this->response->array($msg_array);
                }
            }
        }

        $advert = new Advertisement();
        if(request()->hasFile('ad_cover')) {
            $files = request()->file("ad_cover");
            $advert->ad_cover =imgupload($files);
        }else{
            $msg_array=["message"=>"图片不能为空","status"=>0];
            return $this->response->array($msg_array);
        }
        $advert->title =request()->input('title');
        $advert->ad_url=request()->input('ad_url');
        $advert->price=abs(intval(request()->input('price')))*100;
        $advert->add_time=date('Y-m-d H-i-s');
        $advert->update_time =date('Y-m-d H-i-s');
        if($advert->save()){
            setAdminLog("广告管理/添加".$advert->id);
            return $this->response->array([
                "message" => "广告添加成功",
                "status" =>1,
            ]);
        }else{
            return $this->response->array([
                "message" => "广告添加失败",
                "status" =>0,
            ]);
        }
    }

    public function edit_advert($id, Request $request)
    {
        if(request()->input('ad_url')){
            $rules = [
                'ad_url' => 'url',
            ];
            $messages = [
                'ad_url.url' => 'url格式错误',
            ];
            $date['ad_url']=request()->input('ad_url');
            $validator = Validator::make($date, $rules, $messages);
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $v){
                    $msg_array=["message"=>$v[0],"status"=>0];
                    return $this->response->array($msg_array);
                }
            }
        }

        $advert = Advertisement::where('id', $id)->first();
        if (isset($advert->id)) {
            if (request()->hasFile('ad_cover')) {
                $files = request()->file("ad_cover");
                $advert->ad_cover = imgupload($files);
            }
            $advert->title = request()->input('title');
            $advert->ad_url = request()->input('ad_url');
            $advert->price = abs(intval($request->input('price'))) * 100;
            $advert->update_time = date('Y-m-d H-i-s');
            if ($advert->save()) {
                setAdminLog("广告管理/编辑" . $advert->id);
                return $this->response->array([
                    "message" => "广告编辑成功",
                    "status" => 1,
                ]);
            } else {
                return $this->response->array([
                    "message" => "广告编辑失败",
                    "status" => 0,
                ]);
            }
        } else {
            return $this->response->array([
                "message" => "数据错误",
                "status" => 0,
            ]);
        }
    }
    public function advert_info(){
        $id =abs(intval(request()->input('id')));
        $advert=Advertisement::where('id',$id)->first();
        if(isset($advert->id)){
            return $this->response->array([
                "title"=>$advert->title,
                "ad_cover"=>asset($advert->ad_cover),
                "price"=>$advert->price,
                "ad_url"=>$advert->ad_url,
                "status" => 1,
            ]);
        }else{
            return $this->response->array([
                "message" => "数据错误",
                "status" => 0,
            ]);
        }
    }
    public function del_advert($id){
        $advert=Advertisement::where('id',$id)->first();
        if(isset($advert->id)){
            if($advert->delete()){
                setAdminLog("广告管理/删除" . $advert->id);
                return $this->response->array([
                    "message" => "广告删除成功",
                    "status" => 1,
                ]);
            }else{
                return $this->response->array([
                    "message" => "广告删除失败",
                    "status" => 0,
                ]);
            }
        }else{
            return $this->response->array([
                "message" => "数据错误",
                "status" => 0,
            ]);
        }

    }



}
