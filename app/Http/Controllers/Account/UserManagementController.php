<?php

namespace App\Http\Controllers\Account;

use App\Helpers\TreeHelper;
use App\Helpers\TreeHelper_disabled;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $departments = Department::orderBy('name')->get();
        $departmentOptions = TreeHelper::buildOptions($departments, 0, '', $request->department_id);

        $users = $this->applyUserFilters(
            User::query()->where('permission', '<', 6),
            $request
        )
            ->with(['department', 'departmentlv1', 'departmentlv2'])
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('account.users.index', [
            'pageTitle' => 'Quản lý người dùng',
            'users' => $users,
            'departmentOptions' => $departmentOptions,
            'type' => 'admin',
        ]);
    }

    public function members(Request $request)
    {
        $this->authorizeAccess();

        $departments = Department::orderBy('name')->get();
        $departmentOptions = TreeHelper::buildOptions($departments, 0, '', $request->department_id);

        $users = $this->applyUserFilters(
            User::query()->where('permission', 6),
            $request
        )
            ->with(['department', 'departmentlv1', 'departmentlv2'])
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('account.users.index', [
            'pageTitle' => 'Quản lý thành viên',
            'users' => $users,
            'departmentOptions' => $departmentOptions,
            'type' => 'member',
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeAccess();

        $departments = Department::with('children')->get();
        $departmentOptions = TreeHelper_disabled::buildDepartmentOptions($departments, 0, '');

        return view('account.users.form', [
            'pageTitle' => 'Thêm người dùng',
            'userData' => new User(),
            'departmentOptions' => $departmentOptions,
            'formAction' => route('account.users.store'),
            'formMethod' => 'POST',
            'backRoute' => $request->query('type') === 'member'
                ? route('account.users.members')
                : route('account.users.index'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $this->validateUser($request);
        $department = Department::with('parentDepartment.parentDepartment')->findOrFail($validated['department_id']);

        $user = new User();
        $this->fillUser($user, $validated, $department, true);
        $user->save();

        return redirect($validated['permission'] == 6 ? route('account.users.members') : route('account.users.index'))
            ->with('success', 'Thêm người dùng thành công');
    }

    public function edit(User $user)
    {
        $this->authorizeAccess();

        $departments = Department::with('children')->get();
        $departmentOptions = TreeHelper_disabled::buildDepartmentOptions(
            $departments,
            0,
            '',
            $user->department_id
        );

        return view('account.users.form', [
            'pageTitle' => 'Cập nhật người dùng',
            'userData' => $user,
            'departmentOptions' => $departmentOptions,
            'formAction' => route('account.users.update', $user),
            'formMethod' => 'PUT',
            'backRoute' => (int) $user->permission === 6
                ? route('account.users.members')
                : route('account.users.index'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAccess();

        $validated = $this->validateUser($request, $user);
        $department = Department::with('parentDepartment.parentDepartment')->findOrFail($validated['department_id']);

        $this->fillUser($user, $validated, $department, false);
        $user->save();

        return redirect()->back()->with('success', 'Cập nhật người dùng thành công');
    }

    public function destroy(User $user)
    {
        $this->authorizeAccess();

        if ((int) Auth::id() === (int) $user->id) {
            return redirect()->back()->with('error', 'Không thể xóa tài khoản đang đăng nhập');
        }

        $targetRoute = (int) $user->permission === 6
            ? route('account.users.members')
            : route('account.users.index');

        $user->delete();

        return redirect($targetRoute)->with('success', 'Xóa người dùng thành công');
    }

    public function toggleStatus(User $user, Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $user->status = $validated['status'];
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật trạng thái thành công',
            'user_status' => $user->status,
        ]);
    }

    public function toggleMarketing(User $user, Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'allow_marketing' => 'required|boolean',
        ]);

        $user->allow_marketing = $validated['allow_marketing'];
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật quyền đăng ký marketing thành công',
            'allow_marketing' => $user->allow_marketing,
        ]);
    }

    public function importKpi(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'kpi_file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('kpi_file')->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) <= 1) {
                return redirect()->back()->with('error', 'File dữ liệu trống hoặc không hợp lệ.');
            }

            // Xóa tất cả KPI cũ và reset quyền MKT
            User::query()->update([
                'kpi' => null,
                'allow_marketing' => 0
            ]);

            // Bỏ qua dòng tiêu đề (index 0)
            for ($i = 1; $i < count($rows); $i++) {
                $employeeCode = trim($rows[$i][0] ?? '');
                $kpi = trim($rows[$i][1] ?? '');
                
                // Chuẩn hóa KPI: thay phẩy thành chấm, loại bỏ ký tự không phải số/chấm
                $cleanKpi = str_replace(',', '.', $kpi);
                $cleanKpi = preg_replace('/[^0-9.]/', '', $cleanKpi);
                $kpiValue = (float) $cleanKpi;

                // Nếu file Excel tự động format % thành số thập phân (vd: 0.463 thay vì 46.3%)
                if ($kpiValue > 0 && $kpiValue <= 1 && strpos($kpi, '%') === false) {
                    $kpiValue *= 100;
                }

                $allowMarketing = $kpiValue >= 50 ? 1 : 0;

                if (!empty($employeeCode)) {
                    User::where('employee_code', $employeeCode)->update([
                        'kpi' => $kpi,
                        'allow_marketing' => $allowMarketing
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Cập nhật KPI hàng loạt thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function authorizeAccess(): void
    {
        abort_unless(Auth::check() && (int) Auth::user()->rank === 1, 403);
    }

    private function applyUserFilters($query, Request $request)
    {
        if ($request->filled('key')) {
            $key = trim($request->key);
            $query->where(function ($q) use ($key) {
                $q->where('yourname', 'like', "%{$key}%")
                    ->orWhere('email', 'like', "%{$key}%")
                    ->orWhere('secondary_email', 'like', "%{$key}%")
                    ->orWhere('employee_code', 'like', "%{$key}%")
                    ->orWhere('phone', 'like', "%{$key}%");
            });
        }

        if ($request->filled('department_id')) {
            $departmentId = $request->department_id;
            $query->where(function ($q) use ($departmentId) {
                $q->where('department_lv1', $departmentId)
                    ->orWhere('department_lv2', $departmentId)
                    ->orWhere('department_id', $departmentId);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rank')) {
            $query->where('rank', $request->rank);
        }

        if ($request->filled('allow_marketing')) {
            $query->where('allow_marketing', $request->allow_marketing);
        }

        return $query;
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $userId = $user?->id;

        return $request->validate(
            [
                'employee_code' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users', 'employee_code')->ignore($userId),
                ],
                'yourname' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($userId),
                ],
                'secondary_email' => [
                    'nullable',
                    'email',
                    Rule::unique('users', 'secondary_email')->ignore($userId),
                ],
                'permission' => 'required|integer|in:1,2,3,6',
                'rank' => 'nullable|integer|in:1,2,3',
                'status' => 'required|in:active,inactive',
                'department_id' => 'required|exists:departments,id',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'facebook' => 'nullable|string|max:255',
                'kpi' => 'nullable|string|max:255',
                'password' => $user ? 'nullable|min:6' : 'required|min:6',
                'passwordagain' => $user ? 'nullable|same:password' : 'required|same:password',
            ],
            [
                'employee_code.unique' => 'Mã nhân viên đã tồn tại',
                'email.unique' => 'Email đã tồn tại',
                'secondary_email.unique' => 'Email phụ đã tồn tại',
                'passwordagain.same' => 'Mật khẩu nhập lại không khớp',
            ]
        );
    }

    private function fillUser(User $user, array $validated, Department $department, bool $isCreate): void
    {
        if (!empty($validated['secondary_email']) && $validated['secondary_email'] === $validated['email']) {
            throw ValidationException::withMessages([
                'secondary_email' => 'Email phụ không được trùng với email chính',
            ]);
        }

        $user->employee_code = $validated['employee_code'];
        $user->yourname = $validated['yourname'];
        $user->email = $validated['email'];
        $user->secondary_email = !empty($validated['secondary_email']) ? $validated['secondary_email'] : null;
        $user->permission = $validated['permission'];
        $user->rank = $validated['rank'] ?? null;
        $user->status = $validated['status'];
        $user->phone = $validated['phone'] ?? null;
        $user->address = $validated['address'] ?? null;
        $user->facebook = $validated['facebook'] ?? null;
        $user->kpi = $validated['kpi'] ?? null;
        $user->department_id = $department->id;
        $user->department_lv2 = $department->parentDepartment?->id;
        $user->department_lv1 = $department->parentDepartment?->parentDepartment?->id;

        if ($isCreate || !empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }
    }
}
