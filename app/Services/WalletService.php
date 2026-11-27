<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class WalletService
{
    // =========================
    // Normalize số (tránh "1.000.000", "1,5")
    // =========================
    private function normalizeNumber($v): string
    {
        $s = (string)($v ?? '0');
        $s = trim($s);
        $s = str_replace([' ', "\u{00A0}"], '', $s);
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^0-9\.\-]/', '', $s);
        if ($s === '' || $s === '-' || $s === '.' || $s === '-.') return '0';
        return $s;
    }

    // =========================
    // Helpers bc math (tránh float)
    // =========================
    private function bc_add($a, $b, $scale = 2) { return bcadd((string)$a, (string)$b, $scale); }
    private function bc_sub($a, $b, $scale = 2) { return bcsub((string)$a, (string)$b, $scale); }
    private function bc_mul($a, $b, $scale = 2) { return bcmul((string)$a, (string)$b, $scale); }
    private function bc_div($a, $b, $scale = 4) { return bcdiv((string)$a, (string)$b, $scale); }

    /**
     * Xác định user nào là người bị trừ tiền/giữ tiền.
     * - Ưu tiên handler_id (vì bạn hiển thị $task->handler)
     * - Fallback user_id
     */
    private function resolveWalletUserId(Task $task): int
    {
        // ✅ CHỈ LẤY THEO CỘT user TRONG BẢNG tasks
        // yêu cầu: tasks.user phải là ID (int/bigint). Nếu đang là string tên user thì phải đổi DB.
        $uid = $task->user;

        if (empty($uid) || !is_numeric($uid)) {
            throw ValidationException::withMessages([
                'task' => 'Cột user của task không hợp lệ (phải là ID user).'
            ]);
        }

        return (int) $uid;
    }


    /**
     * Trừ trực tiếp vào balance (available). (Giữ lại chức năng cũ)
     */
    public function charge(User $user, float $amount, string $description): void
    {
        DB::transaction(function () use ($user, $amount, $description) {

            $wallet = $user->wallet()->lockForUpdate()->first();
            if (!$wallet) throw new Exception('User chưa có ví');

            $amount = bcadd((string)$amount, '0', 2);

            if (bccomp((string)$wallet->balance, (string)$amount, 2) < 0) {
                throw new Exception('Số dư không đủ');
            }

            $balanceBefore = (string)$wallet->balance;
            $heldBefore    = (string)($wallet->held_balance ?? '0.00');

            $wallet->balance = $this->bc_sub($wallet->balance, $amount, 2);
            $wallet->save();

            WalletTransaction::create([
                'wallet_id'       => $wallet->id,
                'type'            => 'withdraw',
                'amount'          => $amount,
                'description'     => $description,
                'balance_before'  => $balanceBefore,
                'balance_after'   => (string)$wallet->balance,
                'held_before'     => $heldBefore,
                'held_after'      => (string)($wallet->held_balance ?? '0.00'),
            ]);
        });
    }

    /**
     * Tính số tiền HOLD mặc định từ Task:
     * net = days * expected_costs * (1 - rate/100)
     */
    public function calcExpectedAmount(Task $task): string
    {
        $days     = $this->normalizeNumber($task->days ?? 0);
        $expected = $this->normalizeNumber($task->expected_costs ?? 0);
        $rate     = $this->normalizeNumber($task->rate ?? 0);

        $gross    = $this->bc_mul($days, $expected, 2);
        $discount = $this->bc_sub('1', $this->bc_div($rate, '100', 4), 4);
        $net      = $this->bc_mul($gross, $discount, 2);

        if (bccomp($net, '0', 2) < 0) return '0.00';
        return $net;
    }

    /**
     * HOLD tiền theo Task: balance giảm, held_balance tăng
     */
    public function holdTask(Task $task, ?string $amount = null): WalletTransaction
    {
        return DB::transaction(function () use ($task, $amount) {

            // lock task trước
            $task = Task::whereKey($task->id)->lockForUpdate()->firstOrFail();

            // Idempotent: nếu đã hold thì trả transaction hold gần nhất
            if (!empty($task->hold_transaction_id) || $task->status === 'held') {
                $existing = WalletTransaction::where('ref_type', 'task')
                    ->where('ref_id', $task->id)
                    ->where('type', 'hold')
                    ->orderByDesc('id')
                    ->first();
                if ($existing) return $existing;
            }

            // lock wallet đúng user
            $walletUserId = $this->resolveWalletUserId($task);

            /** @var Wallet|null $wallet */
            $wallet = Wallet::where('user_id', $walletUserId)->lockForUpdate()->first();
            if (!$wallet) throw new Exception('User chưa có ví');

            // log sau khi đã có $wallet (tránh undefined variable)
            \Log::info('HOLD TARGET', [
                'task_id'         => $task->id,
                'task_user_id'    => $task->user_id,
                'task_handler_id' => $task->handler_id ?? null,
                'wallet_user_id'  => $wallet->user_id,
            ]);

            $holdAmount = $amount ?? $this->calcExpectedAmount($task);
            $holdAmount = bcadd((string)$holdAmount, '0', 2);

            if (bccomp($holdAmount, '0', 2) < 0) {
                throw ValidationException::withMessages(['amount' => 'Số tiền hold không hợp lệ.']);
            }

            // check tiền khả dụng
            if (bccomp((string)$wallet->balance, $holdAmount, 2) < 0) {
                throw ValidationException::withMessages(['balance' => 'Số dư không đủ để đăng ký dịch vụ.']);
            }

            $cycle = (int)($task->hold_cycle ?? 1);
            $idempotencyKey = "task:{$task->id}:hold:{$cycle}";

            $dup = WalletTransaction::where('wallet_id', $wallet->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();
            if ($dup) return $dup;

            $balanceBefore = (string)$wallet->balance;
            $heldBefore    = (string)($wallet->held_balance ?? '0.00');

            // move balance -> held
            $wallet->balance      = $this->bc_sub($wallet->balance, $holdAmount, 2);
            $wallet->held_balance = $this->bc_add($wallet->held_balance ?? '0.00', $holdAmount, 2);
            $wallet->save();

            $tx = WalletTransaction::create([
                'wallet_id'        => $wallet->id,
                'type'             => 'hold',
                'amount'           => $holdAmount,
                'description'      => "Hold tiền cho task #{$task->id}",
                'ref_type'         => 'task',
                'ref_id'           => $task->id,
                'idempotency_key'  => $idempotencyKey,
                'meta'             => [
                    'days'           => $task->days,
                    'expected_costs'  => (string)$task->expected_costs,
                    'rate'           => (string)$task->rate,
                    'wallet_user_id' => $wallet->user_id,
                ],
                'balance_before'   => $balanceBefore,
                'balance_after'    => (string)$wallet->balance,
                'held_before'      => $heldBefore,
                'held_after'       => (string)$wallet->held_balance,
            ]);

            // update task
            $task->price_expected       = $holdAmount;
            $task->hold_transaction_id  = $tx->id;
            $task->status               = 'held';
            $task->paid                 = 1; // ✅ đồng bộ paid = 1 khi hold
            $task->save();

            return $tx;
        });
    }

    /**
     * RELEASE hold: held_balance giảm, balance tăng
     */
    public function releaseTask(Task $task, string $reason = 'release'): WalletTransaction
    {
        return DB::transaction(function () use ($task, $reason) {

            $task = Task::whereKey($task->id)->lockForUpdate()->firstOrFail();

            if (empty($task->hold_transaction_id) || bccomp((string)$task->price_expected, '0', 2) < 0) {
                throw ValidationException::withMessages(['task' => 'Task chưa hold tiền.']);
            }

            if (!empty($task->capture_transaction_id) || $task->status === 'accepted') {
                throw ValidationException::withMessages(['task' => 'Task đã nghiệm thu, không thể release.']);
            }

            $walletUserId = $this->resolveWalletUserId($task);
            $wallet = Wallet::where('user_id', $walletUserId)->lockForUpdate()->firstOrFail();

            $amount = bcadd((string)$task->price_expected, '0', 2);

            $cycle = (int)($task->hold_cycle ?? 1);
            $idempotencyKey = "task:{$task->id}:release:{$cycle}";

            $dup = WalletTransaction::where('wallet_id', $wallet->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();
            if ($dup) return $dup;

            if (bccomp((string)$wallet->held_balance, $amount, 2) < 0) {
                throw ValidationException::withMessages(['wallet' => 'Held balance không đủ để release (dữ liệu lệch).']);
            }

            $balanceBefore = (string)$wallet->balance;
            $heldBefore    = (string)$wallet->held_balance;

            // move held -> balance
            $wallet->held_balance = $this->bc_sub($wallet->held_balance, $amount, 2);
            $wallet->balance      = $this->bc_add($wallet->balance, $amount, 2);
            $wallet->save();

            $tx = WalletTransaction::create([
                'wallet_id'        => $wallet->id,
                'type'             => 'release',
                'amount'           => $amount,
                'description'      => "Release tiền task #{$task->id} ({$reason})",
                'ref_type'         => 'task',
                'ref_id'           => $task->id,
                'idempotency_key'  => $idempotencyKey,
                'meta'             => ['reason' => $reason, 'wallet_user_id' => $wallet->user_id],
                'balance_before'   => $balanceBefore,
                'balance_after'    => (string)$wallet->balance,
                'held_before'      => $heldBefore,
                'held_after'       => (string)$wallet->held_balance,
            ]);

            // reset task để hold lại được
            $task->paid                = 0;
            $task->hold_transaction_id = null;
            $task->price_expected      = '0.00';
            $task->status              = 'registered';
            $task->hold_cycle          = (int)($task->hold_cycle ?? 1) + 1;
            $task->save();

            return $tx;
        });
    }

    /**
     * CAPTURE khi nghiệm thu: trừ từ held_balance
     */
    public function captureTask(Task $task, ?string $finalAmount = null): WalletTransaction
    {
        return DB::transaction(function () use ($task, $finalAmount) {

            $task = Task::whereKey($task->id)->lockForUpdate()->firstOrFail();

            if (empty($task->hold_transaction_id) || bccomp((string)$task->price_expected, '0', 2) <= 0) {
                throw ValidationException::withMessages(['task' => 'Task chưa hold tiền.']);
            }

            if (!empty($task->capture_transaction_id) || $task->status === 'accepted') {
                $existing = WalletTransaction::where('ref_type', 'task')
                    ->where('ref_id', $task->id)
                    ->where('type', 'capture')
                    ->orderByDesc('id')
                    ->first();
                if ($existing) return $existing;

                throw ValidationException::withMessages(['task' => 'Task đã capture trước đó.']);
            }

            $walletUserId = $this->resolveWalletUserId($task);
            $wallet = Wallet::where('user_id', $walletUserId)->lockForUpdate()->firstOrFail();

            $expected = bcadd((string)$task->price_expected, '0', 2);
            $final    = bcadd((string)($finalAmount ?? $expected), '0', 2);

            if (bccomp($final, $expected, 2) > 0) {
                throw ValidationException::withMessages([
                    'final' => 'Số tiền nghiệm thu lớn hơn số đã hold. (Cần hold thêm hoặc yêu cầu nạp thêm.)'
                ]);
            }

            $idempotencyKey = "task:{$task->id}:capture";
            $dup = WalletTransaction::where('wallet_id', $wallet->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();
            if ($dup) return $dup;

            if (bccomp((string)$wallet->held_balance, $final, 2) < 0) {
                throw ValidationException::withMessages(['wallet' => 'Held balance không đủ để capture (dữ liệu lệch).']);
            }

            $balanceBefore = (string)$wallet->balance;
            $heldBefore    = (string)$wallet->held_balance;

            $wallet->held_balance = $this->bc_sub($wallet->held_balance, $final, 2);
            $wallet->save();

            $tx = WalletTransaction::create([
                'wallet_id'       => $wallet->id,
                'type'            => 'capture',
                'amount'          => $final,
                'description'     => "Capture nghiệm thu task #{$task->id}",
                'ref_type'        => 'task',
                'ref_id'          => $task->id,
                'idempotency_key' => $idempotencyKey,
                'meta'            => ['expected' => $expected, 'final' => $final, 'wallet_user_id' => $wallet->user_id],
                'balance_before'  => $balanceBefore,
                'balance_after'   => (string)$wallet->balance,
                'held_before'     => $heldBefore,
                'held_after'      => (string)$wallet->held_balance,
            ]);

            // release phần dư nếu final < expected
            if (bccomp($final, $expected, 2) < 0) {
                $diff = bcsub($expected, $final, 2);

                if (bccomp((string)$wallet->held_balance, $diff, 2) < 0) {
                    throw ValidationException::withMessages(['wallet' => 'Held balance không đủ để release phần dư (dữ liệu lệch).']);
                }

                $idk = "task:{$task->id}:release_diff";
                $dup2 = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('idempotency_key', $idk)
                    ->first();

                if (!$dup2) {
                    $balanceB = (string)$wallet->balance;
                    $heldB    = (string)$wallet->held_balance;

                    $wallet->held_balance = $this->bc_sub($wallet->held_balance, $diff, 2);
                    $wallet->balance      = $this->bc_add($wallet->balance, $diff, 2);
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id'       => $wallet->id,
                        'type'            => 'release',
                        'amount'          => $diff,
                        'description'     => "Release phần dư task #{$task->id}",
                        'ref_type'        => 'task',
                        'ref_id'          => $task->id,
                        'idempotency_key' => $idk,
                        'meta'            => ['reason' => 'diff_after_capture', 'wallet_user_id' => $wallet->user_id],
                        'balance_before'  => $balanceB,
                        'balance_after'   => (string)$wallet->balance,
                        'held_before'     => $heldB,
                        'held_after'      => (string)$wallet->held_balance,
                    ]);
                }
            }

            $task->price_final            = $final;
            $task->capture_transaction_id = $tx->id;
            $task->status                 = 'accepted';
            $task->save();

            return $tx;
        });
    }


    // App\Services\WalletService.php
    public function settleTask(Task $task, int $adminId): array
    {
        return DB::transaction(function () use ($task, $adminId) {

            $task = Task::whereKey($task->id)->lockForUpdate()->firstOrFail();

            if ((int)$task->settled === 1) {
                return [
                    'refund' => (string)($task->refund_money ?? '0.00'),
                    'extra'  => (string)($task->extra_money ?? '0.00'),
                ];
            }

            $walletUserId = $this->resolveWalletUserId($task);
            $wallet = Wallet::where('user_id', $walletUserId)->lockForUpdate()->firstOrFail();

            $hold   = bcadd((string)($task->price_expected ?? '0.00'), '0', 2);
            $refund = bcadd((string)($task->refund_money ?? '0.00'), '0', 2);
            $extra  = bcadd((string)($task->extra_money ?? '0.00'), '0', 2);

            // idempotency theo cycle (bạn đang có hold_cycle)
            $cycle = (int)($task->settle_cycle ?? 1);
            $idkRelease = "task:{$task->id}:settle_release:{$cycle}";
            $idkRefund  = "task:{$task->id}:settle_refund:{$cycle}";
            $idkExtra   = "task:{$task->id}:settle_extra:{$cycle}";

            // === 1) RELEASE HOLD (chỉ giảm held, không cộng balance) ===
            if (bccomp($hold, '0', 2) > 0) {

                $dup = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('idempotency_key', $idkRelease)
                    ->first();
                if (!$dup) {

                    if (bccomp((string)$wallet->held_balance, $hold, 2) < 0) {
                        throw ValidationException::withMessages([
                            'wallet' => 'Held_balance không đủ để giải phóng hold của task này.',
                        ]);
                    }

                    $balanceBefore = (string)$wallet->balance;
                    $heldBefore    = (string)$wallet->held_balance;

                    $wallet->held_balance = $this->bc_sub($wallet->held_balance, $hold, 2);
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id'       => $wallet->id,
                        'type'            => 'release',
                        'amount'          => $hold,
                        'description'     => "Release tiền task #{$task->id} (settled)",
                        'ref_type'        => 'task',
                        'ref_id'          => $task->id,
                        'idempotency_key' => $idkRelease,
                        'meta'            => [
                            'reason' => 'settled_release_only',
                            'admin_id' => $adminId,
                            'wallet_user_id' => $wallet->user_id,
                        ],
                        'balance_before'  => $balanceBefore,
                        'balance_after'   => (string)$wallet->balance, // không đổi
                        'held_before'     => $heldBefore,
                        'held_after'      => (string)$wallet->held_balance,
                    ]);
                }
            }

            // === 2) REFUND (balance +refund) ===
            if (bccomp($refund, '0', 2) > 0 && bccomp($extra, '0', 2) <= 0) {

                $dup = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('idempotency_key', $idkRefund)
                    ->first();
                if (!$dup) {

                    $balanceBefore = (string)$wallet->balance;
                    $heldBefore    = (string)($wallet->held_balance ?? '0.00');

                    $wallet->balance = $this->bc_add($wallet->balance, $refund, 2);
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id'       => $wallet->id,
                        'type'            => 'rollback',
                        'amount'          => $refund,
                        'description'     => "Hoàn tiền task #{$task->id} (settled)",
                        'ref_type'        => 'task',
                        'ref_id'          => $task->id,
                        'idempotency_key' => $idkRefund,
                        'meta'            => [
                            'reason' => 'settled_refund',
                            'admin_id' => $adminId,
                            'wallet_user_id' => $wallet->user_id,
                        ],
                        'balance_before'  => $balanceBefore,
                        'balance_after'   => (string)$wallet->balance,
                        'held_before'     => $heldBefore,
                        'held_after'      => (string)($wallet->held_balance ?? '0.00'),
                    ]);
                }
            }

            // === 3) EXTRA (balance -extra) ===
            if (bccomp($extra, '0', 2) > 0 && bccomp($refund, '0', 2) <= 0) {

                $dup = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('idempotency_key', $idkExtra)
                    ->first();
                if (!$dup) {

                    if (bccomp((string)$wallet->balance, $extra, 2) < 0) {
                        throw ValidationException::withMessages([
                            'balance' => 'Số dư không đủ để thu thêm.',
                        ]);
                    }

                    $balanceBefore = (string)$wallet->balance;
                    $heldBefore    = (string)($wallet->held_balance ?? '0.00');

                    $wallet->balance = $this->bc_sub($wallet->balance, $extra, 2);
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id'       => $wallet->id,
                        'type'            => 'withdraw',
                        'amount'          => $extra,
                        'description'     => "Thu thêm tiền task #{$task->id} (settled)",
                        'ref_type'        => 'task',
                        'ref_id'          => $task->id,
                        'idempotency_key' => $idkExtra,
                        'meta'            => [
                            'reason' => 'settled_extra',
                            'admin_id' => $adminId,
                            'wallet_user_id' => $wallet->user_id,
                        ],
                        'balance_before'  => $balanceBefore,
                        'balance_after'   => (string)$wallet->balance,
                        'held_before'     => $heldBefore,
                        'held_after'      => (string)($wallet->held_balance ?? '0.00'),
                    ]);
                }
            }

            // update task
            $task->settled    = 1;
            $task->settled_at = now();
            $task->settled_by = $adminId;
            $task->save();

            return ['refund' => $refund, 'extra' => $extra];
        });
    }



    public function unsettleTask(Task $task, int $adminId): array
    {
        return DB::transaction(function () use ($task, $adminId) {
            
            // ✅ CHẶN: rank > 1 không được hủy tất toán
            $actor = User::query()->select('id', 'rank')->findOrFail($adminId);
            $actorRank = (int)($actor->rank ?? 0);
            if ($actorRank !== 1) {
                throw ValidationException::withMessages([
                    'permission' => 'Bạn không có quyền hủy tất toán (chỉ Admin rank = 1).',
                ]);
            }

            $task = Task::whereKey($task->id)->lockForUpdate()->firstOrFail();

            if ((int)$task->settled !== 1) {
                throw ValidationException::withMessages(['task' => 'Task chưa tất toán.']);
            }

            $walletUserId = $this->resolveWalletUserId($task);
            $wallet = Wallet::where('user_id', $walletUserId)->lockForUpdate()->firstOrFail();

            $hold   = bcadd((string)($task->price_expected ?? '0.00'), '0', 2);
            $refund = bcadd((string)($task->refund_money ?? '0.00'), '0', 2);
            $extra  = bcadd((string)($task->extra_money ?? '0.00'), '0', 2);

            // ✅ Chỉ khôi phục hold nếu task đã hold trước đó
            $hadHold = ((int)($task->paid ?? 0) === 1)
                && !empty($task->hold_transaction_id)
                && bccomp($hold, '0', 2) > 0;

            // stamp để idempotent cho lần toggle off này
            $cycle = (int)($task->settle_cycle ?? 1);

            $idkReturnExtra   = "task:{$task->id}:unsettle:return_extra:{$cycle}";
            $idkReclaimRefund = "task:{$task->id}:unsettle:reclaim_refund:{$cycle}";
            $idkRestoreFund   = "task:{$task->id}:unsettle:restore_hold_fund:{$cycle}";
            $idkHoldAgain     = "task:{$task->id}:unsettle:hold_again:{$cycle}";

            // ==========================================================
            // 0) (Quan trọng) Nếu task đã hold trước đó -> RESTORE quỹ hold
            //    (đưa tiền hold "đã tiêu" về lại ví), để tránh lỗi thiếu balance
            // ==========================================================
            if ($hadHold) {
                $dupFund = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('idempotency_key', $idkRestoreFund)
                    ->first();

                if (!$dupFund) {
                    $balanceBefore = (string)$wallet->balance;
                    $heldBefore    = (string)($wallet->held_balance ?? '0.00');

                    // +hold vào balance (khôi phục quỹ)
                    $wallet->balance = $this->bc_add($wallet->balance, $hold, 2);
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id'       => $wallet->id,
                        'type'            => 'rollback',
                        'amount'          => $hold,
                        'description'     => "Khôi phục quỹ hold task #{$task->id} (admin_toggle_off)",
                        'ref_type'        => 'task',
                        'ref_id'          => $task->id,
                        'idempotency_key' => $idkRestoreFund,
                        'meta'            => [
                            'reason' => 'unsettle_restore_hold_fund',
                            'admin_id' => $adminId,
                            'wallet_user_id' => $wallet->user_id,
                        ],
                        'balance_before'  => $balanceBefore,
                        'balance_after'   => (string)$wallet->balance,
                        'held_before'     => $heldBefore,
                        'held_after'      => (string)($wallet->held_balance ?? '0.00'),
                    ]);
                }
            }

            // ==========================================================
            // 1) Hoàn tác EXTRA: nếu trước đó đã trừ ví chính -> cộng lại
            // ==========================================================
            if (bccomp($extra, '0', 2) > 0 && bccomp($refund, '0', 2) <= 0) {

                $dup = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('idempotency_key', $idkReturnExtra)
                    ->first();

                if (!$dup) {
                    $balanceBefore = (string)$wallet->balance;
                    $heldBefore    = (string)($wallet->held_balance ?? '0.00');

                    $wallet->balance = $this->bc_add($wallet->balance, $extra, 2);
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id'       => $wallet->id,
                        'type'            => 'rollback',
                        'amount'          => $extra,
                        'description'     => "Cộng lại tiền đã thu thêm task #{$task->id} (admin_toggle_off)",
                        'ref_type'        => 'task',
                        'ref_id'          => $task->id,
                        'idempotency_key' => $idkReturnExtra,
                        'meta'            => [
                            'reason' => 'unsettle_return_extra',
                            'admin_id' => $adminId,
                            'wallet_user_id' => $wallet->user_id,
                        ],
                        'balance_before'  => $balanceBefore,
                        'balance_after'   => (string)$wallet->balance,
                        'held_before'     => $heldBefore,
                        'held_after'      => (string)($wallet->held_balance ?? '0.00'),
                    ]);
                }
            }

            // ==========================================================
            // 2) Hoàn tác REFUND: nếu trước đó đã cộng ví chính -> trừ lại
            //    (sau restore fund thì thường sẽ đủ balance)
            // ==========================================================
            if (bccomp($refund, '0', 2) > 0 && bccomp($extra, '0', 2) <= 0) {

                $dup = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('idempotency_key', $idkReclaimRefund)
                    ->first();

                if (!$dup) {
                    if (bccomp((string)$wallet->balance, $refund, 2) < 0) {
                        throw ValidationException::withMessages([
                            'balance' => 'Không đủ số dư để thu hồi tiền đã hoàn khi hủy tất toán.'
                        ]);
                    }

                    $balanceBefore = (string)$wallet->balance;
                    $heldBefore    = (string)($wallet->held_balance ?? '0.00');

                    $wallet->balance = $this->bc_sub($wallet->balance, $refund, 2);
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id'       => $wallet->id,
                        'type'            => 'withdraw',
                        'amount'          => $refund,
                        'description'     => "Trừ lại tiền đã hoàn task #{$task->id} (admin_toggle_off)",
                        'ref_type'        => 'task',
                        'ref_id'          => $task->id,
                        'idempotency_key' => $idkReclaimRefund,
                        'meta'            => [
                            'reason' => 'unsettle_reclaim_refund',
                            'admin_id' => $adminId,
                            'wallet_user_id' => $wallet->user_id,
                        ],
                        'balance_before'  => $balanceBefore,
                        'balance_after'   => (string)$wallet->balance,
                        'held_before'     => $heldBefore,
                        'held_after'      => (string)($wallet->held_balance ?? '0.00'),
                    ]);
                }
            }

            // ==========================================================
            // 3) Nếu task đã hold trước đó -> HOLD LẠI (balance -> held)
            // ==========================================================
            if ($hadHold) {
                $dupHold = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('idempotency_key', $idkHoldAgain)
                    ->first();

                if (!$dupHold) {
                    if (bccomp((string)$wallet->balance, $hold, 2) < 0) {
                        throw ValidationException::withMessages([
                            'balance' => 'Không đủ số dư để hold lại khi hủy tất toán.'
                        ]);
                    }

                    $balanceBefore = (string)$wallet->balance;
                    $heldBefore    = (string)($wallet->held_balance ?? '0.00');

                    $wallet->balance      = $this->bc_sub($wallet->balance, $hold, 2);
                    $wallet->held_balance = $this->bc_add($wallet->held_balance ?? '0.00', $hold, 2);
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id'       => $wallet->id,
                        'type'            => 'hold',
                        'amount'          => $hold,
                        'description'     => "Hold lại tiền cho task #{$task->id} (admin_toggle_off)",
                        'ref_type'        => 'task',
                        'ref_id'          => $task->id,
                        'idempotency_key' => $idkHoldAgain,
                        'meta'            => [
                            'reason' => 'unsettle_hold_again',
                            'admin_id' => $adminId,
                            'wallet_user_id' => $wallet->user_id,
                        ],
                        'balance_before'  => $balanceBefore,
                        'balance_after'   => (string)$wallet->balance,
                        'held_before'     => $heldBefore,
                        'held_after'      => (string)$wallet->held_balance,
                    ]);
                }
            }

            // Update task: hủy cờ tất toán
            $task->settled      = 0;
            $task->settled_at   = null;
            $task->settled_by   = null;
            $task->settle_cycle = $cycle + 1; // <<< quan trọng: cho phép tất toán lại lần sau
            $task->save();
            return ['refund' => $refund, 'extra' => $extra, 'hold' => $hold, 'had_hold' => $hadHold ? 1 : 0];
        });
    }



}
