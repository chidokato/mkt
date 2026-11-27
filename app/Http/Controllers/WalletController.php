<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\WalletTransaction;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Task;

use App\Models\Department;
use App\Helpers\TreeHelper;

class WalletController extends Controller
{
    public function wallets(Request $request)
    {
        $query = Wallet::with(['user.department'])->latest();

        // 1) Tìm theo mã/tên
        if ($request->filled('key')) {
            $key = trim($request->key);

            $query->whereHas('user', function ($q) use ($key) {
                $q->where('employee_code', 'like', "%{$key}%")
                  ->orWhere('yourname', 'like', "%{$key}%");
            });
        }

        // 2) Lọc theo nhóm/phòng (đệ quy cả con + cháu)
        if ($request->filled('department_id')) {
            $rootId = (int) $request->department_id;

            // dùng hàm bạn đã có trong Department model
            $ids = Department::getChildIds($rootId);

            $query->whereHas('user', function ($q) use ($ids) {
                $q->whereIn('department_id', $ids);
            });
        }

        $wallets = $query->paginate(50)->withQueryString();

        // Build options department đệ quy tại controller
        $departments = Department::orderBy('name')->get(['id', 'name', 'parent']);
        $departmentOptions = TreeHelper::buildOptions(
            $departments,
            0,
            '',
            $request->department_id
        );

        return view('account.wallet.wallets', compact('wallets', 'departmentOptions'));
    }

    public function detail(Request $request, Wallet $wallet)
    {
        $wallet->load(['user.department']);

        $query = WalletTransaction::where('wallet_id', $wallet->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query
            ->orderByDesc('id')
            ->paginate(50, ['*'], 'tx_page')
            ->withQueryString();

        $transactions->getCollection()->transform(function (WalletTransaction $transaction) {
            $transaction->display_meta = $this->resolveTransactionDisplay($transaction);
            $transaction->display_delta = $this->resolveBalanceDelta($transaction);
            $transaction->display_meta_data = $this->resolveTransactionMeta($transaction);

            return $transaction;
        });

        $summary = [
            'total_in' => 0,
            'deposit_total' => 0,
            'transfer_in_total' => 0,
            'transfer_out_total' => 0,
            'spend_total' => 0,
            'withdraw_total' => 0,
        ];

        foreach ($wallet->transactions as $transaction) {
            $category = $this->resolveTransactionCategory($transaction);
            $amount = (float) ($transaction->amount ?? 0);
            $delta = $this->resolveBalanceDelta($transaction);

            if ($delta > 0) {
                $summary['total_in'] += $delta;
            }

            if ($category === 'deposit_money') {
                $summary['deposit_total'] += $amount;
            }

            if ($category === 'transfer_in') {
                $summary['transfer_in_total'] += $amount;
            }

            if ($category === 'recall_debit') {
                $summary['transfer_in_total'] -= $amount;
            }

            if ($category === 'transfer_out') {
                $summary['transfer_out_total'] += $amount;
            }

            if ($category === 'recall_credit') {
                $summary['transfer_out_total'] -= $amount;
            }

            if ($category === 'spend_money') {
                $summary['spend_total'] += $amount;
            }

            if ($category === 'withdraw_money') {
                $summary['withdraw_total'] += $amount;
            }
        }

        $tasks = Task::with(['Report', 'Post', 'Channel'])
            ->where('user', $wallet->user_id)
            ->orderByDesc('id')
            ->paginate(30, ['*'], 'task_page')
            ->withQueryString();

        return view('account.wallet.detail', compact('wallet', 'transactions', 'summary', 'tasks'));
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $wallet = $user->wallet()->firstOrCreate([
            'user_id' => $user->id
        ]);

        $query = WalletTransaction::where('wallet_id', $wallet->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('account.wallet.index', compact('wallet', 'transactions'));
    }

    // public function depositForm()
    // {
    //     $deposits = Deposit::where('user_id', auth()->id())
    //     ->latest()
    //     ->paginate(10);

    //     $user = auth()->user();

    //     $wallet = $user->wallet()->firstOrCreate([
    //         'user_id' => $user->id
    //     ]);

    //     return view('account.wallet.deposit', compact('deposits', 'wallet', 'user'));
    // }


    // public function depositSubmit(Request $request)
    // {
    //     $request->validate([
    //         'amount' => 'required|numeric|min:1000',
    //         'proof_image'      => 'required|image|max:20480', // tối đa 20MB
    //     ]);

    //     $user = Auth::user();

    //     $imagePath = null;
    //     if ($request->hasFile('proof_image')) {
    //         $file = $request->file('proof_image');

    //         $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

    //         $imagePath = $file->storeAs(
    //             'deposits',
    //             $filename,
    //             'public'
    //         );
    //     }

    //     Deposit::create([
    //         'user_id' => auth()->id(),
    //         'amount' => $request->amount,
    //         'proof_image'      => $imagePath,
    //         'status' => 'pending',
    //     ]);

    //     return redirect()
    //         ->route('wallet.deposit.form')
    //         ->with('success', 'Đã gửi yêu cầu nạp tiền. Vui lòng chờ admin duyệt.');
    // }

    private function toCents($value): int
    {
        // nhận "10000", "10000.5", "10000.50"
        $s = trim((string) $value);
        $s = str_replace([',', ' '], '', $s);

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $s)) {
            throw new \InvalidArgumentException('Số tiền không hợp lệ.');
        }

        $parts = explode('.', $s, 2);
        $whole = (int)$parts[0];
        $frac  = $parts[1] ?? '';
        $frac  = str_pad($frac, 2, '0'); // "5" => "50"
        $frac  = substr($frac, 0, 2);

        return $whole * 100 + (int)$frac;
    }

    private function centsToDecimalString(int $cents): string
    {
        $whole = intdiv($cents, 100);
        $frac  = $cents % 100;
        return sprintf('%d.%02d', $whole, $frac);
    }


    public function bulkTransferForm()
    {
        abort_unless(config('wallet.transfers_enabled', false), 403, 'Tính năng chuyển tiền đang tạm dừng.');

        $user = auth()->user();

        // Nếu bạn muốn chỉ rank 1,2 dùng:
        // if (!in_array((int)$user->rank, [1,2], true)) {
        //     abort(403, 'Bạn không có quyền sử dụng chức năng này.');
        // }

        $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id], [
            'balance' => 0,
            'held_balance' => 0,
        ]);

        // Danh sách user để chọn (loại bản thân)
        $users = User::select('id', 'yourname', 'email', 'employee_code')
            ->where('id', '!=', $user->id)
            ->orderBy('email')
            ->limit(500)
            ->get();

        // idempotency_key để chống submit 2 lần
        $idempotencyKey = (string) Str::uuid();

        return view('account.wallet.bulk-transfer', compact('users', 'wallet', 'idempotencyKey'));
    }

    public function bulkTransferSubmit(Request $request)
    {
        abort_unless(config('wallet.transfers_enabled', false), 403, 'Tính năng chuyển tiền đang tạm dừng.');

        $user = auth()->user();

        // Nếu bạn muốn chỉ rank 1,2 dùng:
        // if (!in_array((int)$user->rank, [1,2], true)) {
        //     abort(403, 'Bạn không có quyền sử dụng chức năng này.');
        // }

        $request->validate([
            'mode' => 'required|in:same,custom',
            'recipient_ids' => 'required|array|min:1',
            'recipient_ids.*' => 'integer|distinct|exists:users,id',

            'amount' => 'required_if:mode,same|nullable|numeric|min:1000',
            // custom: amounts[user_id] = xxx
            'amounts' => 'required_if:mode,custom|nullable|array',
            'note' => 'nullable|string|max:255',

            'idempotency_key' => 'required|string|max:120',
        ], [
            'recipient_ids.required' => 'Vui lòng chọn ít nhất 1 người nhận.',
            'amount.required_if' => 'Vui lòng nhập số tiền chuyển.',
            'amount.min' => 'Số tiền tối thiểu là 1.000.',
            'amounts.required_if' => 'Vui lòng nhập số tiền cho từng người.',
            'idempotency_key.required' => 'Thiếu mã chống gửi trùng.',
        ]);

        $mode = $request->mode;
        $note = $request->note;
        $idempotencyKey = $request->idempotency_key;

        // chống gửi trùng (bấm 2 lần / refresh)
        if (WalletTransaction::where('idempotency_key', $idempotencyKey)
            ->orWhere('idempotency_key', 'like', $idempotencyKey.'-%')
            ->exists()
        ) {
            return redirect()->route('wallet.bulk.form')->with('success', 'Giao dịch đã được xử lý trước đó.');
        }


        $recipientIds = array_values(array_unique(array_map('intval', $request->recipient_ids)));

        // không cho chuyển cho chính mình
        $recipientIds = array_values(array_filter($recipientIds, fn($id) => $id !== (int)$user->id));
        if (!$recipientIds) {
            return back()->withErrors(['recipient_ids' => 'Danh sách người nhận không hợp lệ.'])->withInput();
        }

        // Build transfers: [to_user_id => cents]
        $transfers = [];
        try {
            if ($mode === 'same') {
                $amtCents = $this->toCents($request->amount);
                foreach ($recipientIds as $toId) {
                    $transfers[$toId] = $amtCents;
                }
            } else {
                $amounts = $request->amounts ?? [];
                foreach ($recipientIds as $toId) {
                    $raw = $amounts[$toId] ?? null;
                    if ($raw === null || $raw === '' ) continue;

                    $cents = $this->toCents($raw);
                    if ($cents > 0) $transfers[$toId] = $cents;
                }
                if (!$transfers) {
                    return back()->withErrors(['amounts' => 'Bạn chưa nhập số tiền hợp lệ cho người nhận nào.'])->withInput();
                }
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        $totalCents = array_sum($transfers);

        try {
            DB::transaction(function () use ($user, $transfers, $totalCents, $note, $idempotencyKey) {

                // lock ví người gửi
                $fromWallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if (!$fromWallet) {
                    $fromWallet = Wallet::create(['user_id' => $user->id, 'balance' => 0, 'held_balance' => 0]);
                    $fromWallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                }

                $fromBalanceCents = $this->toCents($fromWallet->balance);
                if ($fromBalanceCents < $totalCents) {
                    throw new \RuntimeException('Số dư không đủ để chuyển tiền hàng loạt.');
                }

                // chuẩn bị wallet người nhận (tạo nếu thiếu), rồi lock
                $toUserIds = array_keys($transfers);
                foreach ($toUserIds as $uid) {
                    Wallet::firstOrCreate(['user_id' => $uid], ['balance' => 0, 'held_balance' => 0]);
                }

                $toWallets = Wallet::whereIn('user_id', $toUserIds)->lockForUpdate()->get()->keyBy('user_id');

                // ====== NGƯỜI GỬI: trừ theo từng người + ghi lịch sử chi tiết ======
                $fromStartCents = $this->toCents($fromWallet->balance);

                // Check đủ tiền theo tổng
                if ($fromStartCents < $totalCents) {
                    throw new \RuntimeException('Số dư không đủ để chuyển tiền hàng loạt.');
                }

                $runningCents = $fromStartCents;

                // Tên người nhận để ghi lịch sử đẹp hơn
                $toUserIds = array_keys($transfers);
                $toUsers = \App\Models\User::select('id','yourname')
                    ->whereIn('id', $toUserIds)->get()->keyBy('id');

                foreach ($transfers as $toUserId => $amtCents) {
                    $toName = $toUsers[$toUserId]->yourname ?? ('User#'.$toUserId);

                    $beforeCents = $runningCents;
                    $afterCents  = $runningCents - (int)$amtCents;

                    WalletTransaction::create([
                        'wallet_id' => $fromWallet->id,
                        'ref_type' => 'BulkTransfer',
                        'ref_id' => null,
                        'type' => 'withdraw',
                        'amount' => $this->centsToDecimalString((int)$amtCents),
                        'balance_before' => $this->centsToDecimalString($beforeCents),
                        'balance_after'  => $this->centsToDecimalString($afterCents),
                        'held_before' => $fromWallet->held_balance,
                        'held_after'  => $fromWallet->held_balance,
                        'description' => "Chuyển tiền cho {$toName}",
                        'idempotency_key' => $idempotencyKey.'-out-'.$toUserId,
                        'meta' => json_encode([
                            'batch_key' => $idempotencyKey,
                            'from_user_id' => (int)$user->id,
                            'to_user_id' => (int)$toUserId,
                            'to_name' => $toName,
                            'amount' => $this->centsToDecimalString((int)$amtCents),
                            'note' => $note,
                        ], JSON_UNESCAPED_UNICODE),
                    ]);

                    $runningCents = $afterCents;
                }

                // Save số dư người gửi 1 lần
                $fromWallet->balance = $this->centsToDecimalString($runningCents);
                $fromWallet->save();


                // ✅ update số dư người gửi 1 lần (khỏi save nhiều lần)
                $fromWallet->balance = $this->centsToDecimalString($runningCents);
                $fromWallet->save();


                // Cộng tiền từng người nhận + log deposit
                $fromName = $user->yourname ?? $user->name ?? ('User#'.$user->id);

                foreach ($transfers as $toUserId => $amtCents) {
                    $w = $toWallets[$toUserId];

                    $before = $w->balance;
                    $beforeCents = $this->toCents($before);

                    $afterCents = $beforeCents + (int)$amtCents;
                    $after = $this->centsToDecimalString($afterCents);

                    $w->balance = $after;
                    $w->save();

                    WalletTransaction::create([
                        'wallet_id' => $w->id,
                        'ref_type' => 'BulkTransfer',
                        'ref_id' => null,
                        'type' => 'deposit',
                        'amount' => $this->centsToDecimalString((int)$amtCents),
                        'balance_before' => $before,
                        'balance_after' => $after,
                        'held_before' => $w->held_balance,
                        'held_after' => $w->held_balance,

                        // ✅ người nhận thấy rõ nhận từ ai
                        'description' => $note
                            ? ("Nhận tiền từ {$fromName}: ".$note)
                            : ("Nhận tiền từ {$fromName}"),

                        'idempotency_key' => $idempotencyKey.'-'.$toUserId,

                        // ✅ lưu chi tiết để hiển thị ở lịch sử
                        'meta' => json_encode([
                            'from_user_id' => (int)$user->id,
                            'from_name' => $fromName,
                            'to_user_id' => (int)$toUserId,
                            'amount' => $this->centsToDecimalString((int)$amtCents),
                            'note' => $note,
                        ], JSON_UNESCAPED_UNICODE),
                    ]);
                }

            });

            return redirect()->route('wallet.bulk.form')
                ->with('success', 'Đã chuyển tiền hàng loạt thành công.');

        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function recallTransfer(Request $request, $id)
    {
        $user = auth()->user();

        // Nếu bạn muốn giới hạn rank 1,2:
        // if (!in_array((int)$user->rank, [1,2], true)) abort(403);

        $tx = WalletTransaction::findOrFail($id);

        // Chỉ thu hồi với giao dịch chuyển tiền (người gửi)
        if ($tx->type !== 'withdraw' || $tx->ref_type !== 'BulkTransfer') {
            return back()->withErrors(['error' => 'Giao dịch này không hỗ trợ thu hồi.']);
        }

        // Check đúng ví của người đang đăng nhập
        $myWallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0, 'held_balance' => 0]);
        if ((int)$tx->wallet_id !== (int)$myWallet->id) {
            abort(403, 'Bạn không có quyền thu hồi giao dịch này.');
        }

        // Lấy chi tiết người nhận từ meta
        $meta = $tx->meta ? json_decode($tx->meta, true) : [];
        $toUserId = (int)($meta['to_user_id'] ?? 0);

        if (!$toUserId) {
            return back()->withErrors(['error' => 'Không tìm thấy người nhận trong giao dịch này (meta thiếu to_user_id).']);
        }

        // amount trong DB là decimal(15,2)
        $amountCents = $this->toCents($tx->amount);

        // Chống bấm thu hồi 2 lần
        $recallKeySender = 'recall-'.$tx->id.'-sender';
        $recallKeyReceiver = 'recall-'.$tx->id.'-receiver';
        if (WalletTransaction::whereIn('idempotency_key', [$recallKeySender, $recallKeyReceiver])->exists()) {
            return back()->with('success', 'Giao dịch này đã được thu hồi trước đó.');
        }

        try {
            DB::transaction(function () use ($user, $toUserId, $amountCents, $tx, $recallKeySender, $recallKeyReceiver) {

                // lock ví gửi + ví nhận
                $fromWallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                Wallet::firstOrCreate(['user_id' => $toUserId], ['balance' => 0, 'held_balance' => 0]);
                $toWallet = Wallet::where('user_id', $toUserId)->lockForUpdate()->first();

                // Tiền khả dụng của người nhận = balance - held_balance
                $toBalCents  = $this->toCents($toWallet->balance);
                $toHeldCents = $this->toCents($toWallet->held_balance ?? 0);
                $toAvailable = $toBalCents - $toHeldCents;

                if ($toAvailable < $amountCents) {
                    throw new \RuntimeException('Không thể thu hồi vì người nhận không còn đủ số dư khả dụng.');
                }

                // ---- 1) Trừ tiền ví người nhận ----
                $toBefore = $toWallet->balance;
                $toAfterCents = $toBalCents - $amountCents;
                $toAfter = $this->centsToDecimalString($toAfterCents);

                $toWallet->balance = $toAfter;
                $toWallet->save();

                WalletTransaction::create([
                    'wallet_id' => $toWallet->id,
                    'ref_type' => 'RecallTransfer',
                    'ref_id' => $tx->id,
                    'type' => 'withdraw',
                    'amount' => $this->centsToDecimalString($amountCents),
                    'balance_before' => $toBefore,
                    'balance_after' => $toAfter,
                    'held_before' => $toWallet->held_balance,
                    'held_after' => $toWallet->held_balance,
                    'description' => 'Thu hồi chuyển nhầm (bị trừ)',
                    'idempotency_key' => $recallKeyReceiver,
                    'meta' => json_encode([
                        'from_user_id' => $user->id,
                        'to_user_id' => $toUserId,
                        'origin_transaction_id' => $tx->id,
                        'amount' => $this->centsToDecimalString($amountCents),
                    ], JSON_UNESCAPED_UNICODE),
                ]);

                // ---- 2) Cộng tiền lại ví người gửi ----
                $fromBalCents = $this->toCents($fromWallet->balance);
                $fromBefore = $fromWallet->balance;

                $fromAfterCents = $fromBalCents + $amountCents;
                $fromAfter = $this->centsToDecimalString($fromAfterCents);

                $fromWallet->balance = $fromAfter;
                $fromWallet->save();

                WalletTransaction::create([
                    'wallet_id' => $fromWallet->id,
                    'ref_type' => 'RecallTransfer',
                    'ref_id' => $tx->id,
                    'type' => 'rollback', // hiện UI bạn map rollback là +
                    'amount' => $this->centsToDecimalString($amountCents),
                    'balance_before' => $fromBefore,
                    'balance_after' => $fromAfter,
                    'held_before' => $fromWallet->held_balance,
                    'held_after' => $fromWallet->held_balance,
                    'description' => 'Thu hồi chuyển nhầm (hoàn tiền)',
                    'idempotency_key' => $recallKeySender,
                    'meta' => json_encode([
                        'from_user_id' => $user->id,
                        'to_user_id' => $toUserId,
                        'origin_transaction_id' => $tx->id,
                        'amount' => $this->centsToDecimalString($amountCents),
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            });

            return back()->with('success', 'Thu hồi giao dịch thành công.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function resolveTransactionMeta(WalletTransaction $transaction): array
    {
        $meta = $transaction->meta ?? [];

        if (is_string($meta)) {
            $meta = json_decode($meta, true) ?: [];
        }

        return is_array($meta) ? $meta : [];
    }

    private function resolveTransactionCategory(WalletTransaction $transaction): string
    {
        $meta = $this->resolveTransactionMeta($transaction);
        $description = mb_strtolower((string) ($transaction->description ?? ''), 'UTF-8');
        $refType = (string) ($transaction->ref_type ?? '');

        if ($transaction->type === 'deposit') {
            if ($refType === 'BulkTransfer' || !empty($meta['from_user_id'])) {
                return 'transfer_in';
            }

            if (str_contains($description, 'nạp tiền')) {
                return 'deposit_money';
            }

            return 'other_in';
        }

        if ($transaction->type === 'withdraw') {
            if ($refType === 'RecallTransfer') {
                return 'recall_debit';
            }

            if ($refType === 'BulkTransfer' || !empty($meta['to_user_id'])) {
                return 'transfer_out';
            }

            if ($refType === 'Withdrawal' || str_contains($description, 'rút tiền')) {
                return 'withdraw_money';
            }

            return 'spend_money';
        }

        if ($transaction->type === 'capture') {
            return 'spend_money';
        }

        if ($transaction->type === 'rollback') {
            if ($refType === 'RecallTransfer') {
                return 'recall_credit';
            }

            return 'other_in';
        }

        if ($transaction->type === 'release') {
            return 'other_in';
        }

        return 'other';
    }

    private function resolveTransactionDisplay(WalletTransaction $transaction): array
    {
        $meta = $this->resolveTransactionMeta($transaction);
        $category = $this->resolveTransactionCategory($transaction);

        $map = [
            'deposit_money' => ['Nạp tiền', 'bg-success', 'text-success', '+'],
            'transfer_in' => ['Nhận tiền', 'bg-soft-success text-success', 'text-success', '+'],
            'transfer_out' => ['Chuyển tiền', 'bg-soft-warning text-warning', 'text-warning', '-'],
            'spend_money' => ['Chi tiêu', 'bg-danger', 'text-danger', '-'],
            'withdraw_money' => ['Rút tiền', 'bg-dark', 'text-dark', '-'],
            'other_in' => ['Tiền vào', 'bg-info', 'text-info', '+'],
            'other' => ['Khác', 'bg-secondary', 'text-muted', ''],
        ];

        $display = $map[$category] ?? $map['other'];

        if ($transaction->type === 'hold') {
            return ['Tạm giữ', 'bg-primary', 'text-primary', ''];
        }

        if ($transaction->type === 'release') {
            return ['Hoàn hold', 'bg-info', 'text-info', '+'];
        }

        if ($transaction->type === 'rollback' && $transaction->ref_type === 'RecallTransfer') {
            return ['Thu hồi hoàn tiền', 'bg-warning', 'text-warning', '+'];
        }

        if ($transaction->type === 'withdraw' && $transaction->ref_type === 'RecallTransfer') {
            return ['Thu hồi trừ ví', 'bg-warning', 'text-warning', '-'];
        }

        if ($category === 'transfer_in' && !empty($meta['from_name'])) {
            $display[0] = 'Nhận từ ' . $meta['from_name'];
        }

        if ($category === 'transfer_out' && !empty($meta['to_name'])) {
            $display[0] = 'Chuyển cho ' . $meta['to_name'];
        }

        return $display;
    }

    private function resolveBalanceDelta(WalletTransaction $transaction): float
    {
        if ($transaction->balance_before !== null && $transaction->balance_after !== null) {
            return (float) $transaction->balance_after - (float) $transaction->balance_before;
        }

        $amount = (float) ($transaction->amount ?? 0);
        $category = $this->resolveTransactionCategory($transaction);

        return match ($category) {
            'deposit_money', 'transfer_in', 'other_in', 'recall_credit' => $amount,
            'transfer_out', 'spend_money', 'withdraw_money', 'recall_debit' => -$amount,
            default => 0,
        };
    }

    public function histories(\App\Models\Wallet $wallet)
    {
        $transactions = $wallet->transactions()->latest()->limit(200)->get();

        $html = view('account.wallet._history_rows', compact('transactions'))->render();

        return response()->json([
            'ok' => true,
            'html' => $html,
        ]);
    }

    public function updateHeldBalance(Request $request, Wallet $wallet)
    {
        $request->validate([
            'held_balance' => 'required|numeric|min:0',
        ]);

        $wallet->held_balance = $request->held_balance;
        $wallet->save();

        return response()->json([
            'ok' => true,
            'message' => 'Cập nhật tiền Hold thành công!',
        ]);
    }

    public function updateBalance(Request $request, Wallet $wallet)
    {
        $request->validate([
            'balance' => 'required|numeric|min:0',
        ]);

        $wallet->balance = $request->balance;
        $wallet->save();

        return response()->json([
            'ok' => true,
            'message' => 'Cập nhật Số dư thành công!',
        ]);
    }
}
