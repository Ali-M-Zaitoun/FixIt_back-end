<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ActivityResource;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Ministry;
use App\Models\MinistryBranch;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class StatisticsController extends Controller
{
    use ResponseTrait;
    public function getActivity()
    {
        $activities = Activity::all();
        return $this->successResponse(ActivityResource::collection($activities), __('messages.success'));
    }


    public function statsByStatus()
    {
        $total = Complaint::count();

        $stats = Complaint::select(
            'status',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('status')
            ->get();

        return $stats->map(function ($item) use ($total) {
            $item->percentage = ($total > 0 ? round(($item->total / $total) * 100, 2) : 0) . '%';
            return $item;
        });
    }

    public function statsByMinistryAndBranch()
    {
        return Complaint::select(
            'ministry_id',
            'ministry_branch_id',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('ministry_id', 'ministry_branch_id')
            ->with(['ministry', 'ministryBranch'])
            ->get();
    }

    public function statsByMonth()
    {
        return Complaint::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END) as new_count'),
            DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_count'),
            DB::raw('SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved_count'),
            DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_count'),
            DB::raw('COUNT(*) as total')
        )
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }


    public function statsByUserActivity()
    {
        return Complaint::select(
            'citizen_id',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('citizen_id')
            ->orderByDesc('total')
            ->with('citizen.user')
            ->take(10)
            ->get();
    }

    public function getCounts()
    {
        $data = Cache::remember('dashboard_stats', now()->addMinutes(10), function () {
            return [
                'employees_count' => Employee::count(),
                'ministries_count' => Ministry::count(),
                'branches_count' => MinistryBranch::count(),
            ];
        });
        return $this->successResponse($data, __('messages.success'));
    }
}
