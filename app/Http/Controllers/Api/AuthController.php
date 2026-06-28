<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\Api\UserResourse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $register_data = $request->validated();
        try {
            DB::beginTransaction();
            $register_data['password'] = bcrypt($register_data['password']);
            $register_data['email_verified_at'] = now();

            User::create($register_data);
            DB::commit();

            return $this->setCode(200)->setSuccess('User created successfully')->send();
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->setError('Failed to create user')->setCode(500)->send();
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->setError('Unauthorized')->setCode(401)->send();
            }
        } catch (JWTException $e) {
            return $this->setError('Could not create token')->setCode(500)->send();
        }

        return $this->setCode(200)->setSuccess('Login successful')->setData(new UserResourse(auth()->user(), $token))->send();
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->setCode(200)->setSuccess('Successfully logged out')->send();
    }

    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        return $this->setCode(200)->setSuccess('Token refreshed')->setData(compact('token'))->send();
    }
}
