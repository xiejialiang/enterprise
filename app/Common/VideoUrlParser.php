<?php
namespace App\Http\Common;
class VideoUrlParser
{
	public function check($url){
		$result = ['status'=>0,'message'=>'暂不支持该地址解析'];
		if(strpos($url,'.youku.com') !== false){
			$result = $this->youkuvideo($url);
		}else if(strpos($url,'.qq.com') !== false){
			$result = $this->qqvideo($url);
		}else if(strpos($url,'.163.com') !== false){
			$result = $this->opencourse163($url);
		}
		return $result;
	}
	public function opencourse163($url)
	{
        $response = $this->fetchUrl($url);

        if ($response['code'] != 200) {
			return ['status'=>0,'message'=>'获取网易公开课视频信息失败'];
        }

        $matched = preg_match('/getCurrentMovie.*?id\s*:\s*\'(.*?)\'.*?image\s*:\s*\'(.*?)\'\s*\+\s*\'(.*?)\'\s*\+\s*\'(.*?)\'.*?title\s*:\s*\'(.*?)\'.*?appsrc\s*:\s*\'(.*?)\'.*?src\s*:\s*\'(.*?)\'/s', $response['content'], $matches);
        if (!$matched) {
			return ['status'=>0,'message'=>'解析网易公开课视频信息失败'];
        }

        $item['source'] = '163';
        $item['uuid'] = $matches[1];
        $item['name'] = iconv('gbk', 'utf-8', $matches[5]);
		$item['page'] = $url;
        $item['type'] = 4;
		$item['files'] = $matches[7];
		$item['filelist'] = array('swf' => $matches[7],'mp4' => str_replace('.m3u8', '.mp4', $matches[6]),'m3u8' => $matches[6]);

		return ['status'=>1,'message'=>'获取成功','rs'=>$item];
	}
	
    public function qqvideo($url)
    {
        $matched  = preg_match('/vid=(\w+)/s', $url, $matches);
        $response = array();

        if (!empty($matched)) {
            $vid = $matches[1];
        } else {
            $response = $this->fetchUrl($url);

            if ($response['code'] != 200) {
				return ['status'=>0,'message'=>'获取QQ视频页面信息失败'];
            }

            $matched = preg_match('/VIDEO_INFO.*?[\"]?vid[\"]?\s*:\s*"(\w+?)"/s', $response['content'], $matches);

            if (empty($matched)) {
				return ['status'=>0,'message'=>'解析QQ视频ID失败'];
            }

            $vid = $matches[1];
        }

        $matched = $this->getUrlMatched($url);

        if ($matched) {
            $responseInfo = $response ? $response : array();
            $videoUrl     = 'http://sns.video.qq.com/tvideo/fcgi-bin/video?otype=json&vid='.$vid;

            $response = $this->fetchUrl($videoUrl);

            if ($response['code'] != 200) {
				return ['status'=>0,'message'=>'获取QQ视频信息失败'];
            }

            $matched = preg_match('/{.*}/s', $response['content'], $matches);

            if (empty($matched)) {
				return ['status'=>0,'message'=>'解析QQ视频信息失败'];
            }

            $video = json_decode($matches[0], true) ?: array();

            if (!empty($video) && !empty($video['video'])) {
                $video = $video['video'];
                $title = $video['title'];
            } else {
                $video = array();
                $title = $url;

                if ($responseInfo) {
                    $title = $this->getVideoTitle($responseInfo);
                }
            }

            $summary  = $video ? $video['desc'] : '';
            $duration = $video ? $video['duration'] : '';
            $pageUrl  = $video ? 'http://v.qq.com/cover/'.substr($video['cover'], 0, 1)."/{$video['cover']}.html?vid={$vid}" : $url;

            $item = $this->getItem($vid, $title, $summary, $duration, $pageUrl);
        } else {
            $title = $this->getVideoTitle($response);

            if (empty($title)) {
				return ['status'=>0,'message'=>'解析QQ视频ID失败'];
            }
        }

		return ['status'=>1,'message'=>'获取成功','rs'=>$this->getItem($vid, $title, '', '', $url)];
    }

    protected function getItem($vid, $title, $summary, $duration, $pageUrl)
    {
        $item = array(
            'source'   => 'qqvideo',
            'name'     => $title,
            'uuid'     => $vid,
            'page'     => $pageUrl,
            'type'     => 3,
            'files'    => "https://imgcache.qq.com/tencentvideo_v1/playerv3/TPout.swf?max_age=86400&v=20161117&vid={$vid}&auto=0"//"http://static.video.qq.com/TPout.swf?vid={$vid}&auto=1"
        );

        return $item;
    }

    protected function getUrlMatched($url)
    {
		$patterns = array(
			'p1'  => '/^http\:\/\/v\.qq\.com\/cover\//s',
			'p2'  => '/^http\:\/\/v\.qq\.com\/boke\/page\//s',
			'p3'  => '/^http\:\/\/v\.qq\.com\/page\//s',
			'p4'  => '/^http\:\/\/v\.qq\.com\/x\/page\//s',
			'p5'  => '/^http\:\/\/v\.qq\.com\/x\/cover\//s',
			'p6'  => '/^https\:\/\/v\.qq\.com\/cover\//s',
			'p7'  => '/^https\:\/\/v\.qq\.com\/boke\/page\//s',
			'p8'  => '/^https\:\/\/v\.qq\.com\/page\//s',
			'p9'  => '/^https\:\/\/v\.qq\.com\/x\/page\//s',
			'p10' => '/^https\:\/\/v\.qq\.com\/x\/cover\//s'
		);
        foreach ($patterns as $key => $pattern) {
            $matched = preg_match($pattern, $url);

            if ($matched) {
                return $matched;
            }
        }

        return false;
    }

    protected function getVideoTitle($responseInfo)
    {
        $matched = preg_match('/VIDEO_INFO.*?[\"]?title[\"]?\s*:\s*"(.*?)"/s', $responseInfo['content'], $matches);

        if (empty($matched)) {
            return '';
        }

        return $matches[1];
    }
	
	public function youkuvideo($url)
	{
		$patterns = array(
			'p1' => '/^http\:\/\/v\.youku\.com\/v_show\/id_(.+?).html/s',
			'p2' => '/http:\/\/player\.youku\.com\/player\.php.*?\/sid\/(.+?)\/v.swf/s',
		);

        $matched = preg_match($patterns['p2'], $url, $matches);
        if ($matched) {
            $url = "http://v.youku.com/v_show/id_{$matches[1]}.html";
        }

        $matched = preg_match('/\/id_(.+?).html/s', $url, $matches);
        if (empty($matched)) {
            return ['status'=>0,'message'=>'优酷视频地址不正确'];
        }

        $videoId = $matches[1];

        $response = $this->fetchUrl($url);
        if ($response['code'] != 200) {
            return ['status'=>0,'message'=>'获取优酷视频页面信息失败'];
        }

        $item = array();
        $item['source'] = 'youku';
        $matched = preg_match('/id="s_baidu1"\s+href="(.*?)"/s', $response['content'], $matches);
        if (empty($matched)) {
            return ['status'=>0,'message'=>'解析优酷视频页面信息失败'];
        }
        $queryString = substr($matches[1], strpos($matches[1], '?') + 1);
        $queryString = substr($queryString, 0, strpos($queryString, '#') ? : strlen($queryString));
        parse_str($queryString, $query);

        if (empty($query) || empty($query['title'])) {
            return ['status'=>0,'message'=>'解析优酷视频页面信息失败'];
        }

        $item['name'] = $query['title'];
        $item['uuid'] = $videoId;
        $item['page'] = "http://v.youku.com/v_show/id_{$videoId}.html";
        $item['type'] = 2;
        $item['files'] = "//player.youku.com/player.php/sid/{$videoId}/v.swf";

		return ['status'=>1,'message'=>'获取成功','rs'=>$item];
	}
	
    protected function fetchUrl ($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_ENCODING, "gzip");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        // curl_setopt($curl, CURLOPT_USERAGENT, $this->options['user_agent']);
        
        $content = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
		if(empty($content)){
			$content = file_get_contents($url);
			if(!empty($content)){
				$code = 200;
			}
		}
        return array('code' => $code , 'content' => $content);
    }
}
