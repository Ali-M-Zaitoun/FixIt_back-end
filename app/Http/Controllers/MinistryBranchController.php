<?php

namespace App\Http\Controllers;

use App\DAO\MinistryBranchDAO;
use App\Http\Requests\MinistryBranchRequest;
use App\Http\Resources\MinistryBranchResource;
use App\Http\Resources\MinistryResource;
use App\Models\Ministry;
use App\Models\MinistryBranch;
use App\Services\MinistryBranchService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class MinistryBranchController extends Controller
{
    use ResponseTrait;

    protected MinistryBranchService $service;

    public function __construct()
    {
        $this->service = new MinistryBranchService();
    }

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

    public function assignManager($id, $manager_id)
    {
        $branch = $this->service->assignManager($id, $manager_id);
        return $this->successResponse(MinistryBranchResource::collection($branch), __('messages.employee_promoted'), 200);
    }
}
