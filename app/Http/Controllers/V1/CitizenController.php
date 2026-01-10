<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CitizenResource;
use App\Models\Citizen;
use App\Services\CitizenService;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;

class CitizenController extends Controller
{
    use ResponseTrait;

    public function __construct(protected CitizenService $citizenService) {}

    public function read()
    {
        $citizens = $this->citizenService->read();

        return $this->successResponse(
            CitizenResource::collection($citizens),
            $citizens->isEmpty() ? __('messages.empty') : __('messages.citizens_retrieved')
        );
    }

    public function readOne(Citizen $citizen)
    {
        $data = $this->citizenService->readOne($citizen);

        return $this->successResponse(new CitizenResource($data), __('messages.citizen_retrieved'));
    }

    public function myAccount()
    {
        $citizen = Auth::user()->citizen;

        if (!$citizen) {
            return $this->errorResponse(__('messages.user_not_found'), 404);
        }

        $data = $this->citizenService->readOne($citizen);

        return $this->successResponse(new CitizenResource($data), __('messages.citizen_retrieved'));
    }

    public function delete(Citizen $citizen)
    {
        $this->citizenService->delete($citizen);
        return $this->successResponse([], __('messages.success'));
    }
}
