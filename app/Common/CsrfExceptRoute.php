<?php

return [

	//不应用csrf的路由集合
	"except" => [
		'admin::editors',
		'weixin::indexs',
		'streammediacallbacks',
	],
	
	//后台登录路由，组里的路由不受网站关闭的影响
	"login" => [
		'logins',
		'loginposts',
		'logouts',
	],
	
	//后台基础框架路由，组里的路由登录后无需授权可访问
	"base" => [
		'admin::indexs',
		'admin::rights',
		'admin::editors',
		'admin::user_follows',
        'admin::check_contents'
	],
	
	//后台最高管理权限用户id，默认管理员表里第一个用户
	"founderid" => 1,
];