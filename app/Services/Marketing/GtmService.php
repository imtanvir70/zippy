<?php

namespace App\Services\Marketing;

use App\Services\Frontend\FrontendCacheService;
use Illuminate\Support\Facades\DB;

class GtmService
{
    public static function getContainerId(): ?string
    {
        $id = FrontendCacheService::setting('gtm_container_id');
        if (!$id) {
            $setting = DB::table('settings')->where('key', 'gtm_container_id')->first();
            $id = $setting ? $setting->value : null;
        }
        return $id ? trim($id) : null;
    }

    public static function isEnabled(): bool
    {
        $enabled = FrontendCacheService::setting('gtm_enabled');
        if ($enabled === null) {
            $setting = DB::table('settings')->where('key', 'gtm_enabled')->first();
            $enabled = $setting ? $setting->value : null;
        }
        return $enabled === '1' || $enabled === 'true';
    }

    public static function isPlaceholderId(?string $id): bool
    {
        if (empty($id)) {
            return true;
        }
        $upper = strtoupper(trim($id));
        return in_array($upper, ['GTM-ZIPPY01', 'GTM-XXXXXXX', 'GTM-EXAMPLE']) || str_contains($upper, 'ZIPPY01') || str_contains($upper, 'XXXXXX');
    }

    public static function renderHeadScript(): string
    {
        $containerId = self::getContainerId();
        if (!self::isEnabled() || empty($containerId) || self::isPlaceholderId($containerId)) {
            return '';
        }

        return "<script data-turbo-eval=\"false\">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$containerId}');</script>";
    }

    public static function renderBodyScript(): string
    {
        $containerId = self::getContainerId();
        if (!self::isEnabled() || empty($containerId) || self::isPlaceholderId($containerId)) {
            return '';
        }

        return '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $containerId . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
    }
}
