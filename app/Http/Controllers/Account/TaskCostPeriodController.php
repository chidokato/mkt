<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Department;
use App\Models\Report;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskCostPeriodController extends Controller
{
    public function index(Request $request)
    {
        $reports = Report::orderByDesc('id')->get();
        $departments = Department::where('parent', 0)->orderBy('name')->get();
        $channels = Channel::orderBy('name')->get();
        $selectedCompanyId = (int) $request->input('company_id', 0);
        $selectedFloorId = (int) $request->input('floor_id', 0);
        $selectedChannelId = (int) $request->input('channel_id', 0);

        $floors = Department::whereIn('parent', function($query) {
            $query->select('id')->from('departments')->where('parent', 0);
        })->orderBy('name')->get();

        $selectedReportIds = collect((array) $request->input('report_ids', []))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($selectedReportIds->isEmpty() && $reports->isNotEmpty()) {
            $selectedReportIds = collect([(int) $reports->first()->id]);
        }

        $baseTaskQuery = Task::query()
            ->leftJoin('reports', 'reports.id', '=', 'tasks.report_id')
            ->leftJoin('posts', 'posts.id', '=', 'tasks.post_id')
            ->when(
                $selectedCompanyId > 0,
                fn ($query) => $query->where('tasks.department_lv1', $selectedCompanyId)
            )
            ->when(
                $selectedFloorId > 0,
                fn ($query) => $query->where('tasks.department_lv2', $selectedFloorId)
            )
            ->when(
                $selectedChannelId > 0,
                fn ($query) => $query->where('tasks.channel_id', $selectedChannelId)
            )
            ->when(
                $selectedReportIds->isNotEmpty(),
                fn ($query) => $query->whereIn('tasks.report_id', $selectedReportIds->all())
            );

        $baseDepartmentQuery = Task::query()
            ->leftJoin('departments as current_department', 'current_department.id', '=', 'tasks.department_id')
            ->leftJoin('departments as parent_department', 'parent_department.id', '=', 'current_department.parent')
            ->when(
                $selectedCompanyId > 0,
                fn ($query) => $query->where('tasks.department_lv1', $selectedCompanyId)
            )
            ->when(
                $selectedFloorId > 0,
                fn ($query) => $query->where('tasks.department_lv2', $selectedFloorId)
            )
            ->when(
                $selectedChannelId > 0,
                fn ($query) => $query->where('tasks.channel_id', $selectedChannelId)
            )
            ->when(
                $selectedReportIds->isNotEmpty(),
                fn ($query) => $query->whereIn('tasks.report_id', $selectedReportIds->all())
            );

        $baseCompanyQuery = Task::query()
            ->leftJoin('departments as company_department', 'company_department.id', '=', 'tasks.department_lv1')
            ->when(
                $selectedCompanyId > 0,
                fn ($query) => $query->where('tasks.department_lv1', $selectedCompanyId)
            )
            ->when(
                $selectedFloorId > 0,
                fn ($query) => $query->where('tasks.department_lv2', $selectedFloorId)
            )
            ->when(
                $selectedChannelId > 0,
                fn ($query) => $query->where('tasks.channel_id', $selectedChannelId)
            )
            ->when(
                $selectedReportIds->isNotEmpty(),
                fn ($query) => $query->whereIn('tasks.report_id', $selectedReportIds->all())
            );

        $baseFloorQuery = Task::query()
            ->leftJoin('departments as floor_department', 'floor_department.id', '=', 'tasks.department_lv2')
            ->when(
                $selectedCompanyId > 0,
                fn ($query) => $query->where('tasks.department_lv1', $selectedCompanyId)
            )
            ->when(
                $selectedFloorId > 0,
                fn ($query) => $query->where('tasks.department_lv2', $selectedFloorId)
            )
            ->when(
                $selectedChannelId > 0,
                fn ($query) => $query->where('tasks.channel_id', $selectedChannelId)
            )
            ->when(
                $selectedReportIds->isNotEmpty(),
                fn ($query) => $query->whereIn('tasks.report_id', $selectedReportIds->all())
            );

        $summary = (clone $baseTaskQuery)
            ->selectRaw('COUNT(tasks.id) as total_tasks')
            ->selectRaw('COUNT(DISTINCT tasks.post_id) as total_projects')
            ->selectRaw('COALESCE(SUM(tasks.actual_costs), 0) as total_actual_costs')
            ->selectRaw('COALESCE(SUM(tasks.extra_money), 0) as total_extra_money')
            ->selectRaw('COALESCE(SUM(tasks.refund_money), 0) as total_refund_money')
            ->first();

        $projectSummaries = (clone $baseTaskQuery)
            ->selectRaw('tasks.post_id')
            ->selectRaw('COALESCE(posts.name, "Khong xac dinh") as post_name')
            ->selectRaw('COUNT(tasks.id) as total_tasks')
            ->selectRaw('COUNT(DISTINCT tasks.report_id) as total_reports')
            ->selectRaw('COALESCE(SUM(tasks.actual_costs), 0) as total_actual_costs')
            ->selectRaw('COALESCE(SUM(tasks.extra_money), 0) as total_extra_money')
            ->selectRaw('COALESCE(SUM(tasks.refund_money), 0) as total_refund_money')
            ->groupBy('tasks.post_id', 'posts.name')
            ->havingRaw('COALESCE(SUM(tasks.actual_costs), 0) > 0')
            ->orderByDesc('total_actual_costs')
            ->get();

        $projectTotalActualCosts = (float) $projectSummaries->sum('total_actual_costs');

        $departmentSummaries = (clone $baseDepartmentQuery)
            ->selectRaw('tasks.department_id')
            ->selectRaw('COALESCE(current_department.name, "Khong xac dinh") as department_name')
            ->selectRaw('COALESCE(parent_department.name, "") as parent_department_name')
            ->selectRaw('COUNT(tasks.id) as total_tasks')
            ->selectRaw('COUNT(DISTINCT tasks.report_id) as total_reports')
            ->selectRaw('COALESCE(SUM(tasks.actual_costs), 0) as total_actual_costs')
            ->groupBy('tasks.department_id', 'current_department.name', 'parent_department.name')
            ->havingRaw('COALESCE(SUM(tasks.actual_costs), 0) > 0')
            ->orderByDesc('total_actual_costs')
            ->get();

        $departmentTotalActualCosts = (float) $departmentSummaries->sum('total_actual_costs');

        $companySummaries = (clone $baseCompanyQuery)
            ->selectRaw('tasks.department_lv1')
            ->selectRaw('COALESCE(company_department.name, "Khong xac dinh") as company_name')
            ->selectRaw('COUNT(tasks.id) as total_tasks')
            ->selectRaw('COUNT(DISTINCT tasks.report_id) as total_reports')
            ->selectRaw('COALESCE(SUM(tasks.actual_costs), 0) as total_actual_costs')
            ->groupBy('tasks.department_lv1', 'company_department.name')
            ->havingRaw('COALESCE(SUM(tasks.actual_costs), 0) > 0')
            ->orderByDesc('total_actual_costs')
            ->get();

        $companyTotalActualCosts = (float) $companySummaries->sum('total_actual_costs');

        $floorSummaries = (clone $baseFloorQuery)
            ->selectRaw('tasks.department_lv2')
            ->selectRaw('COALESCE(floor_department.name, "Khong xac dinh") as floor_name')
            ->selectRaw('COUNT(tasks.id) as total_tasks')
            ->selectRaw('COUNT(DISTINCT tasks.report_id) as total_reports')
            ->selectRaw('COALESCE(SUM(tasks.actual_costs), 0) as total_actual_costs')
            ->groupBy('tasks.department_lv2', 'floor_department.name')
            ->havingRaw('COALESCE(SUM(tasks.actual_costs), 0) > 0')
            ->orderByDesc('total_actual_costs')
            ->get();

        $floorTotalActualCosts = (float) $floorSummaries->sum('total_actual_costs');

        return view('account.report.statistical', [
            'reports' => $reports,
            'departments' => $departments,
            'floors' => $floors,
            'channels' => $channels,
            'selectedReportIds' => $selectedReportIds,
            'selectedCompanyId' => $selectedCompanyId,
            'selectedFloorId' => $selectedFloorId,
            'selectedChannelId' => $selectedChannelId,
            'summary' => $summary,
            'projectSummaries' => $projectSummaries,
            'projectTotalActualCosts' => $projectTotalActualCosts,
            'departmentSummaries' => $departmentSummaries,
            'departmentTotalActualCosts' => $departmentTotalActualCosts,
            'companySummaries' => $companySummaries,
            'companyTotalActualCosts' => $companyTotalActualCosts,
            'floorSummaries' => $floorSummaries,
            'floorTotalActualCosts' => $floorTotalActualCosts,
        ]);
    }
}
