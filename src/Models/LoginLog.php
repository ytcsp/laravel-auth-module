<?php

namespace Strichpunkt\LaravelAuthModule\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $table = 'login_logs';

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'success',
    ];
}
