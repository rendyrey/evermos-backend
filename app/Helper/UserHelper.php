<?php

namespace App\Helper;
use Firebase\JWT\JWT;
use App\Models\User;

class UserHelper
{
    public static function getUser($token) {
        $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        return User::find($credentials->sub);
    }
}
