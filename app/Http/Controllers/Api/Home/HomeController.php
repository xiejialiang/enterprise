<?php

namespace App\Http\Controllers\Api\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Http\Models\Editarea;
use Tymon\JWTAuth\Facades\JWTAuth;


class HomeController extends Controller
{
    use Helpers;
    //
    public  function index(){

        $editarea=Editarea::where('type',1)->where('status',1)->orderBy('seq','desc')->take(3)->get();

    }
}
