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

        // Performance: ETag conditional request support (304 Not Modified)
        $etag = '"' . md5($minified) . '"';
        $response->headers->set('ETag', $etag);
        $response->headers->set('Vary', 'Accept-Encoding');

        if ($request->header('If-None-Match') === $etag) {
            $response->setStatusCode(304);
            $response->setContent('');
            return $response;
        }

        // Performance: Transparent GZIP encoding (80%+ network bandwidth reduction)
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
        if ($request->is('admin*') || $request->is('api*')) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html');
    }

    protected function minify(string $html): string
    {
        $placeholders = [];

        // 1. Protect pre and textarea verbatim
        $html = preg_replace_callback('/<(pre|textarea)\b[^>]*>.*?<\/\1>/is', function ($matches) use (&$placeholders) {
            $key = '___MINIFY_PRE_' . count($placeholders) . '___';
            $placeholders[$key] = $matches[0];
            return $key;
        }, $html);

        // 2. Safe CSS Minification for style tags (Single unbroken line)
        $html = preg_replace_callback('/<style\b([^>]*)>(.*?)<\/style>/is', function ($matches) use (&$placeholders) {
            $attrs = $matches[1];
            $css = trim($matches[2]);
            $minCss = $css !== '' ? $this->minifyCss($css) : '';
            $minCss = str_replace(["\r", "\n"], '', $minCss);
            $key = '___MINIFY_STYLE_' . count($placeholders) . '___';
            $placeholders[$key] = "<style{$attrs}>{$minCss}</style>";
            return $key;
        }, $html);

        // 3. Safe JS Minification for script tags (Single unbroken line)
        $html = preg_replace_callback('/<script\b([^>]*)>(.*?)<\/script>/is', function ($matches) use (&$placeholders) {
            $attrs = $matches[1];
            $code = trim($matches[2]);

            // Skip external scripts or empty script tags
            if ($code === '' || str_contains($attrs, 'src=')) {
                $key = '___MINIFY_SCRIPT_' . count($placeholders) . '___';
                $placeholders[$key] = "<script{$attrs}>" . str_replace(["\r", "\n"], '', $code) . "</script>";
                return $key;
            }

            // Compact JSON-LD / JSON scripts
            if (str_contains($attrs, 'application/ld+json') || str_contains($attrs, 'application/json')) {
                $minCode = $this->minifyJson($code);
            } else {
                $minCode = $this->minifyJs($code);
            }

            $minCode = str_replace(["\r", "\n"], '', $minCode);
            $key = '___MINIFY_SCRIPT_' . count($placeholders) . '___';
            $placeholders[$key] = "<script{$attrs}>{$minCode}</script>";
            return $key;
        }, $html);

        // 4. HTML Minification (Strip comments, collapse spaces, remove all newlines)
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
        $html = preg_replace('/>\s+</s', '><', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        $html = str_replace(["\r", "\n"], '', $html);

        // 5. Restore protected pre and textarea
        if (!empty($placeholders)) {
            $html = strtr($html, $placeholders);
        }

        return trim($html);
    }

    /**
     * Safely minify JavaScript into a single continuous unreadable line
     * without breaking ASI, strings, regex, or template literals.
     */
    protected function minifyJs(string $js): string
    {
        if (trim($js) === '') {
            return '';
        }

        // Step 1: Strip comments while preserving strings and template literals
        $pattern = '/(`(?:[^`\\\\]|\\\\.)*`|\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*")|(\/\/[^\r\n]*)|(\/\*[\s\S]*?\*\/)/';

        $cleaned = preg_replace_callback($pattern, function ($matches) {
            if (!empty($matches[1])) {
                return $matches[1];
            }
            if (isset($matches[2]) && $matches[2] !== '') {
                return "\n"; // Semicolon insertion boundary
            }
            return '';
        }, $js);

        if ($cleaned === null) {
            $cleaned = $js;
        }

        // Step 2: Protect template literals during line joining
        $templatePlaceholders = [];
        $protected = preg_replace_callback('/`(?:[^`\\\\]|\\\\.)*`/s', function ($m) use (&$templatePlaceholders) {
            $key = '___TL_' . count($templatePlaceholders) . '___';
            $templatePlaceholders[$key] = $m[0];
            return $key;
        }, $cleaned);

        // Step 3: Line joining without line breaks
        $lines = preg_split('/\r?\n/', $protected);
        $result = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if ($result === '') {
                $result = $trimmed;
                continue;
            }

            $lastChar = substr($result, -1);
            $firstChar = $trimmed[0];

            // Never insert semicolon before closing brackets, punctuation, dots, operators, or keywords
            if (str_contains(')}].,:?=+*/%&|^<>!~-', $firstChar)) {
                $sep = '';
            } elseif ($firstChar === '{') {
                $sep = '';
            } elseif (preg_match('/^(else|catch|finally|while|instanceof|in)\b/', $trimmed)) {
                $sep = ($lastChar === '}') ? '' : ' ';
            } elseif ($lastChar === '}') {
                $sep = ';';
            } elseif (str_contains(';{}([,:?=+*/%&|^<>!~.-', $lastChar)) {
                $sep = '';
            } else {
                $sep = ';';
            }

            // If no separator but both edges are word characters, add single space
            if ($sep === '' && preg_match('/[a-zA-Z0-9_$]/', $lastChar) && preg_match('/[a-zA-Z0-9_$]/', $firstChar)) {
                $sep = ' ';
            }

            // Avoid merging + with + (++) or - with - (--)
            if (($lastChar === '+' && $firstChar === '+') || ($lastChar === '-' && $firstChar === '-')) {
                $sep = ' ';
            }

            $result .= $sep . $trimmed;
        }

        // Step 4: Protect strings ('...' and "...")
        $stringPlaceholders = [];
        $result = preg_replace_callback('/(\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*")/s', function ($m) use (&$stringPlaceholders) {
            $key = '___STR_' . count($stringPlaceholders) . '___';
            $stringPlaceholders[$key] = $m[0];
            return $key;
        }, $result);

        // Step 5: Compress multiple spaces into 1 space
        $result = preg_replace('/\s+/', ' ', $result);

        // Step 6: Remove spaces around safe delimiters
        $result = preg_replace('/\s*([\{\}\(\)\[\];,:?=\*\/\%&\|\^<>!~])\s*/', '$1', $result);
        $result = preg_replace('/\s*([+\-])\s*/', '$1', $result);
        $result = str_replace(['+ +', '- -'], ['+ +', '- -'], $result);

        // Step 7: Restore strings & templates
        if (!empty($stringPlaceholders)) {
            $result = strtr($result, $stringPlaceholders);
        }
        if (!empty($templatePlaceholders)) {
            // Flatten newlines in template literals
            foreach ($templatePlaceholders as $k => $v) {
                $templatePlaceholders[$k] = preg_replace('/\r?\n\s*/', ' ', $v);
            }
            $result = strtr($result, $templatePlaceholders);
        }

        return trim($result);
    }

    /**
     * Safely minify CSS content inside style tags into a single line.
     */
    protected function minifyCss(string $css): string
    {
        if (trim($css) === '') {
            return '';
        }

        // Strip comments while preserving quotes
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

        // Collapse whitespace
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        // Remove spaces around CSS delimiters
        $cleaned = preg_replace('/\s*([\{\};:,])\s*/', '$1', $cleaned);

        // Ensure calc() operators retain spaces without breaking CSS variable/env hyphens
        $cleaned = preg_replace_callback('/calc\((?:[^()]|\([^()]*\))+\)/i', function ($m) {
            return preg_replace('/(?<=[0-9%)\s])\s*([+\-])\s*(?=[0-9\s]|\b(?:env|var|calc)\b)/i', ' $1 ', $m[0]);
        }, $cleaned);

        return trim($cleaned);
    }

    /**
     * Safely compact JSON content into a single line.
     */
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
