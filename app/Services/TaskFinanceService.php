<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TaskFinanceService
{
    private function payable(float $cost, float $rate): float
    {
        // user_payable = cost * (100 - rate) / 100
        $payable = $cost * (100 - $rate) / 100;
        return round($payable, 2);
    }

    public function updateActualCost(int $taskId, float $actualCost): Task
    {
        return DB::transaction(function () use ($taskId, $actualCost) {

            /** @var Task $task */
            $task = Task::whereKey($taskId)->lockForUpdate()->firstOrFail();

            if ($task->financial_status === 'settled') {
                throw new RuntimeException('Task đã settled, không thể cập nhật actual_costs.');
            }
            if ($task->financial_status === 'cancelled') {
                throw new RuntimeException('Task đã cancelled, không thể cập nhật actual_costs.');
            }

            /** @var Wallet $wallet */
            $wallet = Wallet::where('user_id', $task->user_id)->lockForUpdate()->firstOrFail();

            $rate = (float) $task->rate;
            $newPayable = $this->payable($actualCost, $rate);

            $currentHeld = (float) ($task->held_amount ?? 0);
            $diff = round($newPayable - $currentHeld, 2);

            // HOLD thêm
            if ($diff > 0) {
                if ((float)$wallet->balance < $diff) {
                    throw new RuntimeException('Số dư không đủ để giữ thêm tiền (hold).');
                }

                $wallet->decrement('balance', $diff);
                $wallet->increment('held_balance', $diff);

                $wallet->transactions()->create([
                    'type' => 'hold',
                    'amount' => $diff,
                    'reference_type' => Task::class,
                    'reference_id' => $task->id,
                    'description' => 'Hold thêm do cập nhật actual_costs',
                ]);

                $task->held_amount = $currentHeld + $diff;
            }

            // RELEASE bớt
            if ($diff < 0) {
                $release = abs($diff);

                // về lý thuyết held_balance phải đủ; vẫn nên kiểm tra
                if ((float)$wallet->held_balance < $release) {
                    throw new RuntimeException('held_balance không đủ để release (dữ liệu bất thường).');
                }

                $wallet->decrement('held_balance', $release);
                $wallet->increment('balance', $release);

                $wallet->transactions()->create([
                    'type' => 'release',
                    'amount' => $release,
                    'reference_type' => Task::class,
                    'reference_id' => $task->id,
                    'description' => 'Release do actual_costs giảm',
                ]);

                $task->held_amount = $currentHeld - $release;
            }

            $task->actual_costs = round($actualCost, 2);
            $task->financial_status = 'holding';
            $task->save();

            return $task;
        });
    }

    public function finalize(int $taskId): Task
    {
        return DB::transaction(function () use ($taskId) {

            $task = Task::whereKey($taskId)->lockForUpdate()->firstOrFail();

            if ($task->financial_status === 'settled') {
                return $task; // idempotent
            }
            if ($task->financial_status === 'cancelled') {
                throw new RuntimeException('Task cancelled, không thể finalize.');
            }

            $wallet = Wallet::where('user_id', $task->user_id)->lockForUpdate()->firstOrFail();

            $capture = round((float)$task->held_amount, 2);
            if ($capture <= 0) {
                $task->financial_status = 'settled';
                $task->save();
                return $task;
            }

            if ((float)$wallet->held_balance < $capture) {
                throw new RuntimeException('held_balance không đủ để capture (dữ liệu bất thường).');
            }

            // capture: tiền rời khỏi hệ thống ví (chi trả thực tế)
            $wallet->decrement('held_balance', $capture);

            $wallet->transactions()->create([
                'type' => 'capture',
                'amount' => $capture,
                'reference_type' => Task::class,
                'reference_id' => $task->id,
                'description' => 'Chốt tiền task (finalize)',
            ]);

            $task->financial_status = 'settled';
            // tuỳ bạn: giữ held_amount để audit hoặc set về 0
            // $task->held_amount = 0;
            $task->save();

            return $task;
        });
    }

    public function cancel(int $taskId): Task
    {
        return DB::transaction(function () use ($taskId) {

            $task = Task::whereKey($taskId)->lockForUpdate()->firstOrFail();

            if ($task->financial_status === 'cancelled') {
                return $task;
            }
            if ($task->financial_status === 'settled') {
                throw new RuntimeException('Task settled, không thể cancel.');
            }

            $wallet = Wallet::where('user_id', $task->user_id)->lockForUpdate()->firstOrFail();

            $release = round((float)$task->held_amount, 2);
            if ($release > 0) {
                if ((float)$wallet->held_balance < $release) {
                    throw new RuntimeException('held_balance không đủ để release khi cancel.');
                }

                $wallet->decrement('held_balance', $release);
                $wallet->increment('balance', $release);

                $wallet->transactions()->create([
                    'type' => 'release',
                    'amount' => $release,
                    'reference_type' => Task::class,
                    'reference_id' => $task->id,
                    'description' => 'Release do huỷ task',
                ]);

                $task->held_amount = 0;
            }

            $task->financial_status = 'cancelled';
            $task->save();

            return $task;
        });
    }
}
