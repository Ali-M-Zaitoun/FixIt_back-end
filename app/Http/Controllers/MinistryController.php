<?php

namespace App\Http\Controllers;

use App\Http\Requests\MinistryRequest;
use App\Http\Resources\GovernorateResource;
use App\Http\Resources\MinistryResource;
use App\Models\Employee;
use App\Models\Governorate;
use App\Models\Ministry;
use App\Services\EmployeeService;
use App\Services\MinistryService;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MinistryController extends Controller
{
    use ResponseTrait;

    public function __construct(protected MinistryService $service) {}

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

    public function readOne(Ministry $ministry)
    {
        return $this->successResponse(new MinistryResource($ministry), __('messages.ministry_retrieved'), 200);
    }

    public function assignManager(Ministry $ministry, Employee $employee)
    {
        $this->service->assignManager($ministry, $employee);

        return $this->successResponse(new MinistryResource($ministry), __('messages.ministry_manager_assigned_success'), 200);
    }

    public function removeManager(Ministry $ministry)
    {
        $this->service->removeManager($ministry);

        return $this->successResponse(new MinistryResource($ministry), __('messages.ministry_manager_removed_success'), 200);
    }

    public function update(Ministry $ministry, $data) {
        $ministry = $this->service->update()
    }

    public function delete(Ministry $ministry)
    {
        if ($this->service->delete($ministry)) {
            return $this->successResponse([], __('messages.deleted_successfully'));
        }
        return $this->errorResponse(__('messages.error'));
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
