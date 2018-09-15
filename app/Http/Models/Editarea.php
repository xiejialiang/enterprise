<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Editarea extends Model
{
    //
    protected $table = 'editarea';

    protected $primaryKey  = 'id';//设置主键

    public $timestamps  = false;//禁用时间自动更新

    protected $fillable = ['title','imgpath','type','seq','status','carete_id','create_time','update_time'];



}
