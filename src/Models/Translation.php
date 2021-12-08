<?php

namespace Bit\Translatable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Translation extends Model
{
    use SoftDeletes;
}