<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset Code</title>
</head>
<body style="margin:0;padding:0;background-color:#0f172a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#f8fafc;">
    <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#0f172a;padding:40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width:520px;background-color:#1e293b;border:1px solid #334155;border-radius:12px;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.4);">
                    <tr>
                        <td style="padding:32px 36px;border-bottom:1px solid #334155;background:linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                            <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <div style="display:inline-block;width:38px;height:38px;line-height:38px;text-align:center;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#ffffff;font-size:18px;font-weight:800;border-radius:8px;vertical-align:middle;">
                                            {{ strtoupper(substr($storeName ?? 'Z', 0, 1)) }}
                                        </div>
                                        <span style="font-size:18px;font-weight:700;color:#ffffff;margin-left:12px;vertical-align:middle;letter-spacing:-0.02em;">
                                            {{ $storeName ?? 'ZippyBD' }} Admin Desk
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 36px 28px 36px;">
                            <h2 style="margin:0 0 14px 0;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:-0.02em;">
                                Password Reset Verification Code
                            </h2>
                            <p style="margin:0 0 20px 0;font-size:14px;line-height:1.6;color:#94a3b8;">
                                Hello {{ $user->name ?? 'Administrator' }},<br>
                                We received a request to reset your password for the admin control panel. Use the verification code below to authorize your request:
                            </p>
                            <div style="text-align:center;margin:28px 0;padding:22px;background-color:#0f172a;border:1px solid #475569;border-radius:10px;">
                                <span style="display:inline-block;font-size:34px;font-weight:800;letter-spacing:8px;color:#818cf8;font-family:monospace;">
                                    {{ $otp }}
                                </span>
                            </div>
                            <p style="margin:0 0 16px 0;font-size:13px;line-height:1.5;color:#94a3b8;">
                                <strong style="color:#cbd5e1;">Notice:</strong> This code is single-use and will expire in <strong style="color:#cbd5e1;">15 minutes</strong>.
                            </p>
                            <p style="margin:0;font-size:12px;line-height:1.5;color:#64748b;">
                                If you did not initiate this request, you can safely ignore this email. No changes will be made to your account.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 36px;border-top:1px solid #334155;background-color:#162032;text-align:center;">
                            <p style="margin:0;font-size:11px;color:#64748b;letter-spacing:0.02em;">
                                &copy; {{ date('Y') }} {{ $storeName ?? 'ZippyBD' }} Enterprise Operations. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

