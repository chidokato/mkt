<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Models\User;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $locale = Session::get('locale');
        // $category = CategoryTranslation::where('locale', $locale)->orderBy('category_id', 'DESC')->get();
        // return view('category.index', compact('category'));
        return view('admin.login.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    $this->validate($request, [
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $remember = $request->boolean('remember');

    $user = User::where('email', $request->input('email'))
                ->orWhere('secondary_email', $request->input('email'))
                ->first();

    if ($user && Auth::attempt(['email' => $user->email, 'password' => $request->input('password')], $remember)) {

        $permission = (int) Auth::user()->permission;

        // permission < 6 => vào admin
        if ($permission < 6) {
            return redirect()->route('admin'); // đổi route theo dashboard admin của bạn
        }

        // permission = 6 => ra trang người dùng
        return redirect()->route('account.dashboard.main'); // đổi route theo trang user của bạn
    }

    Session::flash('error', 'Email hoặc Password không đúng');
    return redirect()->back()->withInput($request->only('email', 'remember'));
}


    public function showRegisterForm()
    {
        $departments = \App\Models\Department::all();
        $departmentTree = $this->buildDepartmentTree($departments);
        return view('account.auth.register', compact('departmentTree'));
    }

    private function buildDepartmentTree($departments, $parentId = 0, $prefix = '', $level = 1)
    {
        $tree = [];
        foreach ($departments as $department) {
            if ((int) ($department->parent ?? 0) === (int) $parentId) {
                $department->name_with_prefix = $prefix . $department->name;
                $department->level = $level;
                $tree[] = $department;
                $tree = array_merge($tree, $this->buildDepartmentTree($departments, $department->id, $prefix . '-- ', $level + 1));
            }
        }
        return $tree;
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'employee_code' => 'required|unique:users,employee_code',
            'yourname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'password' => 'required|min:6',
            'passwordagain' => 'required|same:password',
        ], [
            'employee_code.unique' => 'Mã nhân viên đã tồn tại trong hệ thống.',
            'email.unique' => 'Email đã tồn tại trong hệ thống.',
            'passwordagain.same' => 'Mật khẩu nhập lại không khớp.',
        ]);

        $department = \App\Models\Department::find($request->department_id);
        
        $User = new User();
        $User->employee_code = $request->employee_code;
        $User->yourname = $request->yourname;
        $User->email = $request->email;
        $User->phone = $request->phone;
        $User->password = bcrypt($request->password);
        $User->department_id = $department->id;

        // Extract hierarchy if possible
        $hierarchy = $department->hierarchy_levels ?? null;
        if ($hierarchy) {
            // Find IDs for level 1 and level 2
            $lvl1 = \App\Models\Department::where('name', $hierarchy['level1'])->first();
            $lvl2 = \App\Models\Department::where('name', $hierarchy['level2'])->first();
            $User->department_lv1 = $lvl1 ? $lvl1->id : null;
            $User->department_lv2 = $lvl2 ? $lvl2->id : null;
        }

        $User->permission = 6;
        $User->status = 'inactive'; // Cần admin duyệt
        $User->save();

        return redirect()->route('dangnhap')->with('success', 'Đăng ký thành công. Vui lòng chờ Admin duyệt tài khoản trước khi đăng nhập.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // echo "ok";
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('dangnhap');
    }

    public function showForgetPasswordForm()
    {
        return view('account.auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ], [
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không đúng định dạng'
        ]);

        $user = User::where('email', $request->email)
                    ->orWhere('secondary_email', $request->email)
                    ->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy tài khoản với email này.']);
        }

        $otp = rand(100000, 999999);
        Session::put('forgot_password_otp', $otp);
        Session::put('forgot_password_email', $request->email);

        \Illuminate\Support\Facades\Mail::raw("Mã OTP khôi phục mật khẩu của bạn là: $otp", function($message) use ($request) {
            $message->to($request->email)->subject('Mã OTP khôi phục mật khẩu');
        });

        return response()->json([
            'status' => true,
            'step' => 'otp',
            'message' => 'Vui lòng kiểm tra email để lấy mã OTP.'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $otp = $request->input('otp');
        
        if (!$otp || Session::get('forgot_password_otp') != $otp) {
            return response()->json(['status' => false, 'message' => 'Mã OTP không đúng.']);
        }

        return response()->json([
            'status' => true,
            'step' => 'reset',
            'message' => 'Mã OTP chính xác. Vui lòng đặt mật khẩu mới.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password'
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password_confirmation.same' => 'Xác nhận mật khẩu không khớp.'
        ]);

        $email = Session::get('forgot_password_email');
        if (!$email) {
            return response()->json(['status' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại từ đầu.']);
        }

        $user = User::where('email', $email)->orWhere('secondary_email', $email)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Lỗi xác định tài khoản.']);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        Session::forget('forgot_password_otp');
        Session::forget('forgot_password_email');

        return response()->json([
            'status' => true,
            'message' => 'Đặt lại mật khẩu thành công!'
        ]);
    }
}
