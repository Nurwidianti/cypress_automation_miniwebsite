<?php
namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helper\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $request){
        try {
            $request->validate([
                'nik' => 'required',
                'password' => 'required',
            ]);

            $credentials = request(['nik','password']);
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed',500);
            }

            $user = User::where('nik', $request->nik)->first();
            if(!Hash::check($request->password, $user->password, [])){
                throw new Exception("Invalid Credentials", 1);
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');

        } catch (Exception $error) {
            return ResponseFormatter::error([
                'massage' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function logout(Request $request){
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function fetch(Request $request){
        return ResponseFormatter::success($request->user(),'Data Profil berhasil diambil');
    }
}
