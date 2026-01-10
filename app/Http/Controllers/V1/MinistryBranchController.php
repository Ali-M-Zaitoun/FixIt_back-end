<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\MinistryBranchRequest;
use App\Http\Resources\V1\MinistryBranchResource;
use App\Models\Employee;
use App\Models\MinistryBranch;
use App\Services\MinistryBranchService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;


class MinistryBranchController extends Controller
{
    use ResponseTrait;

    public function __construct(
        protected MinistryBranchService $service
    ) {}

    public function store(MinistryBranchRequest $request)
    {
        $ministryBranch = $this->service->callWithLogging('store', $request->validated());

        return $this->successResponse(
            new MinistryBranchResource($ministryBranch),
            __('messages.ministry_branch_created'),
            201
        );
    }

    public function read()
    {
        $data = $this->service->read();

        return $this->successCollection(
            $data,
            MinistryBranchResource::class,
            'messages.ministries_branches_retrieved'
        );
    }

    public function readOne(MinistryBranch $branch)
    {
        return $this->successResponse(
            new MinistryBranchResource($branch),
            __('messages.ministry_branches_retrieved')
        );
    }

    public function readAll()
    {
        $active = MinistryBranchResource::collection($this->service->read());
        $trashed = MinistryBranchResource::collection($this->service->readTrashed());

        $data = [
            'active' => $active,
            'trashed' => $trashed
        ];

        $isEmpty = $active->resource->isEmpty() && $trashed->resource->isEmpty();
        return $this->successResponse(
            $data,
            $isEmpty ? __('messages.empty') : __('messages.ministry_branches_retrieved')
        );
    }

    public function update(MinistryBranch $branch, Request $request)
    {
        $updated = $this->service->callWithLogging('update', $branch, $request->all());
        return $this->successResponse(
            new MinistryBranchResource($updated),
            __('messages.success')
        );
    }

    public function delete(MinistryBranch $branch)
    {
        $this->service->callWithLogging('delete', $branch);
        return $this->successResponse([], __('messages.deleted_successfully'));
    }
    public function assignManager(MinistryBranch $branch, Employee $employee)
    {
        $updated = $this->service->callWithLogging('assignManager', $branch, $employee);
        return $this->successResponse(new MinistryBranchResource($updated), __('messages.branch_manager_assigned_success'), 200);
    }

    public function removeManager(MinistryBranch $branch)
    {
        $updated = $this->service->callWithLogging('removeManager', $branch);
        return $this->successResponse(new MinistryBranchResource($updated), __('messages.branch_manager_removed_success'), 200);
    }
}
