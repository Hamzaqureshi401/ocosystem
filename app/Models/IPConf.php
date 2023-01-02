<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class IPConf extends Model
{
    protected $table = 'ipconf';
    protected $guarded = [];
    use SoftDeletes;
}
