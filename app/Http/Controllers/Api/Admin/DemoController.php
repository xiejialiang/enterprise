<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
class DemoController extends Controller
{
    use Helpers;
    //
    public function index(){
        $date=[ '服务器版本'=> php_uname('s').php_uname('r'),
                '服务器域名'=>$_SERVER['SERVER_NAME'] ,
                '服务器域名(主机名)'=>$_SERVER["HTTP_HOST"],
                '服务器IP'=>$_SERVER['SERVER_ADDR'],
                '服务器脚本文档根目录'=> $_SERVER['DOCUMENT_ROOT'],
                '服务器操作系统'=>php_uname(),
                '服务器解译引擎'=>$_SERVER['SERVER_SOFTWARE'],
                '服务器Web端口'=>  $_SERVER['SERVER_PORT'],
                '服务器系统时间'=>date("Y-m-d G:i:s"),
                'PHP版本'=>PHP_VERSION,
                'MYSQL支持'=>function_exists ('mysql_close')?'是':'否','最大上传限制'=>get_cfg_var ('upload_max_filesize')?get_cfg_var ('upload_max_filesize'):'不允许上传',
                '脚本运行占用最大内存'=>get_cfg_var ("memory_limit")?get_cfg_var("memory_limit"):"无",
                '最大执行时间'=>get_cfg_var("max_execution_time")."秒 ",
                '通信协议的名称和版本'=>$_SERVER['SERVER_PROTOCOL']
        ];

        return $this->response->array($date);
    }

}
