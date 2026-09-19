<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

class DynamicMailConfigService
{
    public static function apply(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $mailSettings = DB::table('settings')->whereIn('key', [
            'mail_mailer',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ])->pluck('value', 'key');

        if (!empty($mailSettings['mail_host'])) {
            $encryption = $mailSettings['mail_encryption'] ?? 'ssl';
            $scheme = ($encryption === 'ssl' || $encryption === 'smtps') ? 'smtps' : null;

            config([
                'mail.default' => $mailSettings['mail_mailer'] ?? 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $mailSettings['mail_host'],
                'mail.mailers.smtp.port' => (int) ($mailSettings['mail_port'] ?? 465),
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.mailers.smtp.scheme' => $scheme,
                'mail.mailers.smtp.username' => $mailSettings['mail_username'] ?? null,
                'mail.mailers.smtp.password' => $mailSettings['mail_password'] ?? null,
                'mail.from.address' => $mailSettings['mail_from_address'] ?? $mailSettings['mail_username'] ?? config('mail.from.address'),
                'mail.from.name' => $mailSettings['mail_from_name'] ?? config('mail.from.name', 'Zippy'),
            ]);

            Mail::purge('smtp');
        }
    }
}
