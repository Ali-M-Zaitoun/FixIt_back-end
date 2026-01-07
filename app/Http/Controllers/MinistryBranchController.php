<?php

namespace App\Http\Controllers;

use App\Http\Requests\MinistryBranchRequest;
use App\Http\Resources\MinistryBranchResource;
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
        $ministryBranch = $this->service->store($request->validated());

        if ($ministryBranch) {
            return $this->successResponse($ministryBranch, __('messages.ministry_branch_created'), 201);
        }
        return $this->errorResponse(__('messages.failed'), 500);
    }

    public function read()
    {
        $data = $this->service->read();

        if (sizeof($data) < 1) {
            return $this->successResponse([], __('messages.empty'));
        }

        return $this->successResponse($data, __('messages.ministries_branches_retrieved'));
    }

    public function readOne($id)
    {
        $data = $this->service->readOne($id);
        if (!$data) {
            return $this->errorResponse(__('messages.ministry_not_found'), 404);
        }
        return $this->successResponse($data, __('messages.ministry_branches_retrieved'));
    }

    public function update(MinistryBranch $branch, Request $request)
    {
        $this->service->update($branch, $request->all());
        return $this->successResponse([], __('messages.success'));
    }

    public function delete(MinistryBranch $branch)
    {
        $this->service->delete($branch);
        return $this->successResponse([], __('messages.deleted_successfully'));
    }
    public function assignManager(MinistryBranch $branch, Employee $employee)
    {
        $this->service->assignManager($branch, $employee);
        return $this->successResponse(new MinistryBranchResource($branch), __('messages.branch_manager_assigned_success'), 200);
    }

    public function removeManager(MinistryBranch $branch)
    {
        $this->service->removeManager($branch);
        return $this->successResponse(new MinistryBranchResource($branch), __('messages.branch_manager_removed_success'), 200);
    }
}
