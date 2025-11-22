<?php

namespace App\Http\Controllers;

use App\Http\Requests\MinistryRequest;
use App\Http\Resources\GovernorateResource;
use App\Http\Resources\MinistryResource;
use App\Models\Governorate;
use App\Services\MinistryService;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MinistryController extends Controller
{
    use ResponseTrait;

    protected MinistryService $service;
    public function __construct()
    {
        $this->service = new MinistryService();
    }

    public function store(MinistryRequest $request)
    {
        $user = Auth::user();
        $ministry = $this->service->store($request->validated());

        if ($ministry)
            return $this->successResponse($ministry, __('messages.ministry_stored'), 201);

        return $this->errorResponse(__('messages.registration_failed'), 500);
    }

    public function read()
    {
        $data = $this->service->read();

        if (!$data || $data->isEmpty()) {
            return $this->successResponse([], __('messages.empty'));
        }

        return $this->successResponse(MinistryResource::collection($data), __('messages.ministries_retrieved'), 200);
    }

    public function readOne($id)
    {
        $ministry = $this->service->readOne($id);

        if (!$ministry)
            return $this->errorResponse(__('messages.ministry_not_found'), 404);

        return $this->successResponse(new MinistryResource($ministry), __('messages.ministry_retrieved'), 200);
    }

    public function assignManager($id, $manager_id)
    {
        $ministry = $this->service->assignManager($id, $manager_id);

        if ($ministry)
            return $this->successResponse(new MinistryResource($ministry), __('messages.ministry_manager_assigned_success'), 200);

        return $this->errorResponse(__('messages.ministry_manager_assignment_failed'));
    }

    public function getGovernorates()
    {
        $governorates = Cache::rememberForever('governorates', function () {
            return Governorate::all();
        });
        $governorates = GovernorateResource::collection($governorates);
        return $this->successResponse($governorates, __('messages.governorates_retrieved'), 200);
    }
}
