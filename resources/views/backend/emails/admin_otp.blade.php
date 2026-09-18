<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Zippy Security Desk - Admin Verification Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #dbeafe; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none;">
    @php
        $digits = str_split(str_pad((string)($otp ?? '000000'), 6, '0', STR_PAD_LEFT));
        $recipientEmail = $email ?? ($user->email ?? 'security@zippybd.com');
        $verifyUrl = route('admin.forgot_password', ['step' => 2, 'email' => $recipientEmail, 'otp' => $otp]);
    @endphp
    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#dbeafe" style="background: radial-gradient(circle at 50% 10%, #dbeafe 0%, #e0e7ff 50%, #eff6ff 100%); background-color: #dbeafe; padding: 48px 16px;">
        <tr>
            <td align="center" valign="top">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 540px; width: 100%;">
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td valign="middle" style="padding-right: 12px;">
                                        <table border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center" valign="middle" bgcolor="#2563eb" width="44" height="44" style="width: 44px; height: 44px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); background-color: #2563eb; border-radius: 12px; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 22px; font-weight: 800; line-height: 44px; text-align: center; box-shadow: 0 8px 18px rgba(37, 99, 235, 0.35); border: 1px solid rgba(255, 255, 255, 0.4);">
                                                    Z
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td valign="middle">
                                        <span style="font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; display: block; line-height: 1.2;">
                                            Zippy
                                        </span>
                                        <span style="font-size: 10px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: 0.12em; display: block; margin-top: 2px;">
                                            Security Desk
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" style="background: linear-gradient(160deg, #ffffff 0%, #f8faff 100%); background-color: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 20px; box-shadow: 0 24px 48px -12px rgba(37, 99, 235, 0.16), 0 4px 12px rgba(15, 23, 42, 0.04); overflow: hidden;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td height="4" bgcolor="#2563eb" style="height: 4px; background: linear-gradient(90deg, #2563eb 0%, #0ea5e9 50%, #6366f1 100%); line-height: 1px; font-size: 1px;">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="padding: 36px 34px 38px 34px;">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0 0 10px 0; font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; line-height: 1.3;">
                                            Account Recovery Code
                                        </h1>
                                        <p style="margin: 0 0 22px 0; font-size: 13.5px; line-height: 1.6; color: #475569;">
                                            Hello <strong style="color: #0f172a;">{{ $user->name ?? 'Administrator' }}</strong>,<br />
                                            A security verification request was received for your Zippy Operations account. Use the 6-digit one-time code below to complete authorization:
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding: 6px 0 18px 0;">
                                        <table border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto; user-select: all; -webkit-user-select: all; cursor: pointer;">
                                            <tr>
                                                @foreach($digits as $digit)
                                                <td align="center" valign="middle" width="50" height="60" bgcolor="#f8faff" style="width: 50px; height: 60px; background: #f8faff; border: 1.5px solid #bfdbfe; border-radius: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 28px; font-weight: 800; color: #1d4ed8; text-align: center; box-shadow: 0 6px 14px -2px rgba(37, 99, 235, 0.12); padding: 0;">
                                                    {{ $digit }}
                                                </td>
                                                @if(!$loop->last)
                                                <td width="8" style="width: 8px; font-size: 1px; line-height: 1px;">&nbsp;</td>
                                                @endif
                                                @endforeach
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-bottom: 24px;">
                                        <table border="0" cellpadding="0" cellspacing="0" align="center">
                                            <tr>
                                                <td align="center" bgcolor="#2563eb" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); background-color: #2563eb; border-radius: 10px; padding: 12px 26px; box-shadow: 0 8px 18px -2px rgba(37, 99, 235, 0.38); border: 1px solid rgba(255, 255, 255, 0.3);">
                                                    <a href="{{ $verifyUrl }}" target="_blank" style="color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 700; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: inline-block; letter-spacing: 0.02em;">
                                                        Verify &amp; Reset Password &rarr;
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin: 9px 0 0 0; text-align: center; font-size: 11px; color: #64748b;">
                                            Tap the digit boxes above to select &amp; copy, or click to verify instantly.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 22px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td bgcolor="#eff6ff" style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 12px 16px;">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td style="font-size: 12px; line-height: 1.55; color: #1e40af;">
                                                                <strong style="color: #1e3a8a;">Security Notice:</strong> Valid for <strong>15 minutes</strong> only. Never share this code with anyone. Zippy staff will never ask for your verification code.
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border-top: 1px solid #e2e8f0; padding-top: 18px;">
                                        <p style="margin: 0; font-size: 12.5px; line-height: 1.6; color: #64748b;">
                                            If you did not initiate this recovery request, your account is safe and no changes will take place. Please disregard this notice or report suspicious activity.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top: 24px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="font-size: 12px; line-height: 1.6; color: #475569;">
                                        <p style="margin: 0 0 6px 0;">
                                            Automated security dispatch sent to <strong style="color: #0f172a;">{{ $recipientEmail }}</strong>.
                                        </p>
                                        <p style="margin: 0;">
                                            &copy; {{ date('Y') }} Zippy Operations Suite &bull; Contact: <a href="mailto:security@zippybd.com" style="color: #2563eb; text-decoration: none; font-weight: 700;">security@zippybd.com</a>
                                        </p>
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