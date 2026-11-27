<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Task;
use App\Models\Report;
use App\Models\Post;
use App\Models\Channel;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use App\Helpers\TreeHelper;
use App\Helpers\TreeHelperLv2Only;
use Illuminate\Support\Facades\Auth;


class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::withCount('tasks')->get();
        return view('account.report.index', compact('reports'));
    }

    private function onlyDigitsToInt($value): int
    {
        $n = preg_replace('/[^\d]/', '', (string)$value);
        return $n === '' ? 0 : (int)$n;
    }

    public function recalcExpected(Request $request, Report $report)
    {
        $tasks = Task::where('approved',1)->where('report_id', $report->id)->get(['expected_costs', 'days']);

        $sum = 0;
        foreach ($tasks as $t) {
            $sum += $this->onlyDigitsToInt($t->expected_costs * $t->days);
        }

        $report->expected_costs = $sum;
        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật tổng tiền dự kiến',
            'total' => $sum,
            'total_format' => number_format($sum, 0, ',', '.'),
        ]);
    }

    public function recalcActual(Request $request, Report $report)
    {
        $tasks = Task::where('report_id', $report->id)->get(['actual_costs']);

        $sum = 0;
        foreach ($tasks as $t) {
            $sum += $this->onlyDigitsToInt($t->actual_costs  * $t->days);
        }

        $report->actual_costs = $sum;
        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật tổng tiền thực tế',
            'total' => $sum,
            'total_format' => number_format($sum, 0, ',', '.'),
        ]);
    }

    public function syncKpi(Report $report)
    {
        $users = User::whereNotNull('kpi')->get()->pluck('kpi', 'id');
        $tasks = Task::where('report_id', $report->id)->get();
        
        foreach ($tasks as $task) {
            if (isset($users[$task->user])) {
                $task->kpi = $users[$task->user];
                $task->save();
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Đồng bộ KPI thành công',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'date' => 'required|string',
        ]);

        // Tách khoảng thời gian
        $dates = explode(' - ', $request->date); // ["01/11/2025", "15/11/2025"]
        $time_start = isset($dates[0]) ? \Carbon\Carbon::createFromFormat('d/m/Y', $dates[0])->format('Y-m-d') : null;
        $time_end   = isset($dates[1]) ? \Carbon\Carbon::createFromFormat('d/m/Y', $dates[1])->format('Y-m-d') : null;
        $days = Carbon::parse($time_start)->diffInDays(Carbon::parse($time_end)) + 1;
        // Lưu báo cáo
        Report::create([
            'name' => $request->name,
            'time_start' => $time_start,
            'time_end' => $time_end,
            'days' => $days,
        ]);

        return response()->json(['success' => true, 'message' => 'Đã lưu báo cáo']);
    }

    public function loadReport()
    {
        $reports = Report::withCount('tasks')->orderBy('created_at','desc')->get();
        return view('account.layout.load_report', compact('reports'));
    }

    public function show(Request $request, $id)
    {
        $report = Report::find($id);

        // $task = Task::all();
        // foreach($task as $t){
        //     if ($t->approved == null) {
        //         $task = Task::find($t->id);
        //         $task->approved = '0';
        //         $task->save();
        //     }
        // }

        if (!$report) {
            abort(404, 'Report not found');
        }

        $days = Carbon::parse($report->time_start)->diffInDays(Carbon::parse($report->time_end)) + 1;

        // Base query
        $query = Task::where('report_id', $id)
            ->orderBy('department_lv1')
            ->orderBy('department_lv2')
            ->orderBy('department_id')
            ->orderBy('user')
            ->orderBy('post_id');
        if ($request->department_id) {
            $departmentIds = Department::getChildIds($request->department_id);
            $query->whereHas('department', function($q) use ($departmentIds) {
                $q->whereIn('id', $departmentIds);
            });

        }

        if ($request->user_id) {
            $query->where('user', $request->user_id);
        }

        if ($request->post_id) {
            $query->where('post_id', $request->post_id);
        }

        if ($request->channel_id) {
            $channelIds = Channel::getChildIds($request->channel_id); // lấy tất cả con
            $query->whereIn('channel_id', $channelIds);
        }

        if ($request->filled('approved')) {
            $query->where('approved', $request->approved);
        }

        $task = $query->paginate(1000)->appends($request->query());

        // Select filter
        $users = User::get();
        $departments = Department::all();
        $departmentOptions = TreeHelper::buildOptions($departments,0,'',$request->department_id);

        $posts       = Post::where('sort_by', 'Product')->get();
        $channels    = Channel::all();
        $channelsOptions = TreeHelper::buildOptions($channels,0,'',$request->channel_id);

        $tongTien = $task->sum(function ($val) {
            return $val->total_costs ?? ($val->days * $val->expected_costs);
        });

        return view('account.report.show', compact(
            'users',
            'report',
            'task',
            'days',
            'departmentOptions',
            'posts',
            'channelsOptions',
            'tongTien'
        ));
    }



    public function update(Request $request)
    {
        $dates = explode(' - ', $request->date);

        $start = Carbon::createFromFormat('d/m/Y', $dates[0]);
        $end   = Carbon::createFromFormat('d/m/Y', $dates[1]);

        $days = $start->diffInDays($end) + 1; // tính cả 2 ngày

        Report::where('id', $request->id)->update([
            'name' => $request->name,
            'time_start' => $start->format('Y-m-d'),
            'time_end'   => $end->format('Y-m-d'),
            'days' => $days,
        ]);

        // return back()->with('success', 'Đã lưu thành công!');
        return response()->json(['success' => true]);
    }



    public function delete(Request $request)
    {
        $report = Report::withCount('tasks')->findOrFail($request->id);

        if ((int) $report->tasks_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bao cao da co tac vu ben trong, khong the xoa.',
            ], 422);
        }

        $report->delete();

        return response()->json(['success' => true]);
    }

    public function active(Request $request)
    {
        Report::where('id', $request->id)->update([
            'active' => $request->active
        ]);

        return response()->json(['success' => true]);
    }

    public function payment(Request $request, $id)
    {
        $user = Auth::user();

        $reports = Report::orderByDesc('id')->get();
        $selectedReportId = (int) $request->input('report_id', optional($reports->first())->id);

        $departments = Department::orderBy('name')->get();

        // dropdown đang là LV1+LV2 => id có thể là lv1 hoặc lv2
        $selectedDeptId = (int) $request->input('department_id', $user->department_lv2);

        // build options (dùng hàm của bạn)
        $departmentOptions = TreeHelperLv2Only::buildOptions($departments, $selectedDeptId);

        // ===== xác định danh sách LV2 cần lọc =====
        $selectedDept = $departments->firstWhere('id', $selectedDeptId);

        $lv2Ids = [];
        if ($selectedDept) {
            if ((int)$selectedDept->parent === 0) {
                // chọn LV1 => lấy toàn bộ LV2 con
                $lv2Ids = $departments->where('parent', $selectedDeptId)->pluck('id')->map(fn($x)=>(int)$x)->all();
            } else {
                // chọn LV2
                $lv2Ids = [$selectedDeptId];
            }
        } else {
            // fallback: sàn của user
            $lv2Ids = [(int)$user->department_lv2];
        }

        // ===== lấy users + tasks theo report =====
        $users = User::query()
            ->whereIn('department_lv2', $lv2Ids)
            ->orderBy('department_lv2')
            ->orderBy('department_id')
            ->with([
                'tasks' => function ($q) use ($selectedReportId) {
                    $q->where('approved', 1)
                      ->when($selectedReportId, fn($qq) => $qq->where('report_id', $selectedReportId))
                      ->with(['department', 'Post', 'Channel', 'Report']);
                }
            ])
            ->get();

        // ===== build cây + tổng: LV1 -> LV2 -> ROOM -> USER -> TASK =====
        $tree = [];
        $grandGross = 0;
        $grandNet   = 0;

        foreach ($users as $u) {
            foreach ($u->tasks as $t) {

                $lv2Id = (int)$u->department_lv2;
                $lv2   = $departments->firstWhere('id', $lv2Id);
                $lv1Id = (int)($lv2?->parent ?? 0);
                $lv1   = $departments->firstWhere('id', $lv1Id);

                $lv1Name = $lv1?->name ?? ('Cty #' . $lv1Id);
                $lv2Name = $lv2?->name ?? ('Sàn #' . $lv2Id);

                $roomId   = (int)($t->department_id ?? 0);
                $roomName = $t->department?->name
                    ?? $departments->firstWhere('id', $roomId)?->name
                    ?? ('Phòng #' . $roomId);

                // init lv1
                if (!isset($tree[$lv1Id])) {
                    $tree[$lv1Id] = ['id'=>$lv1Id,'name'=>$lv1Name,'gross'=>0,'net'=>0,'lv2s'=>[]];
                }

                // init lv2
                if (!isset($tree[$lv1Id]['lv2s'][$lv2Id])) {
                    $tree[$lv1Id]['lv2s'][$lv2Id] = ['id'=>$lv2Id,'name'=>$lv2Name,'gross'=>0,'net'=>0,'rooms'=>[]];
                }

                // init room
                if (!isset($tree[$lv1Id]['lv2s'][$lv2Id]['rooms'][$roomId])) {
                    $tree[$lv1Id]['lv2s'][$lv2Id]['rooms'][$roomId] = ['id'=>$roomId,'name'=>$roomName,'gross'=>0,'net'=>0,'users'=>[]];
                }

                // init user node
                if (!isset($tree[$lv1Id]['lv2s'][$lv2Id]['rooms'][$roomId]['users'][$u->id])) {
                    $tree[$lv1Id]['lv2s'][$lv2Id]['rooms'][$roomId]['users'][$u->id] = [
                        'id'=>$u->id,
                        'employee_code'=>$u->employee_code,
                        'yourname'=>$u->yourname,
                        'gross'=>0,'net'=>0,
                        'tasks'=>[]
                    ];
                }

                // push task + totals
                $tree[$lv1Id]['lv2s'][$lv2Id]['rooms'][$roomId]['users'][$u->id]['tasks'][] = $t;

                $tree[$lv1Id]['lv2s'][$lv2Id]['rooms'][$roomId]['users'][$u->id]['gross'] += (int)$t->gross_cost;
                $tree[$lv1Id]['lv2s'][$lv2Id]['rooms'][$roomId]['users'][$u->id]['net']   += (int)$t->net_cost;

                $tree[$lv1Id]['lv2s'][$lv2Id]['rooms'][$roomId]['gross'] += (int)$t->gross_cost;
                $tree[$lv1Id]['lv2s'][$lv2Id]['rooms'][$roomId]['net']   += (int)$t->net_cost;

                $tree[$lv1Id]['lv2s'][$lv2Id]['gross'] += (int)$t->gross_cost;
                $tree[$lv1Id]['lv2s'][$lv2Id]['net']   += (int)$t->net_cost;

                $tree[$lv1Id]['gross'] += (int)$t->gross_cost;
                $tree[$lv1Id]['net']   += (int)$t->net_cost;

                $grandGross += (int)$t->gross_cost;
                $grandNet   += (int)$t->net_cost;
            }
        }

        $tree = collect($tree)->map(function ($lv1) {
            $lv1['lv2s'] = collect($lv1['lv2s'])->map(function ($lv2) {
                $lv2['rooms'] = collect($lv2['rooms'])->map(function ($room) {
                    $room['users'] = collect($room['users']);
                    return $room;
                });
                return $lv2;
            });
            return $lv1;
        });

        return view('account.task.index', compact(
            'user',
            'reports',
            'selectedReportId',
            'departmentOptions',
            'tree',
            'grandGross',
            'grandNet'
        ));
    }


}
