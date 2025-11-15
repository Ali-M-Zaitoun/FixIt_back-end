<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\MinistryBranch;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    use ResponseTrait;
    public function submit(SubmitComplaintRequest $request)
    {
        $data = $request->validated();

        unset($data['media']);

        $data['citizen_id'] = Auth::user()->citizen->id;

        $ministryBranch = MinistryBranch::with('ministry')->findOrFail($data['ministry_branch_id']);

        $governorateCode = DB::table('governorates')
            ->where('id', $data['governorate_id'])
            ->value('code');

        $data['reference_number'] = sprintf(
            '%s_%s_%s',
            $ministryBranch->ministry->abbreviation,
            $governorateCode,
            Str::random(8)
        );

        $complaint = Complaint::create($data);

        foreach ($request->file('media', []) as $file) {
            $path = $file->store('complaints', 'public');

            $complaint->media()->create([
                'path' => $path,
                'type' => in_array(
                    $file->getClientOriginalExtension(),
                    ['pdf', 'doc', 'docx']
                ) ? 'file' : 'img',
            ]);
        }

        return $this->successResponse(
            __('messages.complaint_submitted'),
            ['complaint' => $complaint],
            201
        );
    }

    public function getComplaints($ministry_branch_id)
    {
        $user = User::with('employee.ministryBranch')->findOrFail(
            Auth::id()
        );

        // if ($user->employee->ministryBranch->id != $ministry_branch_id) {
        //     return $this->errorResponse(
        //         __('messages.unauthorized'),
        //         [],
        //         403
        //     );
        // }

        $complaints = Complaint::where('ministry_branch_id', $ministry_branch_id)->get();
        $complaints = ComplaintResource::collection($complaints);

        return $this->successResponse(
            __('messages.complaints_fetched'),
            ['complaints' => $complaints]
        );
    }
}
