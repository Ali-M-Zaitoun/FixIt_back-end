<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Ministry;
use App\Models\MinistryBranch;
use App\Services\ComplaintService;
use Spatie\Activitylog\Models\Activity;
use \Spatie\LaravelPdf\Facades\Pdf;

class ReportController extends Controller
{
    public function __construct(protected ComplaintService $complaintService) {}

    public function downloadComplaintReport(Complaint $complaint)
    {
        $ministry = $complaint->ministry;
        $branch = $complaint->ministryBranch;

        $ministryAr = $ministry->translations->where('locale', 'ar')->first()?->name ?? $ministry->name;
        $ministryEn = $ministry->translations->where('locale', 'en')->first()?->name ?? $ministry->name;

        $branchAr = $branch ? ($branch->translations->where('locale', 'ar')->first()?->name ?? $branch->name) : null;
        $branchEn = $branch ? ($branch->translations->where('locale', 'en')->first()?->name ?? $branch->name) : null;

        $data = [
            'complaint' => $complaint,
            'ministryAr' => $ministryAr,
            'ministryEn' => $ministryEn,
            'branchAr' => $branchAr,
            'branchEn' => $branchEn,
        ];

        return Pdf::view('reports.complaint-report', $data)
            ->format('a4')
            ->name("Report-{$complaint->reference_number}.pdf")
            ->download();
    }

    public function downloadBranchReport(MinistryBranch $branch)
    {
        $complaints = $this->complaintService->getByBranch($branch->id);

        $user = $branch?->manager?->user;
        $manager = $user?->first_name . ' ' . $user?->last_name;

        $ministry = $branch->ministry;

        $Ids = $complaints->pluck('id')->unique()->values()->toArray();

        $logs = Activity::where('subject_type', 'Complaint')
            ->whereIn('subject_id', $Ids)->get();

        $data = [
            'branch'     => $branch,
            'total'      => $complaints->count(),
            'manager'    => $manager,
            'ministryAr' => $ministry->translation('ar')->name,
            'ministryEn' => $ministry->translation('en')->name,
            'branchAr'   => $branch->translation('ar')->name,
            'branchEn'   => $branch->translation('en')->name,
            'statuses'   => $this->getComplaintStatusStats($complaints),
            'activities' => $logs
        ];

        return Pdf::view('reports.branch-report', $data)
            ->name("branch-report-{$branch->id}.pdf")
            ->download();
    }

    public function downloadMinistryReport(Ministry $ministry)
    {
        $allComplaints = $this->complaintService->getByMinistry($ministry->id);

        $reportBranches = $ministry->branches->map(function ($branch) use ($allComplaints) {
            $branchComplaints = $allComplaints->where('ministry_branch_id', $branch->id);

            return (object) [
                'name_ar'  => $branch->translations->where('locale', 'ar')->first()?->name,
                'name_en'  => $branch->translations->where('locale', 'en')->first()?->name,
                'new'      => $branchComplaints->where('status', 'new')->count(),
                'progress' => $branchComplaints->where('status', 'in_progress')->count(),
                'resolved' => $branchComplaints->where('status', 'resolved')->count(),
                'rejected' => $branchComplaints->where('status', 'rejected')->count(),
            ];
        });

        $data = [
            'total'      => $allComplaints->count(),
            'ministryAr' => $ministry->translations->where('locale', 'ar')->first()?->name,
            'ministryEn' => $ministry->translations->where('locale', 'en')->first()?->name,
            'branches'   => $reportBranches,
            'summary'    => $this->getComplaintStatusStats($allComplaints),
        ];

        return Pdf::view('reports.ministry-report', $data)
            ->name("ministry-summary-{$ministry->id}.pdf")
            ->download();
    }

    private function getComplaintStatusStats($complaints)
    {
        return [
            'total'          => $complaints->count(),
            'new_count'      => $complaints->where('status', 'new')->count(),
            'progress_count' => $complaints->where('status', 'in_progress')->count(),
            'resolved_count' => $complaints->where('status', 'resolved')->count(),
            'rejected_count' => $complaints->where('status', 'rejected')->count(),
        ];
    }
}
