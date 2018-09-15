<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Users;
use App\Http\Models\ActionLog;
use Dingo\Api\Routing\Helpers;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogController extends Controller
{
    //
    use Helpers;

    public function index(){

        $contion=['name'=>request()->input('name')];
        $actionlist=ActionLog::search($contion)->paginate(10);
        return $this->response->array($actionlist);
    }
}
