<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [

        'tap_mode',
        'tap_secret_key',
        'tap_public_key',

    ];
}
