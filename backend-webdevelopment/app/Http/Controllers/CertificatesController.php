<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CertificatesController extends Controller
{

    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return JsonResponse [json] user object
     */
    public function add(Request $request)
    {
        if($request->user()->type != 'admin'){
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'type' => 'required|string|unique:certificates'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Provide proper details']);
        }

        $certificate = new Certificate([
            'name' => $request->name,
            'type' => $request->type
        ]);

        if ($certificate->save()) {
            return response()->json([
                'message' => 'Successfully created certificate!'
            ], 201);
        } else {
            return response()->json(['error' => 'Provide proper details']);
        }
    }

    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return JsonResponse [json] user object
     */
    public function remove(Request $request)
    {
        if($request->user()->type != 'admin'){
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:certificates,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Provide proper details']);
        }

        $certificate = Certificate::query()->where('id', $request->id)->delete();

        return response()->json([
            'message' => 'Successfully deleted certificate!'
        ], 201);
    }

    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return JsonResponse [json] user object
     */
    public function get(Request $request)
    {
        $certificate = Certificate::query()->get();
        return response()->json([
            'certificates' => $certificate
        ], 200);
    }

}
