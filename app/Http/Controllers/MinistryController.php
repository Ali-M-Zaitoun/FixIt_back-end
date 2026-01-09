<?php

namespace App\Http\Controllers;

use App\Http\Requests\MinistryRequest;
use App\Http\Resources\GovernorateResource;
use App\Http\Resources\MinistryResource;
use App\Models\Employee;
use App\Models\Governorate;
use App\Models\Ministry;
use App\Services\MinistryService;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;


class MinistryController extends Controller
{
    use ResponseTrait;

    public function __construct(protected MinistryService $service) {}

    public function store(MinistryRequest $request)
    {
        $ministry = $this->service->callWithLogging('store', $request->validated());

        return $this->successResponse(
            new MinistryResource($ministry),
            __('messages.ministry_stored'),
            201
        );
    }

    public function read()
    {
        $data = $this->service->read();

        return $this->successCollection(
            $data,
            MinistryResource::class,
            'messages.ministries_retrieved'
        );
    }

    public function readAll()
    {
        $active = MinistryResource::collection($this->service->read());
        $trashed = MinistryResource::collection($this->service->readTrashed());

        $data = [
            'active' => $active,
            'trashed' => $trashed
        ];

        $isEmpty = $active->resource->isEmpty() && $trashed->resource->isEmpty();

        return $this->successResponse(
            $data,
            $isEmpty ? __('messages.empty') : __('messages.ministries_retrieved')
        );
    }

    public function readOne(Ministry $ministry)
    {
        return $this->successResponse(new MinistryResource($ministry), __('messages.ministry_retrieved'), 200);
    }

    public function assignManager(Ministry $ministry, Employee $employee)
    {
        $updated = $this->service->callWithLogging('assignManager', $ministry, $employee);

        return $this->successResponse(new MinistryResource($updated), __('messages.ministry_manager_assigned_success'), 200);
    }

    public function removeManager(Ministry $ministry)
    {
        $updated = $this->service->callWithLogging('removeManager', $ministry);

        return $this->successResponse(new MinistryResource($updated), __('messages.ministry_manager_removed_success'), 200);
    }

    public function update(Ministry $ministry, Request $request)
    {
        $updated = $this->service->callWithLogging('update', $ministry, $request->all());

        return $this->successResponse($updated, __('messages.success'));
    }

    public function delete(Ministry $ministry)
    {
        $this->service->callWithLogging('delete', $ministry);
        return $this->successResponse([], __('messages.deleted_successfully'));
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
