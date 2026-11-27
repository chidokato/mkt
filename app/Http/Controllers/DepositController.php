<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Helpers\TreeHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Mail\BulkPersonalMail;

class DepositController extends Controller
{
    /**
     * Danh sách nạp tiền
     */

    public function index(Request $request)
    {
        $query = Deposit::with(['user.department', 'histories.admin'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('yourname')) {
            $kw = trim($request->yourname);
            $query->whereHas('user', function ($q) use ($kw) {
                $q->where('yourname', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('bank')) {
            $query->where('bank_name', $request->bank);
        }

        if ($request->filled('department_id')) {
            $rootId = (int) $request->department_id;
            $ids = Department::getChildIds($rootId);

            $query->whereHas('user', function ($q) use ($ids) {
                $q->whereIn('department_id', $ids);
            });
        }

        if ($request->filled('range')) {
            $range = trim($request->range);

            if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{4})\s*-\s*(\d{1,2}\/\d{1,2}\/\d{4})$/', $range, $m)) {

                $tz = config('app.timezone'); 

                $from = Carbon::createFromFormat('m/d/Y', $m[1], $tz)->startOfDay()->utc();
                $to   = Carbon::createFromFormat('m/d/Y', $m[2], $tz)->endOfDay()->utc();

                $query->whereBetween('created_at', [$from, $to]);
            }
        }

        $sumAmount = (clone $query)->sum('amount');

        $deposits = $query->paginate(100)->withQueryString();

        $departments = Department::orderBy('name')->get(['id', 'name', 'parent']);
        $departmentOptions = TreeHelper::buildOptions(
            $departments,
            0,
            '',
            $request->department_id,
            'id',
            'parent',
            'name'
        );

        return view('account.deposit.index', compact('deposits', 'departmentOptions', 'sumAmount'));
    }

    /**
     * Cập nhật trạng thái: duyệt / từ chối / rollback
     */
    public function updateStatus(Request $request, Deposit $deposit)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'note'   => 'nullable|string|max:1000',
        ]);

        $mailPayload = null;

        DB::transaction(function () use ($request, $deposit, &$mailPayload) {

            $deposit = Deposit::where('id', $deposit->id)
                ->lockForUpdate()
                ->first();

            $wallet = Wallet::firstOrCreate([
                'user_id' => $deposit->user_id
            ]);

            if ($request->action === 'approve') {
                if ($deposit->status !== 'approved') {
                    $wallet->increment('balance', $deposit->amount);

                    $wallet->transactions()->create([
                        'amount' => $deposit->amount,
                        'type' => 'deposit',
                        'description' => 'Duyệt nạp tiền #' . $deposit->id,
                    ]);

                    $deposit->update(['status' => 'approved']);
                }
            }

            if ($request->action === 'reject') {
                if ($deposit->status === 'approved') {
                    if ($wallet->balance < $deposit->amount) {
                        throw new \Exception('Không đủ số dư để rollback');
                    }

                    $wallet->decrement('balance', $deposit->amount);

                    $wallet->transactions()->create([
                        'amount' => -$deposit->amount,
                        'type' => 'rollback',
                        'description' => 'Rollback nạp tiền #' . $deposit->id,
                    ]);
                }

                $deposit->update(['status' => 'rejected']);
            }

            $deposit->histories()->create([
                'admin_id' => auth()->id(),
                'action' => $request->action,
                'note' => $request->note,
            ]);

            $user = $deposit->user()->select('id', 'yourname', 'name', 'email')->first();
            if ($user && !empty($user->email)) {
                $statusText = $request->action === 'approve' ? 'ĐƯỢC DUYỆT' : 'BỊ TỪ CHỐI';
                $subject = $request->action === 'approve'
                    ? 'Thông báo: Lệnh nạp tiền đã được duyệt'
                    : 'Thông báo: Lệnh nạp tiền đã bị từ chối';

                $noteText = trim((string) $request->note);
                $content = "Lệnh nạp tiền của bạn vừa được cập nhật.\n"
                    . "Mã lệnh: " . ($deposit->transaction_code ?: ('#' . $deposit->id)) . "\n"
                    . "Số tiền: " . number_format((float) $deposit->amount, 0, ',', '.') . " VND\n"
                    . "Trạng thái: " . $statusText;

                if ($noteText !== '') {
                    $content .= "\nGhi chú: " . $noteText;
                }

                $mailPayload = [
                    'to' => $user->email,
                    'name' => $user->yourname ?: ($user->name ?: 'Bạn'),
                    'subject' => $subject,
                    'content' => $content,
                ];
            }
        });

        if ($mailPayload) {
            Mail::to($mailPayload['to'])->send(
                new BulkPersonalMail(
                    $mailPayload['name'],
                    $mailPayload['content'],
                    $mailPayload['subject']
                )
            );
        }

        return back()->with('success', 'Cập nhật trạng thái thành công');
    }

    public function updateBankName(Request $request, Deposit $deposit)
    {
        $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
        ]);

        $deposit->bank_name = $request->bank_name;
        $deposit->save();

        return response()->json([
            'ok' => true,
            'bank_name' => $deposit->bank_name,
        ]);
    }

    public function depositForm()
    {
        $user = auth()->user();
        $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id]);

        $activeDeposit = Deposit::where('user_id', $user->id)
            ->where('status', 'pending_upload')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        $deposits = Deposit::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('account.wallet.deposit', compact('deposits','wallet','user','activeDeposit'));
    }

    public function depositCreate(Request $request)
    {
        $request->validate([
            'amount' => ['required','integer','min:1000', function($attr,$value,$fail){
                if ($value % 1000 !== 0) $fail('Số tiền phải là bội số của 1.000');
            }],
        ]);

        $user = auth()->user();

        $active = Deposit::where('user_id', $user->id)
            ->where('status', 'pending_upload')
            ->where('expires_at', '>', now())
            ->latest()->first();

        if ($active) {
            return redirect()
                ->route('wallet.deposit.form')
                ->withErrors(['amount' => 'Bạn đang có 1 lệnh nạp đang chờ upload UNC. Vui lòng hoàn tất hoặc chờ hết hạn.']);
        }

        $bankAccount = "0977572947";
        $beneficiary = "NGUYỄN VĂN TUẤN";
        $bankName    = "VPBANK";

        $deposit = Deposit::create([
            'user_id' => $user->id,
            'amount' => (int)$request->amount,
            'status' => 'pending_upload',
            'expires_at' => now()->addMinutes(10),
            'transaction_code' => 'NAP' . $user->id . now()->format('YmdHis') . Str::upper(Str::random(4)),
            'bank_account' => $bankAccount,
            'beneficiary' => $beneficiary,
            'bank_name' => $bankName,
        ]);

        return redirect()->route('wallet.deposit.form')->with('open_deposit_id', $deposit->id);
    }

    public function depositUploadProof(Request $request, Deposit $deposit)
    {
        abort_unless($deposit->user_id === auth()->id(), 403);



        if (!in_array($deposit->status, ['pending_upload', 'rejected', 'expired'])) {
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => 'Lệnh nạp ở trạng thái không cho phép upload thêm ảnh.']);
            }
            return back()->withErrors(['proof_image' => 'Lệnh nạp ở trạng thái không cho phép upload thêm ảnh.']);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'proof_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }
            return back()->withErrors($validator);
        }

        $file = $request->file('proof_image');
        $filename = ($deposit->code ?? 'deposit') . '_' . time() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('deposits', $filename, 'public');

        $deposit->update([
            'proof_image' => $path,
            'proof_uploaded_at' => now(),
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Đã upload UNC. Vui lòng chờ admin duyệt.']);
        }

        return redirect()
            ->route('wallet.deposit.form')
            ->with('success', 'Đã upload UNC. Vui lòng chờ admin duyệt.');
    }

    public function depositUploadProofAdmin(Request $request, Deposit $deposit)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'proof_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
            }
            return back()->withErrors($validator);
        }

        $file = $request->file('proof_image');
        $filename = ($deposit->code ?? 'deposit') . '_' . time() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('deposits', $filename, 'public');

        $deposit->update([
            'proof_image' => $path,
            'proof_uploaded_at' => now(),
            // Don't change status to pending automatically if admin is doing it on a rejected deposit, 
            // but normally they would want it to be reviewed or just set it.
            // Let's just keep the status as is, or if it's pending_upload, we can change to pending.
            'status' => $deposit->status === 'pending_upload' ? 'pending' : $deposit->status,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Đã upload UNC thành công.']);
        }

        return back()->with('success', 'Đã upload UNC thành công.');
    }

    public function depositExpire(Request $request, Deposit $deposit)
    {
        abort_unless($deposit->user_id === auth()->id(), 403);

        $updated = Deposit::where('id', $deposit->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending_upload')
            ->whereNotNull('expires_at')
            ->whereRaw('expires_at <= NOW()')
            ->update(['status' => 'expired']);

        return response()->json([
            'ok' => true,
            'updated' => $updated,
            'status_now' => Deposit::find($deposit->id)->status,
        ]);
    }
}
