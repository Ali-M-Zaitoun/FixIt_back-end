<?php

namespace App\Http\Controllers;

use App\Http\Requests\MinistryRequest;
use App\Http\Resources\MinistryResource;
use App\Models\Ministry;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MinistryController extends Controller
{
    use ResponseTrait;

    public function add(MinistryRequest $request)
    {
        $user = Auth::user();
        $ministry = Ministry::create($request->validated());
        if ($ministry)
            return $this->successResponse($ministry, __('messages.ministry_created'), 201);

        return $this->errorResponse(__('messages.registration_failed'), 500);
        return $this->errorResponse(__('messages.unauthorized'), 401);
    }

    public function getMinistries()
    {
        $ministries = Ministry::all();
        $data = MinistryResource::collection($ministries);
        return $this->successResponse($data, __('messages.ministries_fetched'), 200);
    }
}
