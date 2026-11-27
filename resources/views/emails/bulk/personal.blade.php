<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f5f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f5f7;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="620" style="max-width:620px;background-color:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background-color:#0f766e;padding:20px 24px;color:#ffffff;">
                            <div style="font-size:20px;font-weight:700;line-height:1.3;">Bất Động Sản INDOCHINE</div>
                            <div style="font-size:13px;opacity:.9;margin-top:4px;">Thông báo từ hệ thống nội bộ</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <div style="font-size:16px;font-weight:700;margin:0 0 12px;">Xin chào {{ $name }},</div>
                            <div style="font-size:15px;line-height:1.7;color:#374151;">
                                {!! nl2br(e($content)) !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-top:1px solid #e5e7eb;padding-top:16px;">
                                <tr>
                                    <td style="font-size:13px;line-height:1.6;color:#6b7280;">
                                        Trân trọng,<br>
                                        Mọi thắc mắc xin liên hệ Zalo: 0977572947 (Nguyễn Tuấn)
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
