<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Helpers\TreeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    public function form()
    {
        $user = auth()->user();
        $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id], [
            'balance' => 0,
            'held_balance' => 0,
        ]);

        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('account.wallet.withdraw', compact('wallet', 'withdrawals', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'bank_name' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'note' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();

        try {
            DB::transaction(function () use ($request, $user) {
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

                if (!$wallet) {
                    $wallet = Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                        'held_balance' => 0,
                    ]);
                }

                $available = (float) ($wallet->balance ?? 0);
                $amount = (float) $request->amount;

                if ($amount > $available) {
                    throw new \RuntimeException('Số tiền rút không được vượt quá số dư ví chính.');
                }

                Withdrawal::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'bank_name' => $request->bank_name,
                    'bank_account_name' => $request->bank_account_name,
                    'bank_account_number' => $request->bank_account_number,
                    'note' => $request->note,
                    'transaction_code' => 'RUT' . $user->id . now()->format('YmdHis') . Str::upper(Str::random(4)),
                    'status' => 'pending',
                ]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('wallet.withdraw.form')
            ->with('success', 'Đã tạo lệnh rút tiền. Vui lòng chờ admin xử lý.');
    }

    public function index(Request $request)
    {
        $this->authorizeAdminWithdrawal();

        $query = Withdrawal::with(['user.department', 'histories.admin', 'approver'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('yourname')) {
            $keyword = trim($request->yourname);
            $query->whereHas('user', function ($q) use ($keyword) {
                $q->where('yourname', 'like', "%{$keyword}%")
                    ->orWhere('employee_code', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('department_id')) {
            $ids = Department::getChildIds((int) $request->department_id);
            $query->whereHas('user', function ($q) use ($ids) {
                $q->whereIn('department_id', $ids);
            });
        }

        $withdrawals = $query->paginate(100)->withQueryString();
        $sumAmount = $withdrawals->sum(fn ($item) => (float) ($item->amount ?? 0));

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

        return view('account.withdrawal.index', compact('withdrawals', 'departmentOptions', 'sumAmount'));
    }

    public function process(Request $request, Withdrawal $withdrawal)
    {
        $this->authorizeAdminWithdrawal();

        $request->validate([
            'action' => 'required|in:approve,reject',
            'note' => 'nullable|string|max:1000',
            'transfer_proof' => 'required_if:action,approve|nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        try {
            DB::transaction(function () use ($request, $withdrawal) {
                $withdrawal = Withdrawal::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();

                if ($withdrawal->status === 'approved' && $request->action === 'approve') {
                    return;
                }

                $wallet = Wallet::where('user_id', $withdrawal->user_id)->lockForUpdate()->firstOrFail();

                if ($request->action === 'approve') {
                    if ($withdrawal->status !== 'pending') {
                        throw new \RuntimeException('Lệnh rút tiền này không còn ở trạng thái chờ xử lý.');
                    }

                    if ((float) $wallet->balance < (float) $withdrawal->amount) {
                        throw new \RuntimeException('Số dư ví hiện tại không đủ để xác nhận lệnh rút này.');
                    }

                    $proofPath = $withdrawal->transfer_proof;
                    if ($request->hasFile('transfer_proof')) {
                        $file = $request->file('transfer_proof');
                        $filename = 'withdrawal_' . $withdrawal->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $proofPath = $file->storeAs('withdrawals', $filename, 'public');
                    }

                    $balanceBefore = (string) $wallet->balance;
                    $heldBefore = (string) ($wallet->held_balance ?? '0.00');

                    $wallet->balance = bcsub((string) $wallet->balance, (string) $withdrawal->amount, 2);
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'ref_type' => 'Withdrawal',
                        'ref_id' => $withdrawal->id,
                        'type' => 'withdraw',
                        'amount' => $withdrawal->amount,
                        'description' => 'Rút tiền về ngân hàng #' . $withdrawal->id,
                        'idempotency_key' => 'withdrawal:' . $withdrawal->id . ':approve',
                        'meta' => [
                            'bank_name' => $withdrawal->bank_name,
                            'bank_account_name' => $withdrawal->bank_account_name,
                            'bank_account_number' => $withdrawal->bank_account_number,
                            'note' => $request->note,
                        ],
                        'balance_before' => $balanceBefore,
                        'balance_after' => (string) $wallet->balance,
                        'held_before' => $heldBefore,
                        'held_after' => (string) ($wallet->held_balance ?? '0.00'),
                    ]);

                    $withdrawal->update([
                        'status' => 'approved',
                        'transfer_proof' => $proofPath,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'note' => $request->note,
                    ]);
                }

                if ($request->action === 'reject') {
                    if ($withdrawal->status === 'approved') {
                        throw new \RuntimeException('Lệnh rút tiền đã được xác nhận chuyển tiền, không thể từ chối.');
                    }

                    $withdrawal->update([
                        'status' => 'rejected',
                        'note' => $request->note,
                    ]);
                }

                $withdrawal->histories()->create([
                    'admin_id' => auth()->id(),
                    'action' => $request->action,
                    'note' => $request->note,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Cập nhật lệnh rút tiền thành công.');
    }

    private function authorizeAdminWithdrawal(): void
    {
        abort_unless(auth()->check() && (int) auth()->user()->rank === 1, 403);
    }
}
