<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    /**
     * Create user
     *
     * @param Request $request
     * @return JsonResponse [string] message
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|string',
            'sex' => 'required|string|in:male,female',
            'blood_type' => 'required|string|in:o+,o-,a+,a-,b+,b-,ab+,ab-',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Provide proper details']);
        }
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'sex' => $request->sex,
            'blood_type' => $request->blood_type,
            'active' => 0,
            'type' => 'user',
            'password' => bcrypt($request->password),
        ]);

        if ($user->save()) {
            return response()->json([
                'message' => 'Successfully created user!'
            ], 201);
        } else {
            return response()->json(['error' => 'Provide proper details']);
        }
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string']);
        if ($validator->fails()) {
            return response()->json(['error' => 'Provide proper details']);
        }

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = $request->user();
        if ($user->active == 0) {
            return response()->json([
                'message' => 'Unauthorized, User is not approved yet.'
            ], 401);
        }
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;
        $user->last_login = Carbon::now();
        $user->save();

        return response()->json([
            'accessToken' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return JsonResponse [json] user object
     */
    public function user(Request $request)
    {
        $request->user()->load('certificates');
        return response()->json($request->user());
    }

    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return JsonResponse [json] user object
     */
    public function approve(Request $request)
    {
        if ($request->user()->type != 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Provide proper details']);
        }

        $user = User::query()->where('id', $request->user_id)->first();
        $user->active = true;
        $user->save();

        return response()->json([
            'message' => 'User Approved'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return JsonResponse [json] user object
     */
    public function getUnApprovedUsers(Request $request)
    {
        if ($request->user()->type != 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $users = User::query()->where('active', 0)->get();

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return JsonResponse [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return JsonResponse [json] user object
     */
    public function profile(Request $request)
    {
        if ($request->user()->type != 'user') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email,' . $request->user()->id,
            'password' => 'required|string',
            'sex' => 'required|string|in:male,female',
            'blood_type' => 'required|string|in:o+,o-,a+,a-,b+,b-,ab+,ab-',
            'certificates' => 'array',
            'certificates.*' => 'exists:certificates,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Provide proper details']);
        }

        $user = $request->user();
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'sex' => $request->sex,
            'blood_type' => $request->blood_type,
            'password' => bcrypt($request->password),
        ]);
        if (is_null($request->certificates)) {
            $certificates = [];
        } else {
            $certificates = $request->certificates;
        }
        $user->certificates()->sync($certificates);
        $user->save();

        $user->load('certificates');

        return response()->json([
            'message' => 'User Updated',
            'user' => $user
        ]);
    }

}
