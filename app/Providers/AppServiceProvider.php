<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator ;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        //判断是否为手机号码
        Validator::extend('mobile', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^134[0-8]\d{7}$|^13[^4]\d{8}$|^14[5-9]\d{8}$|^15[^4]\d{8}$|^16[6]\d{8}$|^17[0-8]\d{8}$|^18[\d]{9}$|^19[8,9]\d{8}$/", $value);
        });
        //判断是否为手机号码
        Validator::extend('mobile', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^134[0-8]\d{7}$|^13[^4]\d{8}$|^14[5-9]\d{8}$|^15[^4]\d{8}$|^16[6]\d{8}$|^17[0-8]\d{8}$|^18[\d]{9}$|^19[8,9]\d{8}$/", $value);
        });
        // 判断是否某个区间之内
        Validator::extend('num_between', function ($attribute, $value, $parameters, $validator) {
            return ($value>$parameters[0]&&$value<$parameters[1]);
        });
        // 判断是否小于
        Validator::extend('num_lt', function ($attribute, $value, $parameters, $validator) {
            return ($value<$parameters[0]);
        });
        // 判断是否大于
        Validator::extend('num_gt', function ($attribute, $value, $parameters, $validator) {
            return ($value>$parameters[0]);
        });
        // 判断经度
        Validator::extend('longitude', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^[\-\+]?(0?\d{1,2}\.\d{1,8}|1[0-7]?\d{1}\.\d{1,8}|180\.0{1,8})$/", $value);
        });
        // 判断纬度
        Validator::extend('latitude', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^[\-\+]?([0-8]?\d{1}\.\d{1,8}|90\.0{1,8})$/", $value);
        });

        /**
         * 判断地区是否存在
         */
        Validator::extend('city', function ($attribute, $value, $parameters, $validator) {
            return $res=DB::table('regions')->where('id',$value)->first()?true:false;
        });
        // 判断营业时间
        Validator::extend('time_hour', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^\d{2}\:\d{2}$/", $value);
        });

        //只含有汉字、数字、字母、下划线，下划线位置不限
        Validator::extend('title_check', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u", $value);
        });

        //只含有汉字
        Validator::extend('chinese', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $value);
        });

        //逗号隔开的字符串
        Validator::extend('comma_str_check', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^[0-9\,]+$/u", $value);
        });

        //正浮点数
        Validator::extend('money_check', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^[0-9]+(.[0-9]{1,2})?$/", $value);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
