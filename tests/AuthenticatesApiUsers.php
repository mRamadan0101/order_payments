<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

trait AuthenticatesApiUsers
{
    protected function authenticate(User $user): string
    {
        $token = JWTAuth::fromUser($user);

        $this->withHeader('Authorization', 'Bearer '.$token);

        return $token;
    }
}
