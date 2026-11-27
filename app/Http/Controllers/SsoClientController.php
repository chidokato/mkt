<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SsoClientController extends Controller
{
    public function redirectToPortal()
    {
        $portal = rtrim((string) env('SSO_PORTAL_URL', 'https://indochinerealestate.vn'), '/');
        return redirect()->away($portal . '/sso/login?app=mkt');
    }

    public function callback(Request $request)
    {
        $payload = $this->verifyPayload((string) $request->query('payload'), (string) $request->query('signature'));
        abort_unless($payload, 403, 'Token SSO không hợp lệ hoặc đã hết hạn.');

        $employeeCode = trim((string) ($payload['employee_code'] ?? ''));
        abort_if($employeeCode === '', 403, 'Thiếu Mã nhân viên từ SSO.');

        $user = User::where('employee_code', $employeeCode)->first();
        if (!$user && !empty($payload['email'])) {
            $user = User::where('email', strtolower($payload['email']))->first();
        }

        if (!$user) {
            $user = new User();
            $user->employee_code = $employeeCode;
            $user->name = $payload['name'] ?? $employeeCode;
            $user->yourname = $payload['name'] ?? $employeeCode;
            $user->email = !empty($payload['email']) ? strtolower($payload['email']) : ($employeeCode . '@sso.local');
            $user->phone = $payload['phone'] ?? null;
            $user->password = Hash::make(Str::random(24));
            $user->permission = 6;
            $user->status = 'active';
            $user->save();
        } else {
            $user->employee_code = $employeeCode;
            if (!empty($payload['email'])) {
                $emailLower = strtolower($payload['email']);
                if (!User::where('email', $emailLower)->where('id', '!=', $user->id)->exists()) {
                    $user->email = $emailLower;
                }
            }
            if (!empty($payload['phone'])) {
                $user->phone = $payload['phone'];
            }
            $user->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        if ((int) ($user->permission ?? 6) < 6) {
            return redirect()->route('admin');
        }
        return redirect()->route('account.dashboard.main');
    }

    private function verifyPayload(string $encoded, string $signature): ?array
    {
        $secret = (string) env('SSO_SECRET', 'idc_sso_shared_secret_2026_indochine');
        if ($secret === '' || $encoded === '' || $signature === '') {
            return null;
        }
        if (!hash_equals(hash_hmac('sha256', $encoded, $secret), $signature)) {
            return null;
        }
        $json = base64_decode(strtr($encoded, '-_', '+/'), true);
        $data = $json !== false ? json_decode($json, true) : null;
        if (!is_array($data) || empty($data['timestamp'])) {
            return null;
        }
        if (abs(now()->timestamp - (int) $data['timestamp']) > (int) env('SSO_TOKEN_TTL', 300)) {
            return null;
        }
        return $data;
    }
}
