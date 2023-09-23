<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register New User
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->ErrorResponse($this->validationError, $validator->errors(), null);
        }

        try {
            // Create a new user
            $user           = new User();
            $user->email    = $request->email;
            $user->name     = $request->name;
            $user->password = bcrypt($request->password);
            $user->save();

            $jwt_token = $user->createToken('access-token')->plainTextToken;

            return $this->SuccessResponse(
                'User registered and logged in successfully!',
                [
                    'user'  => $user,
                    'token' => $jwt_token
                ]
            );
        } catch (\Exception $e) {
            return $this->ErrorResponse($this->jsonException, $e->getMessage(), null);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'      => 'required|email',
            'password'   => 'required'
        ]);

        if ($validator->fails()) {
            return $this->ErrorResponse($this->validationError, $validator->errors(), null);
        }

        try {
            // Verify the credentials
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->ErrorResponse('No user found with the email provided. Try again!', null, null);
            }

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                $validator->errors()->add('email', 'Your credetials do not match our records!');
                return $this->ErrorResponse($this->validationError, $validator->errors(), null);
            }

            $jwt_token = $user->createToken('access-token')->plainTextToken;

            return $this->SuccessResponse($this->dataRetrieved, [
                'user' => $user,
                'token' => $jwt_token
            ]);
        } catch (\Exception $e) {
            return $this->ErrorResponse($this->jsonException, $e->getMessage(), null);
        }
    }

    // Logout User
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->ErrorResponse($this->validationError, $validator->errors(), null);
        }

        //Request is validated, do logout        
        try {
            $user = Auth::user();
            $user->tokens()->delete();
            return $this->SuccessResponse('User has been logged out', null);
        } catch (\Exception $e) {
            return $this->ErrorResponse('Sorry, user cannot be logged out', null, null);
        }
    }
}
