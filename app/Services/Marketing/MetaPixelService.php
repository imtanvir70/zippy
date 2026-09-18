<?php

namespace App\Services\Marketing;

use App\Services\Frontend\FrontendCacheService;
use Illuminate\Support\Facades\DB;

class MetaPixelService
{
    public static function getPixelId(): ?string
    {
        $id = FrontendCacheService::setting('meta_pixel_id');
        if (!$id) {
            $setting = DB::table('settings')->where('key', 'meta_pixel_id')->first();
            $id = $setting ? $setting->value : null;
        }
        if (!$id) {
            $id = config('services.meta.pixel_id');
        }
        return $id ? trim($id) : null;
    }

    public static function isPlaceholderId(?string $id): bool
    {
        if (empty($id)) {
            return true;
        }
        $val = trim($id);
        return in_array(strtoupper($val), ['123456789012345', 'YOUR_PIXEL_ID', 'PIXEL_ID']) || strlen($val) < 8;
    }

    public static function renderHeadScript(): string
    {
        $pixelId = self::getPixelId();
        if (empty($pixelId) || self::isPlaceholderId($pixelId)) {
            return '';
        }

        return "<!-- Meta Pixel Code -->
<script data-turbo-eval=\"false\">
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pixelId}');
</script>
<!-- End Meta Pixel Code -->\n";
    }

    public static function renderBodyScript(): string
    {
        $pixelId = self::getPixelId();
        if (empty($pixelId) || self::isPlaceholderId($pixelId)) {
            return '';
        }

        return "<noscript><img height=\"1\" width=\"1\" style=\"display:none\" src=\"https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1\" alt=\"\"/></noscript>\n";
    }
}
