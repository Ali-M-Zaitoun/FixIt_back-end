<?php

namespace App\DAO;

use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Reply;
use App\Services\MinistryService;

use function Symfony\Component\Clock\now;

class ComplaintDAO
{
    public function submit($data)
    {
        return Complaint::create($data);
    }
    public function getByBranch($ministry_branch_id)
    {
        return Complaint::where('ministry_branch_id', $ministry_branch_id)->get();
    }

    public function getByMinistry($branchIds)
    {
        return Complaint::whereIn('ministry_branch_id', $branchIds)->get();
    }

    public function getMyComplaints($citizen_id)
    {
        return Complaint::where('citizen_id', $citizen_id)->get();
    }

    public function read()
    {
        return Complaint::all();
    }

    public function readOne($id)
    {
        return Complaint::where('id', $id)->first();
    }

    public function lock($complaint, $emp_id)
    {
        return $complaint->update([
            'locked_by' => $emp_id,
            'locked_at' => now(),
            'status'    => 'in_progress'
        ]);
    }

    public function unlock(Complaint $complaint)
    {
        return $complaint->update([
            'locked_by' => null,
            'locked_at' => null
        ]);
    }

    public function updateStatus($complaint, $status, $message)
    {
        $complaint->update([
            'status' => $status,
            'notes' => $message
        ]);
        return $complaint;
    }

    public function delete($complaint)
    {
        return $complaint->delete();
    }
}
