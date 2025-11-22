<?php

namespace App\DAO;

use App\Models\Complaint;
use App\Models\Employee;
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
        $complaint->update([
            'locked_by' => $emp_id,
            'locked_at' => now()
        ]);
        return $complaint;
    }

    public function unlock($id)
    {
        $complaint = $this->readOne($id);
        $complaint->update([
            'locked_by' => null,
            'locked_at' => null
        ]);
        return $complaint;
    }

    public function updateStatus($id, $status)
    {
        $complaint = $this->readOne($id);
        $complaint->update(['status' => $status]);
        return $complaint;
    }

    public function addReply($complaint_id, $employee, $message)
    {
        return $employee->replies()->create([
            'complaint_id' => $complaint_id,
            'content'      => $message
        ]);
    }
}
