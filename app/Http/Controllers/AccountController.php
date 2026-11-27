<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use App\Helpers\TreeHelper;
use App\Models\Setting;
use App\Models\Department;
use App\Models\Menu;
use App\Models\User;
use App\Models\Post;
use App\Models\Channel;
use App\Models\Task;
use App\Models\Report;

class AccountController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(Auth::id());
        $statisticalYear = (int) date('Y');
        $currentMonth = (int) date('n');
        $selectedMonthInput = request()->input('month');
        $selectedMonth = is_numeric($selectedMonthInput) ? (int) $selectedMonthInput : null;

        if ($selectedMonth !== null && ($selectedMonth < 1 || $selectedMonth > $currentMonth)) {
            $selectedMonth = null;
        }

        $monthStart = $selectedMonth === null
            ? sprintf('%d-01-01', $statisticalYear)
            : sprintf('%d-%02d-01', $statisticalYear, $selectedMonth);
        $monthEnd = $selectedMonth === null
            ? date('Y-m-t', strtotime(sprintf('%d-%02d-01', $statisticalYear, $currentMonth)))
            : date('Y-m-t', strtotime($monthStart));
        $monthOptions = array_reverse(array_slice([
            1 => 'Tháng 1',
            2 => 'Tháng 2',
            3 => 'Tháng 3',
            4 => 'Tháng 4',
            5 => 'Tháng 5',
            6 => 'Tháng 6',
            7 => 'Tháng 7',
            8 => 'Tháng 8',
            9 => 'Tháng 9',
            10 => 'Tháng 10',
            11 => 'Tháng 11',
            12 => 'Tháng 12',
        ], 0, $currentMonth, true), true);

        // biá»ƒu Ä‘á»“ theo dá»± Ã¡n
        $projects = Task::query()
            ->leftJoin('posts', 'posts.id', '=', 'tasks.post_id') // láº¥y tÃªn dá»± Ã¡n
            ->selectRaw("
                tasks.post_id,
                COALESCE(posts.name, tasks.post_id) AS project_name,
                SUM(
                    CAST(
                        REPLACE(REPLACE(COALESCE(tasks.expected_costs,'0'), '.', ''), ',', '')
                    AS DECIMAL(15,2))
                ) AS total_expected,

                SUM(
                    CAST(
                        REPLACE(REPLACE(COALESCE(tasks.actual_costs,'0'), '.', ''), ',', '')
                    AS DECIMAL(15,2))
                ) AS total_actual
            ")
            ->whereNotNull('tasks.post_id')
            ->where('tasks.post_id', '!=', '')
            ->groupBy('tasks.post_id', 'project_name')
            ->orderByDesc('total_expected')
            ->limit(15)
            ->get();
        $chartLabels  = $projects->pluck('project_name')->values()->all();
        $dataExpected = $projects->pluck('total_expected')->map(fn($v) => (float)$v)->values()->all();
        $dataActual   = $projects->pluck('total_actual')->map(fn($v) => (float)$v)->values()->all();

        // Biểu đồ chi phí các tháng trong năm (chia 2 loại: Chi phí Sale trả & Chi phí Công ty chi)
        $monthlyTaskCosts = Task::query()
            ->join('reports', 'reports.id', '=', 'tasks.report_id')
            ->whereYear('reports.time_start', $statisticalYear)
            ->selectRaw('
                MONTH(reports.time_start) as month,
                COALESCE(SUM(tasks.actual_costs), 0) as total_actual,
                COALESCE(SUM(
                    CAST(REPLACE(REPLACE(COALESCE(tasks.expected_costs, "0"), ".", ""), ",", "") AS DECIMAL(15,2))
                    * COALESCE(tasks.days, 0)
                    * (1 - COALESCE(tasks.rate, 0) / 100)
                ), 0) as total_paid,
                COALESCE(SUM(tasks.extra_money), 0) as total_extra,
                COALESCE(SUM(tasks.refund_money), 0) as total_refund
            ')
            ->groupByRaw('MONTH(reports.time_start)')
            ->get()
            ->keyBy('month');

        $chartMonthlyLabels = [];
        $chartMonthlyActual = [];
        $chartMonthlySale = [];
        $chartMonthlyCompany = [];

        for ($m = 1; $m <= 12; $m++) {
            $chartMonthlyLabels[] = 'Tháng ' . $m;
            $row = $monthlyTaskCosts->get($m);
            if ($row) {
                $actual = (float) $row->total_actual;
                $rawSale = (float) $row->total_paid + (float) $row->total_extra - (float) $row->total_refund;
                if ($actual > 0) {
                    if ($actual >= $rawSale) {
                        $sale = max(0, $rawSale);
                        $company = $actual - $sale;
                    } else {
                        $sale = $actual;
                        $company = 0;
                    }
                } else {
                    $sale = 0;
                    $company = 0;
                }
            } else {
                $actual = 0;
                $sale = 0;
                $company = 0;
            }
            $chartMonthlyActual[] = $actual;
            $chartMonthlySale[] = $sale;
            $chartMonthlyCompany[] = $company;
        }

        $reportIdsInYear = Report::query()
            ->whereDate('time_start', '<=', $monthEnd)
            ->whereDate('time_end', '>=', $monthStart)
            ->pluck('id');

        $baseTaskQuery = Task::query()
            ->leftJoin('posts', 'posts.id', '=', 'tasks.post_id')
            ->whereIn('tasks.report_id', $reportIdsInYear);

        $baseDepartmentQuery = Task::query()
            ->leftJoin('departments as current_department', 'current_department.id', '=', 'tasks.department_id')
            ->leftJoin('departments as parent_department', 'parent_department.id', '=', 'current_department.parent')
            ->whereIn('tasks.report_id', $reportIdsInYear);

        $baseCompanyQuery = Task::query()
            ->leftJoin('departments as company_department', 'company_department.id', '=', 'tasks.department_lv1')
            ->whereIn('tasks.report_id', $reportIdsInYear);

        $baseFloorQuery = Task::query()
            ->leftJoin('departments as floor_department', 'floor_department.id', '=', 'tasks.department_lv2')
            ->whereIn('tasks.report_id', $reportIdsInYear);

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
            ->selectRaw('COALESCE(SUM(tasks.actual_costs), 0) as total_actual_costs')
            ->groupBy('tasks.post_id', 'posts.name')
            ->havingRaw('COALESCE(SUM(tasks.actual_costs), 0) > 0')
            ->orderByDesc('total_actual_costs')
            ->get();

        $projectTotalActualCosts = (float) $projectSummaries->sum('total_actual_costs');

        $departmentSummaries = (clone $baseDepartmentQuery)
            ->selectRaw('tasks.department_id')
            ->selectRaw('COALESCE(current_department.name, "Khong xac dinh") as department_name')
            ->selectRaw('COALESCE(parent_department.name, "") as parent_department_name')
            ->selectRaw('COALESCE(SUM(tasks.actual_costs), 0) as total_actual_costs')
            ->groupBy('tasks.department_id', 'current_department.name', 'parent_department.name')
            ->havingRaw('COALESCE(SUM(tasks.actual_costs), 0) > 0')
            ->orderByDesc('total_actual_costs')
            ->get();

        $departmentTotalActualCosts = (float) $departmentSummaries->sum('total_actual_costs');

        $companySummaries = (clone $baseCompanyQuery)
            ->selectRaw('tasks.department_lv1')
            ->selectRaw('COALESCE(company_department.name, "Khong xac dinh") as company_name')
            ->selectRaw('COALESCE(SUM(tasks.actual_costs), 0) as total_actual_costs')
            ->groupBy('tasks.department_lv1', 'company_department.name')
            ->havingRaw('COALESCE(SUM(tasks.actual_costs), 0) > 0')
            ->orderByDesc('total_actual_costs')
            ->get();

        $companyTotalActualCosts = (float) $companySummaries->sum('total_actual_costs');
        $companyChartLabels = $companySummaries->pluck('company_name')->values()->all();
        $companyChartData = $companySummaries->pluck('total_actual_costs')->map(fn($v) => (float)$v)->values()->all();
        $companyColors = ['#377dff', '#00c9a7', '#fd7e14', '#de4437', '#8c57ff', '#00d2d3', '#20c997'];

        $floorSummaries = (clone $baseFloorQuery)
            ->selectRaw('tasks.department_lv2')
            ->selectRaw('COALESCE(floor_department.name, "Khong xac dinh") as floor_name')
            ->selectRaw('COALESCE(SUM(tasks.actual_costs), 0) as total_actual_costs')
            ->groupBy('tasks.department_lv2', 'floor_department.name')
            ->havingRaw('COALESCE(SUM(tasks.actual_costs), 0) > 0')
            ->orderByDesc('total_actual_costs')
            ->get();

        $floorTotalActualCosts = (float) $floorSummaries->sum('total_actual_costs');
        $floorChartLabels = $floorSummaries->pluck('floor_name')->values()->all();
        $floorChartData = $floorSummaries->pluck('total_actual_costs')->map(fn($v) => (float)$v)->values()->all();
        $floorColors = [
            '#377dff', '#00c9a7', '#fd7e14', '#de4437', '#8c57ff',
            '#00d2d3', '#ffc107', '#20c997', '#e83e8c', '#6c757d',
            '#4b6584', '#10ac84', '#ee5253', '#0abde3', '#ff9f43',
            '#5f27cd', '#54a0ff', '#1dd1a1', '#f368e0', '#222f3e'
        ];

        return view('account.dashboard.main', compact(
            'user',
            'chartLabels', 'dataExpected', 'dataActual',
            'chartMonthlyLabels', 'chartMonthlyActual', 'chartMonthlySale', 'chartMonthlyCompany',
            'companyChartLabels', 'companyChartData',
            'companyColors',
            'statisticalYear',
            'selectedMonth',
            'monthOptions',
            'summary',
            'projectSummaries',
            'projectTotalActualCosts',
            'departmentSummaries',
            'departmentTotalActualCosts',
            'companySummaries',
            'companyTotalActualCosts',
            'floorSummaries',
            'floorTotalActualCosts',
            'floorChartLabels',
            'floorChartData',
            'floorColors',
        ));
    }

    public function dangnhap()
    {
        return view('account.auth.login');
    }

    public function postDangnhap(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $user = Auth::user();

            if ($user->permission < 6) {
                return redirect()->route('admin');
            }

            if ((int) $user->permission === 6) {
                if ($user->status === 'active') {
                    return redirect()->route('account.profile.edit');
                }

                Auth::logout();

                return redirect()->route('dangnhap')->with(
                    'center_warning',
                    'Bạn đã kết nối vào hệ thống thành công. Do hệ thống chỉ lưu hành nội bộ. Bạn cần liên hệ Admin để cấp quyền truy cập cao hơn ! Admin: 0977572947'
                );
            }

            Auth::logout();
            return redirect()->route('dangnhap')->with('error', 'Tài khoản không hợp lệ');
        }

        return back()->with('error', 'Email hoặc mật khẩu không đúng');
    }


    public function edit()
    {
        $user = User::find(Auth::id());
        $departments = Department::with('children')->get();
        $departmentOptions = \App\Helpers\TreeHelper_disabled::buildDepartmentOptions(
            $departments,
            parent: 0,
            prefix: '',
            selectedId: $user->department_id
        );
        return view('account.profile.edit', compact('user', 'departmentOptions'));
    }

    public function update(Request $request)
    {
        $data = $request->all();

        $request->validate([
            'yourname'   => 'required',
            'phone'      => 'nullable',
            'address'    => 'nullable',
        ]);

        $user = User::find(Auth::id());
        if (isset($data['department_id'])) {
            // Láº¥y department theo form gá»­i lÃªn (KHÃ”NG pháº£i theo user cÅ©)
            $deptLv3 = Department::find($data['department_id']);
            $deptLv2 = $deptLv3?->parentDepartment;
            $deptLv1 = $deptLv2?->parentDepartment;

            // Cáº­p nháº­t user
            $user->update([
                'yourname'        => $data['yourname'],
                'phone'           => $data['phone'],
                'address'         => $data['address'],
                'department_id'   => $data['department_id'],      // LV3
                'employee_code'   => $data['employee_code'] ?? null,
                'department_lv1'  => $deptLv1?->id,               // ID
                'department_lv2'  => $deptLv2?->id,               // ID
            ]);
        }else{
            // Cáº­p nháº­t user
            $user->update([
                'yourname'        => $data['yourname'],
                'phone'           => $data['phone'],
                'address'         => $data['address'],
                'employee_code'   => $data['employee_code'] ?? null,
            ]);
        }
        

        return redirect()->back()->with('success', 'Thành công!');
    }

    public function changePassword(Request $request)
    {
        $user = User::find(Auth::id());
        $rules = [
            'newPassword' => 'required|min:6',
            'confirmNewPassword' => 'required|same:newPassword',
        ];

        if ($user->password) {
            $rules['currentPassword'] = 'required';
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, [
            'newPassword.required' => 'Vui lòng nhập mật khẩu mới.',
            'newPassword.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'confirmNewPassword.required' => 'Vui lòng nhập lại mật khẩu mới.',
            'confirmNewPassword.same' => 'Mật khẩu nhập lại không khớp.',
            'currentPassword.required' => 'Vui lòng nhập mật khẩu hiện tại.'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        if ($user->password && !\Illuminate\Support\Facades\Hash::check($request->currentPassword, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->newPassword);
        $user->save();

        return response()->json(['status' => true, 'message' => 'Đổi mật khẩu thành công!']);
    }

    public function updateSecondaryEmail(Request $request)
    {
        $user = User::find(Auth::id());
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'secondary_email' => 'nullable|email|unique:users,secondary_email,' . $user->id,
        ], [
            'secondary_email.email' => 'Định dạng email không hợp lệ.',
            'secondary_email.unique' => 'Email phụ này đã được sử dụng bởi một tài khoản khác.'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        if ($request->filled('secondary_email')) {
            if ($request->secondary_email === $user->email) {
                return response()->json(['status' => false, 'message' => 'Email phụ không được trùng với email chính.']);
            }

            $existsInPrimary = User::where('email', $request->secondary_email)->exists();
            if ($existsInPrimary) {
                return response()->json(['status' => false, 'message' => 'Email này đã tồn tại trong hệ thống.']);
            }

            // Generate OTP
            $otp = rand(100000, 999999);
            Session::put('secondary_email_otp', $otp);
            Session::put('secondary_email_pending', $request->secondary_email);

            // Send Email
            \Illuminate\Support\Facades\Mail::raw("Mã OTP xác nhận email phụ của bạn là: $otp", function($message) use ($request) {
                $message->to($request->secondary_email)->subject('Mã OTP xác nhận Email Phụ');
            });

            return response()->json([
                'status' => true, 
                'step' => 'otp', 
                'message' => 'Vui lòng kiểm tra email để lấy mã OTP.'
            ]);
        }

        // If user is clearing the secondary email (empty string)
        $user->secondary_email = null;
        $user->save();

        return response()->json(['status' => true, 'message' => 'Đã xóa email phụ thành công!']);
    }

    public function verifySecondaryEmailOtp(Request $request)
    {
        $user = User::find(Auth::id());
        $otp = $request->input('otp');

        if (!$otp || Session::get('secondary_email_otp') != $otp) {
            return response()->json(['status' => false, 'message' => 'Mã OTP không đúng.']);
        }

        $pendingEmail = Session::get('secondary_email_pending');
        if (!$pendingEmail) {
            return response()->json(['status' => false, 'message' => 'Yêu cầu đã hết hạn, vui lòng thử lại.']);
        }

        $user->secondary_email = $pendingEmail;
        $user->save();

        Session::forget('secondary_email_otp');
        Session::forget('secondary_email_pending');

        return response()->json(['status' => true, 'message' => 'Xác nhận và lưu email phụ thành công!']);
    }

    public function mktregister()
    {
        $sumPrice = Task::where('extra_money', '>', 0)->where('user', Auth::id())->where('settled', 0)->sum('extra_money');
        $kpi = (float) str_replace('%', '', Auth::user()->kpi ?? '0');

        if (Auth::User()->department_id == null) {
            return redirect()->route('account.profile.edit')->with('center_warning', 'Cần cập nhật thông tin cá nhân trước khi đăng ký marketing');
        } elseif (!Auth::user()->allow_marketing) {
            return redirect()->route('tasks.actualcosts')->with('center_warning', 'KPI của bạn phải đạt từ 50% trở lên để được đăng ký Marketing');
        } else {
            $groupIds = Department::where('parent', Auth::user()->Department->parent)->pluck('id')->toArray();
            $r = Report::where('active', 1)->count();
            if ($r > 0 && $sumPrice <= 0) {
                $users = User::where('department_lv2', Auth::User()->department_lv2)->whereNotNull('department_lv2')->where('department_lv2', '!=', '')->get();
                $posts = Post::where('sort_by', 'Product')->where('rate', '!=', null)->orderBy('name', 'asc')->get();
                $channels = Channel::where('parent', '!=', 0)->get();
                $reports = Report::where('active', 1)->orderBy('id', 'desc')->get();
                return view('account.marketing.register', compact(
                    'users',
                    'channels',
                    'posts',
                    'reports',
                    'groupIds',
                ));
            }else{
                return redirect()->route('tasks.actualcosts')->with('center_warning', 'Không có kỳ đăng ký nào đang mở hoặc bạn đang nợ tiền MKT');
            }
        }
        
    }

    public function storeTask(Request $request)
    {
        $data = $request->all();

        // Láº·p qua tá»«ng dÃ²ng trong form
        foreach ($data['post_id'] as $key => $postId) {
            $post = Post::find($postId);
            if (!$post) continue;

            $deptLv3 = Department::find($data['department_id'][$key]);
            if (!$deptLv3) continue;

            $deptLv2 = $deptLv3->parentDepartment;   // OK
            $deptLv1 = $deptLv2?->parentDepartment;  // OK

            Task::create([
                'user_id' => Auth::id(),
                'user' => $data['user_id'][$key] ?? null,
                'post_id' => $postId,
                'channel_id' => $data['channel_id'][$key] ?? null,
                'rate' => $post->rate ?? null,
                'days' => $data['days'][$key] ?? null,
                'approved' => 0,
                
                'department_id' => $deptLv3->id,              // lv3
                'department_lv2' => $deptLv2?->id,            // lv2
                'department_lv1' => $deptLv1?->id,            // lv1

                'content' => $data['content'][$key] ?? null,
                'expected_costs' => isset($data['expected_costs'][$key]) 
                    ? str_replace(['.', ' Ä‘'], '', $data['expected_costs'][$key]) 
                    : 0,
                'report_id' => $data['report_id'][$key] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Đã lưu tác vụ thành công!');
    }

    public function mktlist()
    {
        if (Auth::User()->department_id == null) {
            return redirect()->route('account.profile.edit')->with('center_warning', 'Cần cập nhật thông tin cá nhân trước khi đăng ký marketing');
        }else{
            $posts = Post::where('sort_by', 'Product')->orderBy('name', 'asc')->get();
            $channels = Channel::all();
            return view('account.marketing.list', compact(
                'channels',
                'posts'
            ));
        }
        
    }

    public function stats()
    {
        $tasks = auth()->user()->tasks()->with(['Post'])->get();
        $total_expected = 0;
        $total_pay = 0;

        foreach ($tasks as $val) {
            $expected = $val->report->days * $val->expected_costs;
            $pay = $val->report->days * $val->expected_costs * (1 - ($val->rate ?? 0) / 100);

            $total_expected += $expected;
            $total_pay += $pay;
        }
        return view('account.partials.stats', compact('tasks', 'total_expected', 'total_pay'));
    }


    public function delete($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['status' => false, 'message' => 'Task không tồn tại']);
        }

        $task->delete();

        $user = Auth::user();

        // Láº¥y report Ä‘ang xem
        $report = Report::find($task->report_id); // hoáº·c theo cÃ¡ch báº¡n Ä‘ang láº¥y

        // $tasks = Task::where('department_id', $user->department_id)->where('report_id', $report->id)->get();
        $tasks = $report->Task()->where('user', Auth::id())->get();

        $total_expected = 0;
        $total_pay = 0;

        foreach ($tasks as $val) {
            $expected = $report->days * $val->expected_costs;
            $pay = $report->days * $val->expected_costs * (1 - ($val->rate ?? 0) / 100);

            $total_expected += $expected;
            $total_pay += $pay;
        }

        $total_project = $tasks->pluck('post_id')->unique()->count();

        return response()->json([
            'status' => true,
            'message' => 'Xóa thành công',
            'stats' => [
                'total_project' => $total_project,
                'total_expected' => number_format($total_expected, 0, ',', '.'),
                'total_pay' => number_format($total_pay, 0, ',', '.'),
            ]
        ]);
    }

    public function opened()
    {
        $user = User::find(Auth::id());
        
        return view('account.guide.index', compact('user'));
    }

    public function ssoLearning()
    {
        return $this->redirectToSso($this->getSsoPayload(), 'learning_url', 'LEARNING_SSO_URL', 'https://learning.indochinerealestate.vn/sso-login');
    }

    public function ssoIzi()
    {
        $setting = Setting::find(1);
        abort_unless($setting && $setting->izi_url, 404, 'Tính năng đang được hoàn thiện. Vui lòng quay lại sau.');

        return $this->redirectToSso($this->getSsoPayload(), 'izi_url', 'IZI_SSO_URL');
    }

    private function getSsoPayload()
    {
        $user = Auth::user();

        return [
            'id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'name' => $user->name ?? $user->yourname,
            'avatar' => $user->avatar,
            'employee_code' => $user->employee_code,
            'timestamp' => time(),
        ];
    }

    private function redirectToSso(array $payload, string $settingField, string $environmentField, ?string $defaultUrl = null)
    {
        $token = \Illuminate\Support\Facades\Crypt::encryptString(json_encode($payload));
        $setting = Setting::find(1);
        $targetUrl = trim((string) ($setting->{$settingField} ?? '')) ?: env($environmentField, $defaultUrl);

        abort_unless($targetUrl, 404, 'Chưa cấu hình liên kết IZI.');

        $separator = strpos($targetUrl, '?') === false ? '?' : '&';

        return redirect()->away($targetUrl . $separator . 'token=' . urlencode($token));
    }
}
