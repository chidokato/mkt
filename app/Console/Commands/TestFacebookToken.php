<?php

namespace App\Console\Commands;

use App\Models\BusinessManager;
use App\Services\FacebookAdsService;
use Illuminate\Console\Command;

class TestFacebookToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fb:test {identifier : Token chuỗi trực tiếp hoặc ID của BM trong database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra kết nối Facebook Graph API và lấy danh sách tài khoản quảng cáo thử nghiệm';

    /**
     * Execute the console command.
     */
    public function handle(FacebookAdsService $fbService)
    {
        $identifier = $this->argument('identifier');

        // Nếu truyền ID số -> tìm BM trong DB
        if (is_numeric($identifier)) {
            $bm = BusinessManager::find($identifier);
            if (!$bm) {
                $this->error("Không tìm thấy Business Manager có ID: {$identifier}");
                return 1;
            }
            $this->info("Đang kiểm tra với BM: [{$bm->name}] (ID: {$bm->id})");
            $token = $bm->access_token;
        } else {
            $token = $identifier;
        }

        $this->info("Đang kết nối tới Meta Graph API...");

        try {
            $accounts = $fbService->getAssignedAdAccounts($token);

            $this->info("Kết nối thành công! Tìm thấy " . count($accounts) . " tài khoản quảng cáo:");

            $tableRows = [];
            foreach ($accounts as $acc) {
                $curr = $acc['currency'] ?? 'VND';
                $divisor = \App\Services\FacebookAdsService::getCurrencyDivisor($curr);
                $spent = isset($acc['amount_spent']) ? number_format($acc['amount_spent'] / $divisor, 0, ',', '.') : 0;
                $balance = isset($acc['balance']) ? number_format($acc['balance'] / $divisor, 0, ',', '.') : 0;

                $statusText = match ($acc['account_status'] ?? 0) {
                    1 => 'Active',
                    2 => 'Disabled',
                    3 => 'Unsettled',
                    default => 'Status ' . ($acc['account_status'] ?? 'Unknown'),
                };

                $tableRows[] = [
                    $acc['id'] ?? 'N/A',
                    $acc['name'] ?? 'Chưa đặt tên',
                    $statusText,
                    "{$spent} {$curr}",
                    "{$balance} {$curr}",
                ];
            }

            $this->table(['Ad Account ID', 'Tên tài khoản', 'Trạng thái', 'Đã chi tiêu', 'Số dư nợ'], $tableRows);
            return 0;
        } catch (\Exception $e) {
            $this->error("Lỗi kết nối Facebook API: " . $e->getMessage());
            return 1;
        }
    }
}
