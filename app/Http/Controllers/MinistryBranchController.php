<?php

namespace App\Http\Controllers;

use App\Http\Requests\MinistryBranchRequest;
use App\Http\Resources\MinistryResource;
use App\Models\Ministry;
use App\Models\MinistryBranch;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class MinistryBranchController extends Controller
{
    use ResponseTrait;

    public function add(MinistryBranchRequest $request)
    {
        $ministryBranch = MinistryBranch::create($request->validated());
        if ($ministryBranch) {
            return $this->successResponse($ministryBranch, __('messages.ministry_branch_created'), 201);
        }
        return $this->errorResponse(__('messages.registration_failed'), 500);
    }

    public function getBranches($ministry_id)
    {
        $ministry = Ministry::where('id', $ministry_id)->get();

        if (sizeof($ministry) < 1) {
            return $this->errorResponse(__('messages.ministry_not_found'), 404);
        }

        $data = MinistryResource::collection($ministry);

        return $this->successResponse($data, __('messages.ministry_branches_fetched'), 200);
    }
}
