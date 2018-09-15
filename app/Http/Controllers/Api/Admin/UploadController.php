<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
class UploadController extends Controller
{
    use Helpers;
    //
    public function index(){

        $images='';
        if(request()->hasFile('img')) {
            $files = request()->file("img");
            $images =imgupload($files);
        }
        return $this->response->array(asset($images));
    }
}
