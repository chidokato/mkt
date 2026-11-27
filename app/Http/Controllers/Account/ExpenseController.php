<?php

namespace App\Http\Controllers\Account;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Services\WalletService;
use App\Models\User;
use App\Models\Department;
use App\Models\Wallet;
use App\Models\Report;
use App\Models\Post;
use App\Models\Channel;
use Illuminate\Support\Facades\Auth;
use App\Helpers\TreeHelperLv2Only;
use App\Helpers\TreeHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException; 
use Illuminate\Support\Facades\Schema;

use App\Services\TaskFinanceService;

class ExpenseController extends Controller
{
    public function expense(Request $request)
    {
        // user_ids[]: nếu không có => mặc định chọn auth id
        $selectedUserIds = collect((array) $request->input('user_ids'))
            ->filter(fn($v) => $v !== null && $v !== '')
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        if (empty($selectedUserIds)) {
            $selectedUserIds = [ (int) auth()->id() ];
        }

        $q = Task::query()
            ->whereIn('user', $selectedUserIds) // bạn dùng cột user
            ->orderByDesc('id');

        // report_id: rỗng lúc đầu, lọc theo ID khi user chọn
        $selectedReportId = $request->filled('report_id') ? (int) $request->report_id : null;
        if ($selectedReportId) {
            $q->where('report_id', $selectedReportId);
        }

        $tasks = $q->get();

        $sumTotal = $tasks->sum(fn($t) => (float)($t->expected_costs ?? 0) * (float)($t->days ?? 0));
        $sumActual = $tasks->sum(fn($t) => (float)($t->actual_costs ?? 0));
        $sum_refund_money = $tasks->sum(fn($t) => (float)($t->refund_money ?? 0));
        $sum_extra_money = $tasks->sum(fn($t) => (float)($t->extra_money ?? 0));

        $reports  = Report::orderByDesc('id')->get();
        $users    = User::get();
        $posts    = Post::where('sort_by', 'Product')->get();
        $channels = Channel::get();

        return view('account.task.expense', compact(
            'tasks','sumTotal','sumActual',
            'users','reports','posts','channels',
            'selectedReportId','selectedUserIds',
            'sum_refund_money',
            'sum_extra_money'
        ));
    }


    public function actualcosts(Request $request)
    {
        $user = auth()->user();
        $maxReportId = Report::max('id'); // có thể null nếu chưa có report
        $selectedReportId = $request->has('report_id')
            ? ($request->filled('report_id') ? (int)$request->report_id : null)
            : (int)$maxReportId;
        // Load departments 1 lần để build options + lọc đệ quy
        $departments = Department::select('id', 'parent', 'name')
            ->orderBy('name')
            ->get();

        // selected department: ưu tiên request, không có thì lấy department_id của user
        $selectedDeptId = $request->filled('department_id')
            ? (int) $request->department_id
            : null; // hoặc 0

        $q = Task::query()
            ->with(['handler', 'department', 'Post', 'channel'])
            ->orderBy('department_lv1')   // ưu tiên 1
            ->orderBy('department_lv2')   // ưu tiên 1
            ->orderBy('department_id')       // ưu tiên 2
            ->orderBy('user')          // ưu tiên 3 (hoặc user_id)
            ->orderBy('post_id')       // ưu tiên 2
            ->orderByDesc('id');             // phụ: cho ổn định

        // Tìm theo mã NV / tên NV
        if ($request->filled('handler_ids')) {
            $handlerIds = array_values(array_filter(array_map('intval', (array)$request->handler_ids)));
            if (!empty($handlerIds)) {
                $q->whereIn('user', $handlerIds); // nếu cột là user_id thì đổi thành user_id
            }
        }


        // Lọc phòng/nhóm (đệ quy: gồm cả con cháu)
        if ($request->filled('department_id')) {
            $deptId = (int) $request->department_id;

            // lấy danh sách id con cháu + chính nó
            $deptIds = TreeHelper::descendantIds($departments, $deptId, true);

            $q->whereIn('department_id', $deptIds);
        }

        // Lọc report
        if (!empty($selectedReportId)) {
            $q->where('report_id', $selectedReportId);
        }
        // if ($request->filled('report_id')) {
        //     $q->where('report_id', (int)$request->report_id);
        // }
        // duyệt ?
        if ($request->filled('approved')) {
            $q->where('approved', $request->approved);
        }
        // đóng tiền ?
        if ($request->filled('paid')) {
            $q->where('paid', $request->paid);
        }
        // tất toán ?
        if ($request->filled('settled')) {
            $q->where('settled', $request->settled);
        }
        // kênh
        if ($request->channel_id) {
            $channelIds = Channel::getChildIds($request->channel_id); // lấy tất cả con
            $q->whereIn('channel_id', $channelIds);
        }
        // dự án
        if ($request->post_id) {
            $q->where('post_id', $request->post_id);
        }

        if ($request->filled('outstanding') && in_array((int)$request->outstanding, [0, 1], true)) {
            $outstanding = (int) $request->outstanding;

            $outstanding === 1
                ? $q->where('extra_money', '>', 0)
                : $q->where('refund_money', '>', 0);
        }


        $tasks = $q->get();

        // Tổng theo trang hiện tại
        $sumTotal = $tasks->sum(function ($t) {
            return (float)($t->expected_costs ?? 0) * (float)($t->days ?? 0);
        });

        $sumActual = $tasks->sum(function ($t) {
            return (float)($t->actual_costs ?? 0);
        });

        $sumPaid = $tasks->sum(function ($t) {
            $total = (float)($t->expected_costs ?? 0) * (float)($t->days ?? 0);
            $rate  = (float)($t->rate ?? 0);
            return $total * (1 - $rate/100);
        });

        $sum_expected = $tasks->sum(function ($t) {
            return (float)($t->price_expected ?? 0);
        });
        $sum_actual_costs = $tasks->sum(function ($t) {
            return (float)($t->actual_costs ?? 0);
        });
        $sum_refund_money = $tasks->sum(function ($t) {
            return (float)($t->refund_money ?? 0);
        });
        $sum_extra_money = $tasks->sum(function ($t) {
            return (float)($t->extra_money ?? 0);
        });

        // Render filter options
        $reports = Report::orderByDesc('id')->get();
        $users = User::get();
        $posts = Post::where('sort_by', 'Product')->get();
        $channels = Channel::all();
        $channelsOptions = TreeHelper::buildOptions($channels,0,'',$request->channel_id);

        // selected option ưu tiên request('department_id') nếu có, fallback user dept
        // $selectedForOptions = $request->filled('department_id')
        //     ? (int)$request->department_id
        //     : (int)($user->department_id ?? 0);

        $departmentOptions = TreeHelper::buildOptions(
            $departments,
            0,
            '',
            $selectedDeptId,
            'id',
            'parent',
            'name'
        );

        $pageTitle = 'Danh sách link đăng ký MKT';
        $excelFilename = 'tasks_' . date('Ymd_His') . '.xlsx';
        $isNsDebt = false;

        return view('account.task.actualcosts', compact(
            'reports',
            'departmentOptions',
            'tasks',
            'sumTotal',
            'sumActual',
            'sumPaid',
            'selectedReportId',
            'users',
            'posts',
            'channelsOptions',
            'sum_expected',
            'sum_actual_costs',
            'sum_refund_money',
            'sum_extra_money',
            'pageTitle',
            'excelFilename',
            'isNsDebt',
        ));
    }

    public function nsDebt(Request $request)
    {
        $user = auth()->user();
        $selectedReportId = null;
        // Load departments 1 lần để build options + lọc đệ quy
        $departments = Department::select('id', 'parent', 'name')
            ->orderBy('name')
            ->get();

        // selected department: ưu tiên request, không có thì lấy department_id của user
        $selectedDeptId = $request->filled('department_id')
            ? (int) $request->department_id
            : null; // hoặc 0

        $q = Task::query()
            ->with(['handler', 'department', 'Post', 'channel', 'Report'])
            ->where('extra_money', '>', 0)
            ->where(function ($query) {
                $query->where('settled', 0)->orWhereNull('settled');
            })
            ->orderByDesc('report_id')       // sắp xếp report từ lớn đến bé
            ->orderBy('department_lv1')   // ưu tiên 1
            ->orderBy('department_lv2')   // ưu tiên 1
            ->orderBy('department_id')       // ưu tiên 2
            ->orderBy('user')          // ưu tiên 3 (hoặc user_id)
            ->orderBy('post_id')       // ưu tiên 2
            ->orderByDesc('id');             // phụ: cho ổn định

        // Tìm theo mã NV / tên NV
        if ($request->filled('handler_ids')) {
            $handlerIds = array_values(array_filter(array_map('intval', (array)$request->handler_ids)));
            if (!empty($handlerIds)) {
                $q->whereIn('user', $handlerIds); // nếu cột là user_id thì đổi thành user_id
            }
        }

        // Lọc phòng/nhóm (đệ quy: gồm cả con cháu)
        if ($request->filled('department_id')) {
            $deptId = (int) $request->department_id;

            // lấy danh sách id con cháu + chính nó
            $deptIds = TreeHelper::descendantIds($departments, $deptId, true);

            $q->whereIn('department_id', $deptIds);
        }

        $tasks = $q->get();

        // Tổng theo trang hiện tại
        $sumTotal = $tasks->sum(function ($t) {
            return (float)($t->expected_costs ?? 0) * (float)($t->days ?? 0);
        });

        $sumActual = $tasks->sum(function ($t) {
            return (float)($t->actual_costs ?? 0);
        });

        $sumPaid = $tasks->sum(function ($t) {
            $total = (float)($t->expected_costs ?? 0) * (float)($t->days ?? 0);
            $rate  = (float)($t->rate ?? 0);
            return $total * (1 - $rate/100);
        });

        $sum_expected = $tasks->sum(function ($t) {
            return (float)($t->price_expected ?? 0);
        });
        $sum_actual_costs = $tasks->sum(function ($t) {
            return (float)($t->actual_costs ?? 0);
        });
        $sum_refund_money = $tasks->sum(function ($t) {
            return (float)($t->refund_money ?? 0);
        });
        $sum_extra_money = $tasks->sum(function ($t) {
            return (float)($t->extra_money ?? 0);
        });

        // Render filter options
        $reports = Report::orderByDesc('id')->get();
        $users = User::get();
        $posts = Post::where('sort_by', 'Product')->get();
        $channels = Channel::all();
        $channelsOptions = TreeHelper::buildOptions($channels,0,'',$request->channel_id);

        $departmentOptions = TreeHelper::buildOptions(
            $departments,
            0,
            '',
            $selectedDeptId,
            'id',
            'parent',
            'name'
        );

        $pageTitle = 'Danh sách NS nợ tiền';
        $excelFilename = 'danh_sach_ns_no_tien_' . date('Ymd_His') . '.xlsx';
        $isNsDebt = true;

        return view('account.task.ns_debt', compact(
            'reports',
            'departmentOptions',
            'tasks',
            'sumTotal',
            'sumActual',
            'sumPaid',
            'selectedReportId',
            'users',
            'posts',
            'channelsOptions',
            'sum_expected',
            'sum_actual_costs',
            'sum_refund_money',
            'sum_extra_money',
            'pageTitle',
            'excelFilename',
            'isNsDebt',
        ));
    }


    public function ajaxUpdateActualCosts(Request $request, Task $task)
    {
        if ((int) ($task->settled ?? 0) === 1) {
            return response()->json([
                'ok' => false,
                'message' => 'Task da tat toan, khong the sua chi phi thuc te.'
            ], 422);
        }
        // input có thể là "1.200.000" hoặc "1,200,000" => sanitize
        $raw   = (string) $request->input('actual_costs', '');
        $clean = preg_replace('/[^\d\-]/', '', $raw); // giữ số và dấu -

        $data = $request->merge(['actual_costs' => $clean])->validate([
            'actual_costs' => ['required', 'numeric', 'min:0'],
        ]);

        // cập nhật actual_costs
        $task->actual_costs = (float) $data['actual_costs'];

        // ====== TÍNH & LƯU extra_money + refund_money ======
        $approved = (int) ($task->approved ?? 0);
        $paid     = (int) ($task->paid ?? 0);

        $actual   = (float) ($task->actual_costs ?? 0);
        $expected = (float) (($task->expected_costs ?? 0) * ($task->days ?? 0));
        $rate     = (float) ($task->rate ?? 0);

        if ($approved !== 1 || $paid !== 1) {
            $task->extra_money  = $actual;
            $task->refund_money = 0;
        } else {
            if ($actual > $expected) {
                $task->extra_money  = $actual - $expected;
                $task->refund_money = 0;
            } else {
                $diff = $expected - $actual; // >= 0
                $task->extra_money  = 0;
                $task->refund_money = $diff * (1 - $rate / 100);
            }
        }

        $task->save();

        return response()->json([
            'ok' => true,
            'message' => 'Thành công',
            'task' => [
                'id' => $task->id,
                'approved' => $approved,
                'paid' => $paid,

                'actual_costs' => (float)$task->actual_costs,
                'actual_costs_formatted' => number_format((float)$task->actual_costs, 0, ',', '.'),

                'extra_money' => (float)$task->extra_money,
                'extra_money_formatted' => number_format((float)$task->extra_money, 0, ',', '.'),

                'refund_money' => (float)$task->refund_money,
                'refund_money_formatted' => number_format((float)$task->refund_money, 0, ',', '.'),

                'extra_money' => (float)$task->extra_money,
                'extra_money_formatted' => number_format((float)$task->extra_money, 0, ',', '.'),
                'refund_money' => (float)$task->refund_money,
                'refund_money_formatted' => number_format((float)$task->refund_money, 0, ',', '.'),
            ],
        ]);
    }


    public function ajaxSearchTasks(Request $request)
    {
        $q = Task::query()
            ->with(['handler', 'department', 'Post', 'channel'])
            ->orderByDesc('id');

        if ($request->filled('name')) {
            $key = trim($request->name);

            $q->whereHas('handler', function ($h) use ($key) {
                $h->where('employee_code', 'like', "%{$key}%")
                  ->orWhere('yourname', 'like', "%{$key}%");
            });
        }

        if ($request->filled('department_id')) {
            $q->where('department_id', $request->department_id);
        }

        if ($request->filled('report_id')) {
            $q->where('report_id', $request->report_id);
            // nếu là post_id thì đổi:
            // $q->where('post_id', $request->report_id);
        }

        $tasks = $q->paginate(20)->appends($request->query());

        $sumTotal = $tasks->getCollection()->sum(function ($t) {
            return (float)($t->expected_costs ?? 0) * (float)($t->days ?? 0);
        });

        $sumPaid = $tasks->getCollection()->sum(function ($t) {
            $total = (float)($t->expected_costs ?? 0) * (float)($t->days ?? 0);
            $rate  = (float)($t->rate ?? 0);
            return $total * (1 - $rate/100);
        });

        // !!! đổi đúng path partial của bạn:
        $tbodyHtml = view('account.task.partials._rows', compact('tasks'))->render();
        $paginationHtml = view('account.task.partials._pagination', compact('tasks'))->render();

        return response()->json([
            'ok' => true,
            'tbody_html' => $tbodyHtml,
            'pagination_html' => $paginationHtml,
            'sum_total' => number_format($sumTotal, 0, ',', '.'),
            'sum_paid'  => number_format($sumPaid, 0, ',', '.'),
            'has_rows'  => $tasks->count() > 0,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'        => ['required', 'integer', 'exists:users,id'],
            'post_id'        => ['required', 'integer', 'exists:posts,id'],
            'channel_id'     => ['required', 'integer', 'exists:channels,id'],
            'expected_costs' => ['required', 'integer', 'min:0'],
            'redirect_url'   => ['nullable', 'string'],
            'addreport_id'   => ['nullable', 'string'],
            'content'        => ['nullable', 'string'],
        ]);

        $user = User::find($data['user_id']);
        $post = Post::find($data['post_id']);
        $report = Report::find($data['addreport_id']);

        // TODO: map field đúng với DB của bạn (vd handler_id, Post_id...)
        $task = Task::create([
            'user_id'        => Auth::user()->id,
            'user'           => $data['user_id'],
            'report_id'      => $data['addreport_id'],
            'days'           => $report['days'],
            'department_lv1' => $user['department_lv1'],
            'department_lv2' => $user['department_lv2'],
            'department_id'  => $user['department_id'],
            'rate'           => $post->rate,
            'post_id'        => $data['post_id'],
            'channel_id'     => $data['channel_id'],
            'expected_costs' => $data['expected_costs'],
            'content'        => $data['content'] ?? null,
        ]);

        $redirect = $data['redirect_url'] ?: url()->previous();

        // Nếu submit bằng AJAX thì trả JSON để frontend redirect
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Đã thêm mới',
                'redirect' => $redirect,
                'id' => $task->id,
            ]);
        }

        // Submit thường
        return redirect()->to($redirect)->with('success', 'Đã thêm mới');
    }



    public function toggleSettled(Request $request, Task $task, WalletService $walletService)
    {
        $data = $request->validate([
            'settled' => ['required', 'in:0,1'],
        ]);
        $toSettled = (int) $data['settled'];

        if ($toSettled === (int) $task->settled) {
            return response()->json(['ok' => true, 'message' => 'No change.']);
        }

        try {
            if ($toSettled === 1) {
                $result = $walletService->settleTask($task, auth()->id());

                // refresh task để chắc chắn settled_at/by đã update
                $task->refresh();

                return response()->json([
                    'ok' => true,
                    'message' => 'Đã tất toán',
                    'task' => [
                        'id' => $task->id,
                        'settled' => 1,
                        'refund_money' => (float) $result['refund'],
                        'extra_money'  => (float) $result['extra'],
                    ],
                ]);
            }

            // =========================
            // settled = 0: ĐẢO NGƯỢC (đảo ví + ghi lịch sử)
            // =========================
            $walletService->unsettleTask($task, auth()->id());

            $task->refresh();

            return response()->json([
                'ok' => true,
                'message' => 'Đã hủy tất toán',
                'task' => [
                    'id' => $task->id,
                    'settled' => 0,
                    'refund_money' => (float) ($task->refund_money ?? 0),
                    'extra_money'  => (float) ($task->extra_money ?? 0),
                ],
            ]);

        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?? 'Thất bại';
            return response()->json(['ok' => false, 'message' => $msg], 409);
        } catch (\Throwable $e) {
            // local thì trả message thật để debug (toast sẽ hiện góc trái dưới)
            if (config('app.debug')) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return response()->json(['ok' => false, 'message' => 'Có lỗi xảy ra.'], 500);
        }

    }

    public function bulkImportActualCosts(Request $request)
    {
        $request->validate([
            'employee_codes' => ['required_without:file', 'nullable', 'string'],
            'file'           => ['required_without:employee_codes', 'nullable', 'mimes:xlsx,xls'],
            'post_id'        => ['required', 'integer', 'exists:posts,id'],
            'channel_id'     => ['required', 'integer', 'exists:channels,id'],
            'actual_costs'   => ['required', 'numeric', 'min:0'],
            'days'           => ['required', 'numeric', 'min:0'],
            'rate'           => ['nullable', 'numeric', 'min:0', 'max:100'],
            'content'        => ['nullable', 'string'],
            'note'           => ['nullable', 'string'],
        ], [
            'employee_codes.required_without' => 'Vui lòng nhập danh sách mã nhân viên.',
            'post_id.required'                => 'Vui lòng chọn dự án.',
            'channel_id.required'             => 'Vui lòng chọn kênh.',
            'actual_costs.required'           => 'Vui lòng nhập số tiền.',
            'days.required'                   => 'Vui lòng nhập số ngày.',
        ]);

        $employeeCodes = [];
        $noteContent = $request->input('content', $request->input('note'));

        if ($request->filled('employee_codes')) {
            $lines = preg_split('/[\r\n]+/', (string) $request->employee_codes);
            foreach ($lines as $line) {
                $code = trim(str_replace(["\xc2\xa0", "\u{00A0}"], ' ', $line));
                if ($code !== '') {
                    $employeeCodes[] = $code;
                }
            }
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                foreach ($rows as $index => $row) {
                    if ($index === 0) continue; // Bỏ qua dòng tiêu đề
                    $code = trim(str_replace(["\xc2\xa0", "\u{00A0}"], ' ', $row[0] ?? ''));
                    if ($code !== '') {
                        $employeeCodes[] = $code;
                    }
                }
            } catch (\Exception $e) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Lỗi khi đọc file Excel: ' . $e->getMessage()
                ], 500);
            }
        }

        if (empty($employeeCodes)) {
            return response()->json([
                'ok' => false,
                'message' => 'Danh sách mã nhân viên trống. Vui lòng nhập ít nhất một mã nhân viên.'
            ], 422);
        }

        // Phân loại mã nhân viên hợp lệ và không tìm thấy
        $validItems = [];
        $notFoundCodes = [];

        foreach ($employeeCodes as $employeeCode) {
            $user = User::where('employee_code', $employeeCode)->first();
            if (!$user) {
                $notFoundCodes[] = $employeeCode;
            } else {
                $validItems[] = [
                    'user' => $user,
                    'code' => $employeeCode,
                ];
            }
        }

        $uniqueNotFound = array_values(array_unique($notFoundCodes));

        // Nếu chưa bấm xác nhận (bước kiểm tra trước khi thêm)
        if (!$request->boolean('confirmed')) {
            return response()->json([
                'ok'              => true,
                'preview'         => true,
                'total'           => count($employeeCodes),
                'found_count'     => count($validItems),
                'error_count'     => count($uniqueNotFound),
                'not_found_codes' => $uniqueNotFound,
            ]);
        }

        if (empty($validItems)) {
            return response()->json([
                'ok' => false,
                'message' => 'Không tìm thấy mã nhân viên nào hợp lệ để thêm.'
            ], 422);
        }

        try {
            $successCount = 0;
            $post = Post::find($request->post_id);
            $maxReportId = Report::max('id');
            $amount = (float) $request->actual_costs;
            $days = (float) $request->days;
            $rate = $request->filled('rate') ? $request->rate : ($post->rate ?? 0);
            $approved = $request->has('approved') ? 1 : 0;
            $authId = Auth::id();

            foreach ($validItems as $item) {
                $user = $item['user'];
                Task::create([
                    'user_id'        => $authId ?? $user->id,
                    'user'           => $user->id,
                    'report_id'      => $maxReportId,
                    'days'           => $days,
                    'department_lv1' => $user->department_lv1,
                    'department_lv2' => $user->department_lv2,
                    'department_id'  => $user->department_id,
                    'rate'           => $rate,
                    'post_id'        => $post->id,
                    'channel_id'     => $request->channel_id,
                    'expected_costs' => $amount,
                    'actual_costs'   => 0,
                    'content'        => $noteContent,
                    'active'         => 1,
                    'approved'       => $approved,
                    'extra_money'    => 0,
                    'refund_money'   => 0
                ]);
                $successCount++;
            }

            $message = "Thêm thành công $successCount dòng.";
            if (count($uniqueNotFound) > 0) {
                $message .= " Không tìm thấy " . count($uniqueNotFound) . " mã nhân viên: " . implode(', ', $uniqueNotFound);
            }

            return response()->json([
                'ok' => true,
                'message' => $message,
                'success_count' => $successCount,
                'not_found_codes' => $uniqueNotFound
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Lỗi khi lưu dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }
}
