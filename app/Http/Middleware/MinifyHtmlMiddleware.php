<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MinifyHtmlMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldMinify($request, $response)) {
            return $response;
        }

        $content = $response->getContent();
        if ($content === false || $content === '') {
            return $response;
        }

        $minified = $this->minify($content);

        $etag = '"' . md5($minified) . '"';
        $response->headers->set('ETag', $etag);
        $response->headers->set('Vary', 'Accept-Encoding');

        if ($request->header('If-None-Match') === $etag) {
            $response->setStatusCode(304);
            $response->setContent('');
            return $response;
        }

        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (str_contains($acceptEncoding, 'gzip') && function_exists('gzencode') && !in_array('ob_gzhandler', ob_list_handlers(), true)) {
            $compressed = gzencode($minified, 6);
            if ($compressed !== false && strlen($compressed) < strlen($minified)) {
                $response->setContent($compressed);
                $response->headers->set('Content-Encoding', 'gzip');
                $response->headers->set('Content-Length', (string) strlen($compressed));
                return $response;
            }
        }

        $response->setContent($minified);
        $response->headers->set('Content-Length', (string) strlen($minified));

        return $response;
    }

    protected function shouldMinify(Request $request, Response $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if ($request->is('admin*') || $request->is('api*') || $request->ajax() || $request->wantsJson()) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html');
    }

    protected function minify(string $html): string
    {
        $placeholders = [];

        $html = preg_replace_callback('/<(pre|textarea)\b[^>]*>.*?<\/\1>/is', function ($matches) use (&$placeholders) {
            $key = '___MINIFY_PRE_' . count($placeholders) . '___';
            $placeholders[$key] = $matches[0];
            return $key;
        }, $html);

        $html = preg_replace_callback('/<style\b([^>]*)>(.*?)<\/style>/is', function ($matches) use (&$placeholders) {
            $attrs = $matches[1];
            $css = trim($matches[2]);
            $minCss = $css !== '' ? $this->minifyCss($css) : '';
            $key = '___MINIFY_STYLE_' . count($placeholders) . '___';
            $placeholders[$key] = "<style{$attrs}>{$minCss}</style>";
            return $key;
        }, $html);

        $html = preg_replace_callback('/<script\b([^>]*)>(.*?)<\/script>/is', function ($matches) use (&$placeholders) {
            $attrs = $matches[1];
            $code = trim($matches[2]);

            if ($code === '' || str_contains($attrs, 'src=')) {
                $key = '___MINIFY_SCRIPT_' . count($placeholders) . '___';
                $placeholders[$key] = "<script{$attrs}>{$code}</script>";
                return $key;
            }

            if (str_contains($attrs, 'application/ld+json') || str_contains($attrs, 'application/json')) {
                $minCode = $this->minifyJson($code);
            } else {
                $minCode = $this->minifyJs($code);
            }

            $key = '___MINIFY_SCRIPT_' . count($placeholders) . '___';
            $placeholders[$key] = "<script{$attrs}>{$minCode}</script>";
            return $key;
        }, $html);

        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
        $html = preg_replace('/>\s+</s', '><', $html);
        $html = preg_replace('/[ \t]+/', ' ', $html);
        $html = preg_replace('/(\r?\n)+/', "\n", $html);

        if (!empty($placeholders)) {
            $html = strtr($html, $placeholders);
        }

        return trim($html);
    }

    protected function minifyJs(string $js): string
    {
        if (trim($js) === '') {
            return '';
        }

        $pattern = '/(`(?:[^`\\\\]|\\\\.)*`|\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*")|(\/\/[^\r\n]*)|(\/\*[\s\S]*?\*\/)/';

        $cleaned = preg_replace_callback($pattern, function ($matches) {
            if (!empty($matches[1])) {
                return $matches[1];
            }
            if (isset($matches[2]) && $matches[2] !== '') {
                return "\n";
            }
            return '';
        }, $js);

        if ($cleaned === null) {
            $cleaned = $js;
        }

        $lines = preg_split('/\r?\n/', $cleaned);
        $filtered = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $filtered[] = $trimmed;
            }
        }

        return implode("\n", $filtered);
    }

    protected function minifyCss(string $css): string
    {
        if (trim($css) === '') {
            return '';
        }

        $pattern = '/(\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*")|(\/\*[\s\S]*?\*\/)/';
        $cleaned = preg_replace_callback($pattern, function ($matches) {
            if (!empty($matches[1])) {
                return $matches[1];
            }
            return '';
        }, $css);

        if ($cleaned === null) {
            return $css;
        }

        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = preg_replace('/\s*([\{\};:,])\s*/', '$1', $cleaned);

        return trim($cleaned);
    }

    protected function minifyJson(string $json): string
    {
        $json = trim($json);
        $decoded = json_decode($json);
        if (json_last_error() === JSON_ERROR_NONE) {
            return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return preg_replace('/\s+/', ' ', $json);
    }
}
