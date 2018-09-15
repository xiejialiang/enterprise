<?php

/**
 * 常用公共方法
 */

use Illuminate\Support\Facades\Storage;
use App\Http\Models\Users;
use App\Http\Models\Config;
use App\Http\Models\ActionLog;
use Intervention\Image\ImageManagerStatic as Image;

    /**
     * @param string $ip 要检测的ip
     * @param string $ips 黑名单ip
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
     function ipTest($ip='',$ips=''){
        if(!$ip){$ip = get_onlineip();}
		if(empty($ip))return ;
        if(!$ips){
            $config = SiteInitParam::where('type','set40')->select('value')->first();
            $ips = isset($config->value)?$config->value:'';
            $ips = str_replace("\r\n", ",", $ips);
        }
        $ipArr = explode(',',$ips);
        foreach($ipArr as $iprule){
            $ipruleregexp=str_replace('.*','ph',$iprule);
            $ipruleregexp=preg_quote($ipruleregexp,'/');
            $ipruleregexp=str_replace('ph','\.[0-9]{1,3}',$ipruleregexp);
            if(preg_match('/^'.$ipruleregexp.'$/',$ip))
            {
                $result= 1;
            }
            else {
                $result=0;
            }
            if($result){
                die('您的IP已被禁止访问本站');
            }
        }
    }

	function realname($name){
		return config("app.name") . $name;
	}


    /**
     * 如果需要允许跨域请求，请在记录处理跨域options请求问题，并且返回200，以便后续请求，这里需要返回几个头部。。
     * @param code 状态码
     * @param int $code
     * @param array $headers
     * @param array $options
     */
    function return_error($message,$code=400,$headers=[],$options = [])
    {
        http_response_code($code);    //设置返回头部
        $responseData['status_code']=$code;
        $responseData['message']=(string)$message;
        if(!empty($options))
            $responseData=array_merge($responseData, $options);
        // 发送头部信息
        foreach ($headers as $name => $val) {
            if (is_null($val)) {
                header($name);
            } else {
                header($name . ':' . $val);
            }
        }
        exit(json_encode($responseData,JSON_UNESCAPED_UNICODE));
    }
	/**
	 * 字符串截取,可截取汉字
	 *
	 * @param string $_String
	 * @param int $_Length
	 * @param int $_Start
	 * @param string $dot
	 * @param string $_Encode
	 * @return string
	 */
	function cutstr($_String, $_Length, $_Start=0,$dot='...', $_Encode='utf-8'){
		if(trim($_String) == ''){
			return '';
		}
		$_P['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|"
		."\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
		$_P['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$_P['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$_P['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";

		$_Le = $_Length*2-1;
		$_v  = 0;
		$_R = '';

		preg_match_all($_P[$_Encode], $_String, $_A);
		$_L = count($_A[0]);
		if($_L<$_Length) { return $_String; }

		for($i=$_Start; $i<$_L; $i++)
		{
			if($_v>=$_Le) return $_R.$dot;
			$_v += ord($_A[0][$i])>129 ? 2 : 1;
			$_R .= $_A[0][$i];
		}
		return $_R;
	}
	/*
	* 功能：连续建目录
	* $dir 目录字符串
	*/
	function makedir( $dir, $mode = 0777 ) {
		if( ! $dir ) return 0;
		$dir = str_replace( "\\", "/", $dir );
		$mdir = "";
		foreach( explode( "/", $dir ) as $val ) {
			$mdir .= $val."/";
			if( $val == ".." || $val == "." ) continue;

			if( ! file_exists( $mdir ) ) {
				if(!@mkdir( $mdir, $mode )){
					//    echo "创建目录 [".$mdir."]失败.";
					exit;
				}
			}
		}
		return true;
	}
	/**
	 * 获取客户端ip
	 *
	 * @return string
	 */
	function get_onlineip() {
		$onlineip = '';
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$onlineip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$onlineip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		return $onlineip;
	}
	/**
	 * 根据ip返回省份
	 * @return string
	 */
	function get_ip_place(){
		$ip2 = sinatogetip();
		if($ip2['code'] == 0){
			$province = '陕西';
			$user_ip =get_onlineip();//用户ip
		}else{
			$province = isset($ip2['data']['region'])?str_replace(["省","市"],"",$ip2['data']['region']):'陕西';
			$user_ip = isset($ip2['data']['ip'])? $ip2['data']['ip'] : get_onlineip();//用户ip
		}
		return $province;
	}	
	function sinatogetip(){


		//$ip=file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=php&ip=".self::get_onlineip(),false,stream_context_create($opts));
		//$ip=file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=".self::get_onlineip(),false,stream_context_create($opts));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://ip.taobao.com/service/getIpInfo.php?ip=".get_onlineip());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$ip = curl_exec($ch);
		curl_close($ch);
		
		$ip2=json_decode($ip,true);
		//$ip = mb_convert_encoding($ip,'UTF-8','GBK');// GBK转UTF-8  或 $ip = iconv("GBK","UTF-8",$ip);// GBK转UTF-8
		
		//$ip2=explode("	",$ip);
		return $ip2;
	}
	/**
	 * 操作日志记录
	 * @param string $log_info 日志内容
	 * @param $log_type=0 操作日志 1后台登录日志
	 * @return int log_id 主键
	 */
	function setAdminLog($log_info,$log_type=0,$uid=0){

		$log = new ActionLog;
		$log->uid = $uid>0?$uid:1;
		$log->log_info = checkcontent($log_info,'(含图片)',2);
		$log->log_type = $log_type;
		$log->add_time = date("Y-m-d H-i-s");
		$log->ip_address = request()->getClientIp();
		$log->save();
		return $log->log_id;
	}
	/**
	 * 删除一个二维数组中包含的一维数组，条件：此一维数组中的键名$keyname等于$val
	 * @param array $array 所要操作的二维数组
	 * @param string $keyname 一维数组的键名
	 * @param string $val 一维数组的键值
	 * example:
	 $arr = array(
		array(
			"a"=>"gggg",
			"b"=>"eeee",
		),
		array(
			"a"=>"ddddd",
			"b"=>"fffff",
		)
		);
		$result = Heler::delArrayExistsValue($arr,'a','ddddd');
		$result:结果数组array(
			array(
				"a"=>"gggg",
				"b"=>"eeee",
			)
		);
	 */
	 function delArrayExistsValue($array,$keyname,$val){
		$find_arr = array_keys(array_filter($array,create_function('$v','if(in_array("'.$keyname.'",array_keys($v)) && $v["'.$keyname.'"] == "'.$val.'") return $v;')));
		if(!empty($find_arr)){
			foreach($find_arr as $v){
				unset($array[$v]);
			}
		}
		return $array;
	}
	/**
	 * 优酷视频信息获取
	 * @param string $link 优酷视频链接地址
	 */
	 function getyoukuvideo($link){
		$data = new VideoUrlParser();
		return $data->check($link);
	}
	/**
	 * 替换/删除链接参数 
	 * @param string $url 链接地址
	 * @param string $name 参数名称
	 * @param string $addname 新增的参数名称
	 * @param string $addval 新增的参数名称对应的参数值
	 */
	function showlink($url,$name,$addname=null,$addval=null){
		$str = $url;
		$str = $str.'&';
		preg_match_all("/".$name."\=([^\&]*)\&/",$str, $arr);
		if(!empty($arr)){
			foreach($arr[0] as $v){
				$str = str_replace($v,"",$str);
			}
			if(!empty($addname)){
				$u_arr = explode("?",$str);
				$str = rtrim($str,'&');
				$str = rtrim($str,'?');
				$u_arr = explode("?",$str);
				if(count($u_arr)>1 && $u_arr[1] != ''){
					if($str{(strlen($str)-1)} == '&' || $str{(strlen($str)-1)} == '?'){
						$str .= $addname.'='.$addval;
					}else{
						$str .= '&'.$addname.'='.$addval;
					}
				}else{
					$str .= '?'.$addname.'='.$addval;
				}
			}
			$str = rtrim($str,'&');
			$str = rtrim($str,'?');
		}
		return $str;
	}
	
	/**
	 * 判断是否是移动设备
	 */
	function checkmobile() {
		$mobile = [];
		static $mobilebrowser_list = ['iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
					'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
					'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
					'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
					'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
					'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
					'benq', 'haier', '^lct', '320x320', '240x320', '176x220'];
		static $wmlbrowser_list = ['cect', 'compal', 'ctl', 'lg', 'nec', 'tcl', 'alcatel', 'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom',
				'pantech', 'dopod', 'philips', 'haier', 'konka', 'kejian', 'lenovo', 'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh',
				'sed', 'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte'];

		$pad_list = ['pad', 'gt-p1000'];

		$useragent = isset($_SERVER['HTTP_USER_AGENT'])?strtolower($_SERVER['HTTP_USER_AGENT']):'';

		if(dstrpos($useragent, $pad_list) || dstrpos($useragent, $mobilebrowser_list) || dstrpos($useragent, $wmlbrowser_list)) {
			return true;
		}
		
		$brower = ['mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop'];
		if(dstrpos($useragent, $brower)) return false;

	}
	/**
	 * 判断是否是移动设备辅助函数
	 */
	function dstrpos($string, $arr, $returnvalue = false) {
		if(empty($string)) return false;
		foreach((array)$arr as $v) {
			if(strpos($string, $v) !== false) {
				$return = $returnvalue ? $v : true;
				return $return;
			}
		}
		return false;
	}
	/**
	 * 获取网站配置参数值
	 * @return value
	 */
    function siteConfig($prem=null,$default=null,$prex=null){
		if(empty($prem))return ;
		$data = Config::where("type","=",$prem)->select('value')->first();
		return (isset($data->value) && ($data->value === '0' || !empty($data->value))) ? $prex.$data->value : ($default?$prex.$default:'');
	}
	/**
	 * 获取后台用户的角色名称
	 * @return string
	 */
	function rolename($uid){
		$group_data = AdminMember::where("uid","=",$uid)->first();
		return isset($group_data->group->group_name)?$group_data->group->group_name:'';
	}
	//获取当前归属的导航目录
    function getnavname($routename="",$field="id") {
		if(!empty($routename)){
			$route_data = AdminPerm::where("perm",$routename)->first();
			return isset($route_data->perm_name) ? $route_data->perm_name : '';
		}else{
			$current_route = request()->route()->getName();
			$route_data = AdminPerm::where("perm",$current_route)->first();
		}
		return isset($route_data->id) ? '<i class="Hui-iconfont">&#xe67f;</i> 首页 '.getauthinfo($route_data->id,$field) : '';
	}
	/**
	 * 图片上传
	 * @param obj $file = request()->file("file") 上传的文件
	 * @param int $width 要求缩放的宽度
	 * @param int $height 要求缩放的高度
	 * @param array $allow_ext 允许的图片格式
	 * @param int $max 允许上传的图片大小，单位:M
	 * @return path
	 */
	function imgupload($file,$width=null,$height=null,$allow_ext=["jpg","jpeg","gif","png"],$max=2,$dir=null) {
		$path = "";
		if($file->isValid()){
			if(empty($allow_ext)){
				$allow_ext=["jpg","jpeg","gif","png"];
			}
			$finfo   = finfo_open(FILEINFO_MIME);//开启支持finfo_open的php扩展精确判断文件类型
			$mimearr = explode(";",finfo_file($finfo, $file->getPathname()));
			$mime_this_type = $mimearr[0];
			$real_extname = returnimghz($mime_this_type,$file->getClientOriginalExtension());
			//$real_extname = $file->extension();
			if(!in_array($real_extname,$allow_ext)){
				die("格式错误!文件格式必须为".implode(",",$allow_ext)."格式");
			}
			if($file->getClientSize() > uploadMaxFilesize(2)){
				die("文件大小超出系统环境限制的".uploadMaxFilesize()."!");
			}
			if($file->getClientSize() > 1024*1024*$max){
				die("大小超出限制!文件大小必须在".$max."M以内");
			}
			if(empty($dir)){
				$dir = 'upload/'.date("Y/m/d");
			}
			$name = str_random(32).'.'.$real_extname;

            if($file->move($dir,$name)){
                $path = $dir.'/'.$name;
            }
            if(empty($path)){
                die('文件上传失败');
            }
           // $path = $file->store($dir);//laravel自带检测图片minitype生成图片后缀不准确

            if(!empty($height) || !empty($width)){
                //图片大小调整
                $path = imgresize($path,$width,$height);
            }

            //配合文件系统迁移的路径处理
            //$path = filesystempath($path);
		}else{
			die($file->getErrorMessage());
		}
		return $path;
	}
	//图片等比例缩放
	 function imgresize($path,$width=0,$height=0){
		$img = Image::make($path);
		if($width <= 0){
			$width = $img->width();
		}
		if($height <= 0){
			$height = $img->height();
		}
		$img->resize($width, $height, function ($constraint) {
			$constraint->aspectRatio();
			$constraint->upsize();
		});

        if($img->save()){
            $path =$img->dirname.'/'.$img->basename;
        }
        return $path;
	}
	//文件系统路径处理
	 function filesystempath($path){
		$path = ltrim(Storage::url($path),"/");
		if(strpos(config('filesystems.disks.'.config('filesystems.default').'.root'), storage_path()) === false){
			$path = str_replace("storage/","",$path);
		}
		return $path;
	}
	//通过图片mime值返回图片后缀  image/jpeg  返回  jpg
	 function returnimghz($mimetype=null,$default=null){
		if(!empty($mimetype)){
			$hz = [];
			include("mimes.php");
			foreach($mimes as $k=>$v){
				if(is_array($v)){
					if(in_array($mimetype,$v)){
						$hz[] = $k;
					}
				}else{
					if($v == $mimetype){
						$hz[] = $k;
					}
				}
			}
			if(!empty($default)){
				if(in_array($default,$hz)){
					return $default;
				}
				return '';
			}
			return isset($hz[0])?$hz[0]:'';
		}
	}
	

	

	/**
	 * 获取上传文件大小限制的环境变量值
	 * @return string
	 */
	function uploadMaxFilesize($t=1,$max_size='',$dec=2){
		$max_size = !empty($max_size) ? $max_size : ini_get('upload_max_filesize');
		preg_match('/(^[0-9\.]+)(\w+)/',$max_size,$info);
		$size = $info[1];
		$suffix = strtoupper($info[2]);
		$a = array_flip(["B", "KB", "MB", "GB", "TB", "PB"]);
		$b = array_flip(["B", "K", "M", "G", "T", "P"]);
		$pos = isset($a[$suffix])&&$a[$suffix]!==0?$a[$suffix]:$b[$suffix];
		$val = round($size*pow(1024,$pos),$dec);
		return $t==1?sizecount($val):$val;
	}
	/**
	 * 将数字转化为带kb,mb,gb的容量单位值
	 * @return string
	 */
	function sizecount($filesize,$t=''){
		if(!empty($t)){
			switch($t){
				case "G":
				$filesize = round($filesize/1073741824*100)/100;
				break;
				case "M":
				$filesize = round($filesize/1048576*100)/100;
				break;
				case "K":
				$filesize = round($filesize/1024*100)/100;
				break;
				case "B":
				$filesize = $filesize;
				break;
			}
			return $filesize;
		}
		
		
		if($filesize >= 1073741824){
			$filesize = round($filesize/1073741824*100)/100 . 'GB';
		}else if($filesize>=1048576){
			$filesize = round($filesize/1048576*100)/100 . 'MB';
		}else if($filesize>=1024){
			$filesize = round($filesize/1024*100)/100 . 'KB';
		}else{
			$filesize = $filesize.'B';
		}
		return $filesize;
	}
	/**
	 * 返回文件类型
	 * @return string
	 */
	function filetypename($t=0){
		$name = '其它';
		switch($t){
			case 1:$name = '文档';break;
			case 2:$name = '视频';break;
			case 3:$name = '音频';break;
			case 4:$name = '图片';break;
			case 5:$name = 'PPT';break;
			case 6:$name = 'Flash';break;
			case 7:$name = '字幕';break;
			case 8:$name = '其它';break;
		}
		return $name;
	}
	
    /**
     * 显示时间规则
     *
     * @param $time 输入时间与当前时间比较
     * @return string
     */
	function  showtime($time){
		$between=time()-$time;
        $int_time = strtotime(date("Y-m-d"));           //  2017/4/14 0:0:0
        $oneday_after = strtotime("-1 day",$int_time);  //  2017/4/13 0:0:0
        $int_one = time() - $oneday_after;              //  当前时间与2017/4/13 0:0:0相差秒数
        $twoday_after = strtotime("-2 day",$int_time);  //  2017/4/12 0:0:0
        $int_two = time() - $twoday_after;              //当前时间与2017/4/12 0:0:0相差秒数
		if($between ==0){
            $show_time = '刚刚';
        }else if($between<60){
			$show_time =$between.'秒前';
		}else if($between >=60 && $between <3600){
			$show_time =intval($between/60).'分钟前';
		}else if($between >=3600 && $between <86400){
            $show_time =intval($between/3600).'小时前';
        }else  if($between >=86400 && $between <=$int_one){
            $show_time ='昨天';
        }else if($between >$int_one && $between <=$int_two){
            $show_time ='前天';
        }else{
            $show_time = date("Y-m-d",$time);
        }
        return $show_time;
	}
	
	/**
	 * 检查内容是否含有图片并返回去除HTML标签的内容
	 * @return string
	 */
	 function checkcontent($str=null,$img=null,$wz=1){
		if(!empty($str)){
			$showimg = '';
			if(preg_match("/<img.*>/",$str)){
				$showimg = $img ? $img : '<i class="glyphicon glyphicon-picture"></i>';
			}
			return $wz==1?($showimg.strip_tags($str)):(strip_tags($str).$showimg);
		}
	}



    
	
	
	
	
	//office转pdf辅助函数
	function MakePropertyValue($name,$value,$osm){
		$oStruct = $osm->Bridge_GetStruct("com.sun.star.beans.PropertyValue");
		$oStruct->Name = $name;
		$oStruct->Value = $value;
		return $oStruct;
	}
	//office转pdf
	function word2pdf($doc_url, $output_url){
		$osm = new COM("com.sun.star.ServiceManager") or die ("Please be sure that OpenOffice.org is installed.n");
		$args = array(MakePropertyValue("Hidden",true,$osm));
		$oDesktop = $osm->createInstance("com.sun.star.frame.Desktop");
		$oWriterDoc = $oDesktop->loadComponentFromURL($doc_url,"_blank", 0, $args);
		$export_args = array(MakePropertyValue("FilterName","writer_pdf_Export",$osm));
		$oWriterDoc->storeToURL($output_url,$export_args);
		$oWriterDoc->close(true);
	}

	//路径转换(应用于本地存储)
	function rewritepath($path=null,$type=1){
		$result = '';
		if(!empty($path)){
			$path = str_replace('upload/','',$path);
			$arr = explode('/',$path);
			$result = '/file/cache/'.(isset($arr[0])?$arr[0]:'').'-'.(isset($arr[1])?$arr[1]:'').'-'.(isset($arr[2])?$arr[2]:'').'/'.(isset($arr[3])?$arr[3]:'');
		}
		
		return $type==1?$result:assets($result);
	}

    
    /**
     * 视频时间显示
     *
     * @param $id
     * @return string
     */
	function showvideolength($len){
		$len = abs(intval($len));
		$shi = '';
		$fen = '00:';
		$miao = '00';
		if(intval($len/3600) > 0){
			$shi = intval($len/3600);
			$shi = ($shi<10 ? '0'.$shi :$shi).':';
			$len -= $shi * 3600;
		}
		if(intval($len/60) > 0){
			$fen = intval($len/60);
			$fen = ($fen<10 ? '0'.$fen :$fen).':';
			$len -= $fen * 60;
		}
		if(intval($len%60) > 0){
			$miao = intval($len%60);
			$miao = $miao<10 ? '0'.$miao :$miao;
		}
		return $shi.$fen.$miao;
    }

    
    
     
    
	//秒数转化为时分秒
	function secondtosfm($time=0){
		$result = '';
		if($time >= 3600){
			$result = intval($time/3600);
			if($result < 10){
				$result = '0'.$result;
			}
			$result .= ':'.gmstrftime('%M:%S',$time%3600);
		}else{
			$result = gmstrftime('%H:%M:%S',$time);
		}
		return $result;
	}
	//curl发送请求
	function curl($url='',$post_data=[],$header=""){
        //$headers = array("Content-Type: text/xml; charset=utf-8");
        //$headers = array("Content-Type: application/x-www-form-urlencoded");//需要将数组转化为url的参数类似&a1&b=2等方式才生效
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);//支持毫秒级超时
		//curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);//超时时长200毫秒
		//curl_setopt($ch, CURLOPT_TIMEOUT,1);//超时时长1秒
		if(!empty($header)){
			$headers = array("Content-Type: ".$header);
			if($header == 'application/x-www-form-urlencoded'){
				if(!empty($post_data)){
					$str = '';
					foreach($post_data as $k=>$v){
						$str .= '&'.$k.'='.$v;
					}
					$post_data = ltrim($str,'&');
				}
				curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
			}else{
				curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}else{
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
		}
		//curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Expect:"));
		$return = curl_exec ( $ch );
		curl_close ( $ch );
        return $return;
	}
	
	

    


    

    /**
     * 将视频时间转为int
     * @param $str string 00:00:00
     * @return int
     */
    function video_time($str){
        $arr = explode(":",$str);
        if(isset($arr[0]) && isset($arr[1]) &&  isset($arr[2])){
            return $arr[0]*3600+$arr[1]*60+$arr[2];
        }
        die('参数错误');
    }

    /**
     * 将int转为视频时间
     *
     * @param $time int 时间
     * @return string
     */
    function time_video($time){
        $h = intval($time/3600);
        $m = intval(($time%3600)/60);
        $s =  $time - 3600*$h - $m*60;
        return sprintf('%02s', $h).':'.sprintf('%02s', $m).':'.sprintf('%02s', $s);
    }

    

    /**
     * 取出分辨率中最小值
     *
     * @param $resolutions array 保存分辨率的数组
     * @return array|bool   数据错误返false 成功则返回 array
     */
    function minResolution($resolutions){

        array_multisort($resolutions,SORT_ASC,SORT_NUMERIC);
        if(isset($resolutions[0])){
            //分辨率设置
            $resolution_arr = explode("x",$resolutions[0]);
            if(count($resolution_arr) ==2 ){
                //分辨率参数
                $left = trim($resolution_arr[0]);
                $right = trim($resolution_arr[1]);
                return compact('left','right');
            }
        }
        return false;
    }
    /**
     * 传入上传的数据，返回文件合成的Session里的key(用来判断该文件是否存在)
     *
     * @param $data array 传入上传的数据
     * @return string
     */
    function mergeSessionKey($data){
        $key = '';
        foreach ($data as $item){
            $start = video_time($item['start']);
            $end = video_time($item['end']);
            $key != '' && $key.='|';
            $key.=$item['id'].'-'.$start.'-'.$end;
        }
        return $key;
    }

    
   
    /**
     * @param array $files_arr 合并音频文件数组
     * @param string $out_name 输出的文件名
     * @return string
     */
    function mergeMusicFile($files_arr = [],$out_name=''){
        if(strtoupper(substr(PHP_OS,0,3))==='WIN'){//windows服务器
            $ffmpeg = app_path('Http/Common/ffmpeg.exe');
        }else{//linux服务器
            $ffmpeg = 'sudo /usr/bin/ffmpeg';
        }
        $cmd = $ffmpeg.' -i "concat:'.implode('|',$files_arr).'" -acodec copy '.$out_name;
        exec($cmd);
        return $out_name;
    }

   




    /**
     * 替换临时文件文件名，返回需要保存的文件名
     * @param $temp_name string 临时文件名字
     * @param $replace string 替换的字符串
     * @return string
     */
    function replaceTempName($temp_name,$replace=''){
        if($replace == ''){
            $dir = 'upload/'.date("Y/m/d");
            $replace = $dir.'/'.str_random(32);
        }
        $dir_prefix = dirname($replace);

        file_server_fu('makedir',['dir'=>$dir_prefix,'mode'=>0777]);


        $str_random = strstr($temp_name,"_",true);
        return  str_replace($str_random.'_',$replace,$temp_name);

    }

    
  


	

    /**
     * 发送短信接口
     *
     * @param $phone    手机号码
     * @param $code     验证码
     * @param int $send_type    应用场景 1登录，2注册，3修改用户名/(基本信息)，4修改密码，5实名认证,	
     * @return array
     */
	function sendmsg($phone,$code,$send_type = 0){
		//登录(SMS_125595027)  注册(SMS_125595025)  修改用户名(SMS_125595023)  修改密码(SMS_125595024)  实名认证(SMS_126464754)
		$result = array('status'=>0,'message'=>'发送失败');		
		$provider = siteConfig('set95','aLiYun');
		$set_96 = siteConfig('set96');
		$set_97 = siteConfig('set97');
		$set_99 = siteConfig('set99');
		
		//设置模板
		switch($send_type){
			case 1:
				$set_98 = 'SMS_125595027';
				break;
			case 2:
				$set_98 = 'SMS_125595025';
				break;
			case 3:
				$set_98 = 'SMS_125595023';
				break;
			case 4:
				$set_98 = 'SMS_125595024';
				break;
			case 5:
				$set_98 = 'SMS_126464754';
				break;		
			default:
				$set_98 = siteConfig('set98');
				break;
		}
		config(['sms.default'=> $provider]);
		config(['sms.signName'=>siteConfig('set100','E学堂')]);
		switch($provider){
			case 'aLiYun':
				config(['sms.agents.aLiYun'=>[
					'credentials' => [
						'appKey' => $set_96,
						'appSecret' => $set_97,
					],
					'templateId' => $set_98,
					'executableFile' => 'ALiYunAgent',
				],]);
				break;
			case 'yunPian':
				config(['sms.agents.yunPian'=>[
					'apiKey' => $set_96,
					'templateContent' => $set_97,
					'executableFile' => 'YunPianAgent',
				],]);
				break;
			case 'yunTongXun':
				config(['sms.agents.yunTongXun'=> [
					'credentials' => [
						'accountSid' => $set_96,
						'accountToken' => $set_97,
						'appId' => $set_98,
					],
					'templateId' => $set_99,
					'executableFile' => 'YunTongXunAgent',
				],]);
				break;
			case 'subMail':
				config(['sms.agents.subMail'=> [
					'credentials' => [
						'appid' => $set_96,
						'apiKey' => $set_97,
					],
					'templateId' => $set_98,
					'executableFile' => 'SubMailAgent',
				],]);
				break;
			case 'luoSiMao':
				config(['sms.agents.luoSiMao'=> [
					'apiKey' => $set_96,
					'templateContent' => $set_97,
					'executableFile' => 'LuoSiMaoAgent',
				],]);
				break;
			case 'qqYun':
				config(['sms.agents.qqYun'=> [
					'credentials' => [
						'appId' => $set_96,
						'appKey' => $set_97
					],
					'templateId' => $set_98,
					'executableFile' => 'QQYunAgent',
				],]);
				break;
			default:
				$tag = 0;
				$result['message'] = '参数错误';
				break;
		}
		if(!isset($tag)){
			$smsDriver = Sms::driver($provider);
			$templateVar = ['code' => $code];
			$smsDriver->setTemplateVar($templateVar, true);
			$data = $smsDriver->singlesSend($phone);
			if(isset($data['code'])){
				if($data['code'] === 0){
					$result = array('status'=>1,'message'=>$data['msg']);
				}else{
					$data['msg'] =='isv.MOBILE_COUNT_OVER_LIMIT' && $result['message'] = '您的操作太频繁，请稍后再试！';
				}
			}
		}
		
		return $result;
	}

    /**
     * 调用短信接口之前验证
     * @param int $send_type    发送场景  1登录，2注册，3修改用户名/(基本信息)，4修改密码，5实名认证,	
     * @param $phone    手机号码
     * @return array
     */
	function send_sms($send_type=1,$phone){
		$result  = ['status'=>0,'message'=>'发送失败，请稍后再试！'];	
		if(siteConfig('set101',0) != 1){
			$result['message'] = '短信平台未开启，请联系管理人员！';
		}else{
		    $today = date('Y-m-d');
			//判断60S后可在此发送短信
			
			if(sessionHas('phone_code_'.$send_type) && sessionGet('phone_code_'.$send_type.'.phone') == $phone && time() <= sessionGet('phone_code_'.$send_type.'.new_time')){
				
				$result  = ['status'=>0,'message'=>'您的操作太频繁，请稍后再试！'];
			}else{
				//发送短信	
				$code = str_pad(rand(1,999999),6,0,STR_PAD_LEFT);
				$sms_result = sendmsg($phone,$code,$send_type);
				/*if(1){
					$code = "123456";
					$sms_result = ['status'=>1 ,'message'=> '发送成功'];
				}else{
					$code = str_pad(rand(1,999999),6,0,STR_PAD_LEFT);
					$sms_result = self::sendmsg($phone,$code,$send_type);
				}*/
				
				if($sms_result['status'] == 1){
					//缓存60S,缓存验证码 10分钟过期
					sessionPut('phone_code_'.$send_type,['phone'=>$phone,'code'=> $code,'new_time'=> time()+60,'end_time'=> time()+10*60]);
					ShortMessageRecord::create([
						'id' => str_random(20),
						'phone' => $phone,
						'code' => $code,
						'validity_time' => sessionGet('phone_code_'.$send_type.'.end_time'),
						'send_date' => $today,
						'type' => $send_type,
						'send_time' => time(),
					]);
					$result  = ['status'=>1,'message'=>'短信发送成功'];	
				}else{
					$result  = ['status'=>0,'message'=>$sms_result['message']];
				}
			}
		}
		return $result;
	}


	
	
     function secToTime($times){
        $result = '00:00:00';
        if ($times>0) {
            $hour = floor($times/3600);
            $minute = floor(($times-3600 * $hour)/60);
            $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);
            if($hour < 100) {
                $hour = "0".$hour;
            }
            if($minute < 10) {
                $minute = "0".$minute;
            }
            if($second < 10) {
                $second = "0".$second;
            }
            $result = $hour.':'.$minute.':'.$second;
        }
        return $result;
    }

	




