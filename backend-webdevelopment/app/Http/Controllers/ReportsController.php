<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return JsonResponse [json] user object
     */
    public function getUsers(Request $request)
    {
        if ($request->user()->type != 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $users = User::query()->where('type', '!=', 'admin')->get();
        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return JsonResponse [json] user object
     */
    public function getCertificates(Request $request)
    {
        if ($request->user()->type != 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'certificates' => Certificate::query()->withCount('users')->get()
        ]);
    }
}
