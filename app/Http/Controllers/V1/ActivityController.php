<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ActivityResource;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Collection;

class ActivityController extends Controller
{
    public function getLog()
    {
        $user = Auth::user();
        $logs = new Collection();
        $employee = $user->employee;

        if ($user->role == 'admin') {
            $logs = Activity::with('causer')
                ->latest()
                ->limit(100)
                ->get();
        } else if ($user->role == 'ministry_manager') {
            if (!$employee || !$employee->ministry) {
                return response()->json(['error' => 'Ministry assignment not found'], 404);
            }

            $ministry = $employee->ministry;
            $branchIds = $ministry->branches->pluck('id')->toArray();
            $complaintIds = $ministry->complaints->pluck('id')->toArray();

            $ministryLogs = Activity::where('subject_type', 'Ministry')
                ->where('subject_id', $ministry->id)
                ->get();

            $branchLogs = Activity::where('subject_type', 'MinistryBranch')
                ->whereIn('subject_id', $branchIds)
                ->get();

            $complaintLogs = Activity::where('subject_type', 'Complaint')
                ->whereIn('subject_id', $complaintIds)
                ->get();

            $logs = $ministryLogs->merge($branchLogs)->merge($complaintLogs);
        } else if ($user->role == 'branch_manager') {
            if (!$employee || !$employee->branch) {
                return response()->json(['error' => 'Branch assignment not found'], 404);
            }

            $branch = $employee->branch;
            $complaintIds = $branch->complaints->pluck('id')->toArray();

            $complaintLogs = Activity::where('subject_type', 'Complaint')
                ->whereIn('subject_id', $complaintIds)
                ->get();

            $branchLogs = Activity::where('subject_type', 'MinistryBranch')
                ->where('subject_id', $branch->id)
                ->get();

            $logs = $complaintLogs->merge($branchLogs);
        }

        $sortedLogs = $logs->sortByDesc('created_at')->values()->load('causer');

        return ActivityResource::collection($sortedLogs);
    }
}
