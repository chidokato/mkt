<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class InviteController extends Controller
{
    public function create()
    {
        $data = Invite::orderByDesc('id')->get();
        return view('invite.create', compact('data'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'gender'    => ['required', 'in:MR,MS'], 
            'full_name' => ['required', 'string', 'max:60'],
            'title'     => ['nullable', 'string', 'max:60'],
            'avatar'    => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // 1) Lưu avatar vào public/uploads/invites/avatars
        $avatarFile = $request->file('avatar');
        $avatarName = 'avatar_' . time() . '_' . mt_rand(1000, 9999) . '.' . $avatarFile->getClientOriginalExtension();

        $avatarDirAbs = public_path('uploads/invites/avatars');
        if (!is_dir($avatarDirAbs)) {
            mkdir($avatarDirAbs, 0777, true);
        }

        $avatarFile->move($avatarDirAbs, $avatarName);

        $avatarPublicPath = 'uploads/invites/avatars/' . $avatarName; // lưu vào DB dạng public path

        // 2) Tạo record
        $invite = Invite::create([
            'user_id'     => auth()->id(),
            'gender'      => $data['gender'],
            'full_name'   => $data['full_name'],
            'title'       => $data['title'] ?? null,
            'avatar_path' => $avatarPublicPath,
            'output_path' => null,
        ]);

        // 3) Render ảnh (bọc try/catch để trả lỗi rõ)
        try {
            $outputPublicPath = $this->renderInviteImage($invite);
        } catch (\Throwable $e) {
            // Nếu lỗi thì trả JSON cho JS (hoặc trả về kèm errors nếu submit thường)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->withErrors($e->getMessage())->withInput();
        }

        $invite->update(['output_path' => $outputPublicPath]);

        $imageUrl = asset($outputPublicPath);
        $downloadUrl = route('invite.download', $invite);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'image_url' => $imageUrl,
                'download_url' => $downloadUrl,
            ]);
        }

        return redirect($downloadUrl);
    }

    public function download(Invite $invite)
    {
        abort_if(empty($invite->output_path), 404);

        $abs = public_path($invite->output_path);
        abort_if(!file_exists($abs), 404);

        $downloadName = 'thu-moi-' . $invite->id . '.png';
        return response()->download($abs, $downloadName, [
            'Content-Type' => 'image/png'
        ]);
    }

    private function renderInviteImage(Invite $invite): string
    {
        // ====== ẢNH NỀN (template) ======
        // Bạn đang dùng thumoi.png ở public/account/img/invite/
        switch ($invite->gender) {
            case 'MR':
                $baseAbs = public_path('templates/mr.png');
                break;

            case 'MS':
                $baseAbs = public_path('templates/ms.png');
                break;

            default:
                throw new \Exception('Giới tính không hợp lệ: ' . $invite->gender);
        }
        
        if (!file_exists($baseAbs)) {
            throw new \Exception('Không tìm thấy template: ' . $baseAbs);
        }

        $img = Image::make($baseAbs);
        $w = $img->width();

        // ====== AVATAR ======
        $avatarAbs = public_path($invite->avatar_path);

        if (!file_exists($avatarAbs)) {
            throw new \Exception('Không tìm thấy avatar: ' . $avatarAbs);
        }

        $avatarSize = 650;
        $avatar = Image::make($avatarAbs)
            ->orientate()               // ✅ auto xoay đúng theo EXIF
            ->fit($avatarSize, $avatarSize);

        // mask tròn
        $mask = Image::canvas($avatarSize, $avatarSize);
        $mask->circle($avatarSize, $avatarSize / 2, $avatarSize / 2, function ($draw) {
            $draw->background('#fff');
        });
        $avatar->mask($mask, true);

        // vị trí avatar (chỉnh theo template của bạn)
        $avatarX = intval(($w - $avatarSize) / 2);
        $avatarY = 433;

        $img->insert($avatar, 'top-left', $avatarX, $avatarY);

        // ====== FONT ======
        $fontBoldAbs = public_path('fonts/1FTV-VIP-BETHANY-AVANUE.OTF');
        $fontRegAbs  = public_path('fonts/1FTV-VIP-RACE-SPORT.TTF');
        $fontXBold  = public_path('fonts/SVN-Gilroy-XBold.otf');

        if (!file_exists($fontBoldAbs)) {
            throw new \Exception('Thiếu font: ' . $fontBoldAbs);
        }
        if (!file_exists($fontRegAbs)) {
            throw new \Exception('Thiếu font: ' . $fontRegAbs);
        }
        if (!file_exists($fontXBold)) {
            throw new \Exception('Thiếu font: ' . $fontXBold);
        }

        // ====== TEXT ======
        $name = mb_strtoupper($invite->full_name);

        // tên
        $img->text($name, intval($w / 2), $avatarY + $avatarSize + 180, function ($font) use ($fontRegAbs) {
            $font->file($fontRegAbs); // FONT ITALIC
            $font->size(120);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('middle');
        });


        // chức vụ
        if (!empty($invite->title)) {
            $img->text($invite->title, intval($w / 2), $avatarY + $avatarSize + 330, function ($font) use ($fontXBold) {
                $font->file($fontXBold);
                $font->size(90);
                $font->color('#fff');
                $font->align('center');
                $font->valign('middle');
            });
        }

        // ====== LƯU OUTPUT VÀO public/uploads/... ======
        $outDirAbs = public_path('uploads/invites/output');
        if (!is_dir($outDirAbs)) {
            mkdir($outDirAbs, 0777, true);
        }

        $fileName = 'invite_' . $invite->id . '.png';
        $outAbs = $outDirAbs . DIRECTORY_SEPARATOR . $fileName;

        $img->save($outAbs, 90, 'png');

        // Trả về path public để asset() build URL
        return 'uploads/invites/output/' . $fileName;
    }
}
