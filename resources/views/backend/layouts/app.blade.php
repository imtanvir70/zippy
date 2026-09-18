@php
    $accentHex = $themeSettings['accent_color'] ?? '#6366f1';
    $cleanHex = ltrim($accentHex, '#');
    if (strlen($cleanHex) === 3) {
        $cleanHex = $cleanHex[0].$cleanHex[0].$cleanHex[1].$cleanHex[1].$cleanHex[2].$cleanHex[2];
    }
    $r = hexdec(substr($cleanHex, 0, 2)) ?: 99;
    $g = hexdec(substr($cleanHex, 2, 2)) ?: 102;
    $b = hexdec(substr($cleanHex, 4, 2)) ?: 241;
    $accentRgb = "{$r}, {$g}, {$b}";
    $fontFamily = $themeSettings['font_family'] ?? 'Outfit';
    $primaryColor = $themeSettings['primary_color'] ?? '#0f172a';
@endphp
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Zippy') }}</title>

    <meta name="turbo-cache-control" content="no-preview">
    <meta name="turbo-visit-control" content="reload-on-error">

    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.es2017-umd.js" data-turbo-track="reload"></script>

    <script data-turbo-eval="false">
        (function() {
            var saved = localStorage.getItem('theme');
            var theme = saved || 'dark';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('backend/lib/bootstrap.min.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('backend/lib/select2.min.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('backend/lib/dataTables.bootstrap5.min.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('backend/lib/all.min.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('backend/lib/template.css') }}" data-turbo-track="reload">

    <script src="{{ asset('backend/lib/themePicker.js') }}" data-turbo-track="reload" data-turbo-eval="false"></script>

    <style>
        .turbo-progress-bar {
            height: 3px;
            background: linear-gradient(90deg, var(--accent), #06b6d4, #10b981);
            box-shadow: 0 0 10px rgba(var(--accent-rgb), 0.7);
            z-index: 99999;
        }
        :root {
            --sidebar-w: 275px;
            --sidebar-mini-w: 72px;
            --topbar-h: 68px;
            --bs-body-font-family: 'Plus Jakarta Sans', 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
            --accent: {{ $accentHex }};
            --accent-rgb: {{ $accentRgb }};
            --bs-primary: {{ $accentHex }};
            --bs-primary-rgb: {{ $accentRgb }};
            --card-bg: var(--surface-2, #ffffff);
            --text-color: var(--text-main, #0f172a);
            --input-bg: var(--surface-2, #ffffff);
            --input-group-bg: var(--surface, #f8fafc);
        }

        body {
            font-family: var(--bs-body-font-family);
            letter-spacing: -0.01em;
            background-color: var(--page-bg) !important;
            color: var(--text-main) !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.25);
            border-radius: 9999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.45);
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.25) transparent;
        }

        [data-bs-theme="dark"] {
            --page-bg: #090d16;
            --surface: rgba(15, 23, 42, 0.92);
            --surface-2: #0f172a;
            --card-bg: #0f172a;
            --input-bg: #0f172a;
            --input-group-bg: rgba(30, 41, 59, 0.7);
            --border-color: #1e293b;
            --text-main: #f8fafc;
            --text-color: #f8fafc;
            --text-muted: #94a3b8;
            --sidebarLable: #64748b;
            --tableHeader: #1e293b;
            --tableBorder: #1e293b;
            --modalBg: #0f172a;
            --modalBorder: #1e293b;
            --cardHeading: #0f172a;
            --card-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4);
        }

        [data-bs-theme="light"] {
            --page-bg: #f8fafc;
            --surface: rgba(255, 255, 255, 0.94);
            --surface-2: #ffffff;
            --card-bg: #ffffff;
            --input-bg: #ffffff;
            --input-group-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-color: #0f172a;
            --text-muted: #64748b;
            --sidebarLable: #64748b;
            --tableHeader: #f1f5f9;
            --tableBorder: #e2e8f0;
            --modalBg: #ffffff;
            --modalBorder: #e2e8f0;
            --cardHeading: #ffffff;
            --card-shadow: 0 1px 3px rgba(15, 23, 42, 0.05), 0 8px 24px -4px rgba(15, 23, 42, 0.05);
        }

        .card {
            background: var(--surface-2) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 14px !important;
            box-shadow: var(--card-shadow) !important;
        }

        .stat-card {
            padding: 1.25rem;
            border-radius: 14px !important;
        }

        .stat-icon {
            width: 44px !important;
            height: 44px !important;
            border-radius: 10px !important;
            background: rgba(var(--accent-rgb), 0.12) !important;
            color: var(--accent) !important;
            font-size: 1.1rem !important;
        }

        .nav-group {
            margin-bottom: 12px;
            padding: 6px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .nav-group.active-group,
        .nav-group:has(.nav-linkx.active) {
            border-color: rgba(var(--accent-rgb), 0.35) !important;
            background: rgba(var(--accent-rgb), 0.05) !important;
        }

        .nav-group.active-group .nav-group-header,
        .nav-group:has(.nav-linkx.active) .nav-group-header {
            color: var(--accent) !important;
        }

        [data-bs-theme="light"] .nav-group {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        [data-bs-theme="light"] .nav-group.active-group,
        [data-bs-theme="light"] .nav-group:has(.nav-linkx.active) {
            background: rgba(var(--accent-rgb), 0.08) !important;
            border-color: rgba(var(--accent-rgb), 0.4) !important;
        }

        .nav-group-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 8px 6px 8px;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
        }

        .nav-group-icon {
            font-size: 0.72rem;
            opacity: 0.8;
            color: var(--accent);
        }

        .nav-group-links {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .nav-linkx {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 8px 12px !important;
            border-radius: 8px !important;
            color: var(--text-muted) !important;
            text-decoration: none !important;
            font-weight: 500 !important;
            font-size: 0.88rem !important;
            width: 100% !important;
            margin-bottom: 2px !important;
            transition: all 0.15s ease !important;
        }

        .nav-linkx b {
            background: transparent !important;
            border-radius: 6px !important;
            height: 24px !important;
            width: 24px !important;
            min-width: 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.95rem !important;
            color: inherit !important;
            flex-shrink: 0 !important;
            line-height: normal !important;
        }

        .nav-linkx .nav-text {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .nav-linkx:hover {
            background: rgba(255, 255, 255, 0.05) !important;
            color: var(--text-main) !important;
        }

        .nav-linkx.active {
            background: rgba(var(--accent-rgb), 0.12) !important;
            color: var(--accent) !important;
            font-weight: 600 !important;
            box-shadow: none !important;
            position: relative !important;
        }

        .nav-linkx.active::before {
            content: '' !important;
            position: absolute !important;
            left: 0 !important;
            top: 6px !important;
            bottom: 6px !important;
            width: 3.5px !important;
            border-radius: 0 4px 4px 0 !important;
            background: var(--accent) !important;
            box-shadow: 0 0 10px rgba(var(--accent-rgb), 0.8) !important;
        }

        .nav-linkx.active b {
            color: var(--accent) !important;
        }

        .sidebar-brand {
            height: var(--topbar-h) !important;
            padding: 0 16px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            border-bottom: 1px solid var(--border-color) !important;
            background: rgba(255, 255, 255, 0.01) !important;
        }

        .brand-link {
            display: flex !important;
            align-items: center !important;
            text-decoration: none !important;
            color: inherit !important;
            transition: all 0.2s ease !important;
        }

        .brand-link:hover {
            opacity: 0.94 !important;
        }

        .brand-badge {
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            border-radius: 11px !important;
            background: linear-gradient(135deg, var(--accent) 0%, #06b6d4 100%) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #ffffff !important;
            font-size: 1.05rem !important;
            box-shadow: 0 4px 14px rgba(var(--accent-rgb), 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
            flex-shrink: 0 !important;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease !important;
        }

        .brand-link:hover .brand-badge {
            transform: scale(1.06) rotate(-4deg) !important;
            box-shadow: 0 6px 18px rgba(var(--accent-rgb), 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.4) !important;
        }

        .sidebar-brand .brand-badge {
            margin-right: 10px !important;
        }

        .brand-meta {
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            line-height: 1.15 !important;
            overflow: hidden !important;
        }

        .brand-text {
            font-size: 1.12rem !important;
            font-weight: 800 !important;
            letter-spacing: -0.03em !important;
            color: var(--text-main) !important;
            white-space: nowrap !important;
            line-height: 1.1 !important;
        }

        .brand-text-accent {
            color: var(--accent) !important;
            margin-left: 1px !important;
        }

        .brand-badge-pill {
            font-size: 0.58rem !important;
            font-weight: 800 !important;
            letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
            padding: 2px 6px !important;
            border-radius: 6px !important;
            background: rgba(var(--accent-rgb), 0.15) !important;
            color: var(--accent) !important;
            border: 1px solid rgba(var(--accent-rgb), 0.3) !important;
            line-height: 1 !important;
            display: inline-flex !important;
            align-items: center !important;
        }

        .brand-subtitle {
            font-size: 0.64rem !important;
            font-weight: 600 !important;
            letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
            color: #64748b !important;
            white-space: nowrap !important;
            margin-top: 3px !important;
            line-height: 1 !important;
        }

        .sidebar-close-btn {
            width: 32px !important;
            height: 32px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 8px !important;
            border: 1px solid var(--border-color) !important;
            background: rgba(255, 255, 255, 0.05) !important;
            color: var(--text-muted) !important;
            cursor: pointer !important;
            transition: all 0.15s ease !important;
        }

        .sidebar-close-btn:hover {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
        }

        .nav-section {
            padding: 14px 12px 100px 12px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            height: calc(100vh - var(--topbar-h)) !important;
        }

        .topbar-action-btn {
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            border-radius: 10px !important;
            display: inline-flex;
            align-items: center !important;
            justify-content: center !important;
            background: rgba(255, 255, 255, 0.04) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-muted) !important;
            cursor: pointer !important;
            position: relative !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.06) !important;
            text-decoration: none !important;
            outline: none !important;
        }

        [data-bs-theme="light"] .topbar-action-btn {
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #475569 !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.9) !important;
        }

        .topbar-action-btn:hover {
            background: rgba(var(--accent-rgb), 0.1) !important;
            border-color: rgba(var(--accent-rgb), 0.4) !important;
            color: var(--accent) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 14px rgba(var(--accent-rgb), 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
        }

        .topbar-action-btn:active {
            transform: translateY(0) scale(0.96) !important;
        }

        .topbar-action-btn i {
            font-size: 0.92rem !important;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.2s ease !important;
        }

        .topbar-action-btn:hover i {
            transform: scale(1.12) !important;
        }

        .topbar-theme-icon-sun {
            color: #f59e0b !important;
        }

        .topbar-theme-icon-moon {
            color: #818cf8 !important;
        }

        .topbar-store-btn {
            height: 38px !important;
            padding: 0 14px !important;
            margin-left: 10px !important;
            border-radius: 11px !important;
            display: inline-flex;
            align-items: center !important;
            gap: 8px !important;
            font-size: 0.82rem !important;
            font-weight: 600 !important;
            letter-spacing: -0.01em !important;
            background: rgba(16, 185, 129, 0.08) !important;
            border: 1px solid rgba(16, 185, 129, 0.24) !important;
            color: #10b981 !important;
            text-decoration: none !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
        }

        [data-bs-theme="light"] .topbar-store-btn {
            background: rgba(16, 185, 129, 0.06) !important;
            border-color: rgba(16, 185, 129, 0.26) !important;
            color: #059669 !important;
        }

        .topbar-store-btn:hover {
            background: rgba(16, 185, 129, 0.16) !important;
            border-color: rgba(16, 185, 129, 0.5) !important;
            color: #10b981 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
        }

        .topbar-store-btn:active {
            transform: translateY(0) scale(0.97) !important;
        }

        .topbar-live-beacon {
            position: relative !important;
            width: 8px !important;
            height: 8px !important;
            border-radius: 50% !important;
            background: #10b981 !important;
            box-shadow: 0 0 8px #10b981 !important;
            flex-shrink: 0 !important;
        }

        .topbar-live-beacon::after {
            content: '' !important;
            position: absolute !important;
            top: -3px !important;
            left: -3px !important;
            right: -3px !important;
            bottom: -3px !important;
            border-radius: 50% !important;
            border: 1.5px solid #10b981 !important;
            animation: beaconPulse 2s cubic-bezier(0.24, 0, 0.38, 1) infinite !important;
        }

        @keyframes beaconPulse {
            0% { transform: scale(0.7); opacity: 0.9; }
            70% { transform: scale(1.7); opacity: 0; }
            100% { transform: scale(1.9); opacity: 0; }
        }

        .topbar-store-icon {
            font-size: 0.72rem !important;
            opacity: 0.75 !important;
            transition: transform 0.2s ease, opacity 0.2s ease !important;
        }

        .topbar-store-btn:hover .topbar-store-icon {
            transform: translate(2px, -2px) !important;
            opacity: 1 !important;
        }

        .topbar-studio-btn {
            height: 38px !important;
            padding: 0 14px !important;
            border-radius: 11px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.01em !important;
            background: linear-gradient(135deg, rgba(var(--accent-rgb), 0.12), rgba(6, 182, 212, 0.08)) !important;
            border: 1px solid rgba(var(--accent-rgb), 0.25) !important;
            color: var(--text-main) !important;
            cursor: pointer !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
            text-decoration: none !important;
            outline: none !important;
        }

        .topbar-studio-btn:hover {
            background: linear-gradient(135deg, rgba(var(--accent-rgb), 0.2), rgba(6, 182, 212, 0.15)) !important;
            border-color: rgba(var(--accent-rgb), 0.5) !important;
            color: var(--accent) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(var(--accent-rgb), 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.15) !important;
        }

        .topbar-studio-btn:active {
            transform: translateY(0) scale(0.97) !important;
        }

        .topbar-studio-icon {
            width: 22px !important;
            height: 22px !important;
            border-radius: 6px !important;
            background: linear-gradient(135deg, var(--accent) 0%, #06b6d4 100%) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #ffffff !important;
            font-size: 0.72rem !important;
            box-shadow: 0 2px 6px rgba(var(--accent-rgb), 0.35) !important;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        }

        .topbar-studio-btn:hover .topbar-studio-icon {
            transform: rotate(15deg) scale(1.1) !important;
        }

        .topbar-divider {
            width: 1px !important;
            height: 24px !important;
            background: var(--border-color) !important;
            margin: 0 4px !important;
        }

        .topbar-user-card {
            height: 40px !important;
            padding: 3px 10px 3px 4px !important;
            border-radius: 12px !important;
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid transparent !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            user-select: none !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        [data-bs-theme="light"] .topbar-user-card {
            background: rgba(15, 23, 42, 0.02) !important;
        }

        .topbar-user-card:hover {
            background: rgba(255, 255, 255, 0.07) !important;
            border-color: var(--border-color) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        [data-bs-theme="light"] .topbar-user-card:hover {
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06) !important;
        }

        .topbar-user-avatar-wrap {
            position: relative !important;
            flex-shrink: 0 !important;
        }

        .topbar-user-avatar {
            width: 32px !important;
            height: 32px !important;
            border-radius: 9px !important;
            font-size: 0.88rem !important;
            font-weight: 800 !important;
        }

        .topbar-user-status {
            position: absolute !important;
            bottom: -1px !important;
            right: -1px !important;
            width: 9px !important;
            height: 9px !important;
            border-radius: 50% !important;
            background: #10b981 !important;
            border: 2px solid var(--surface-2) !important;
            box-shadow: 0 0 5px #10b981 !important;
        }

        .topbar-user-name {
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            line-height: 1.15 !important;
            color: var(--text-main) !important;
            letter-spacing: -0.01em !important;
            white-space: nowrap !important;
        }

        .topbar-user-role {
            font-size: 0.65rem !important;
            font-weight: 600 !important;
            color: var(--text-muted) !important;
            line-height: 1.15 !important;
            letter-spacing: 0.03em !important;
            text-transform: uppercase !important;
            margin-top: 1px !important;
            white-space: nowrap !important;
        }

        .topbar-user-chevron {
            font-size: 0.65rem !important;
            color: var(--text-muted) !important;
            transition: transform 0.2s ease, color 0.2s ease !important;
        }

        .topbar-user-card:hover .topbar-user-chevron {
            color: var(--accent) !important;
            transform: translateY(1px) !important;
        }

        .main {
            padding: 24px 28px !important;
            min-height: calc(100vh - var(--topbar-h)) !important;
        }

        @media (max-width: 767.98px) {
            .main {
                padding: 16px !important;
            }
        }

        .filter-card {
            background: var(--surface-2) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 12px !important;
            padding: 1rem 1.25rem !important;
            margin-bottom: 1.25rem !important;
            box-shadow: var(--card-shadow) !important;
            transition: background-color 0.2s ease, border-color 0.2s ease !important;
        }

        .filter-card .input-group {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            width: 100% !important;
            height: 42px !important;
            min-height: 42px !important;
            max-height: 42px !important;
            border-radius: 10px !important;
            transition: box-shadow 0.2s ease !important;
        }

        .filter-card .input-group-text {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 42px !important;
            min-height: 42px !important;
            max-height: 42px !important;
            min-width: 44px !important;
            padding: 0 14px !important;
            background: var(--input-group-bg, var(--surface)) !important;
            border: 1px solid var(--border-color) !important;
            border-right: none !important;
            color: var(--text-muted) !important;
            border-top-left-radius: 10px !important;
            border-bottom-left-radius: 10px !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            font-size: 0.9rem !important;
            line-height: 1 !important;
            box-sizing: border-box !important;
            flex-shrink: 0 !important;
            transition: border-color 0.2s ease, color 0.2s ease, background-color 0.2s ease !important;
        }

        .filter-card .input-group-text i {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.95rem !important;
            line-height: 1 !important;
            margin: 0 !important;
        }

        .filter-card .form-control,
        .filter-card .form-select,
        .filter-card .filter-control {
            display: flex !important;
            align-items: center !important;
            height: 42px !important;
            min-height: 42px !important;
            max-height: 42px !important;
            background-color: var(--input-bg, var(--surface-2)) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
            font-size: 0.88rem !important;
            padding: 0 14px !important;
            box-sizing: border-box !important;
            box-shadow: none !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease !important;
        }

        .filter-card select.form-select,
        .filter-card select.filter-control {
            padding-right: 36px !important;
            background-position: right 12px center !important;
        }

        .filter-card .input-group > .form-control,
        .filter-card .input-group > .form-select,
        .filter-card .input-group > .filter-control {
            border-left: 1px solid var(--border-color) !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-top-right-radius: 10px !important;
            border-bottom-right-radius: 10px !important;
            margin-left: 0 !important;
            flex: 1 1 auto !important;
        }

        .filter-card .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.15) !important;
        }

        .filter-card .input-group:focus-within .input-group-text {
            border-color: var(--accent) !important;
            color: var(--accent) !important;
            background-color: rgba(var(--accent-rgb), 0.05) !important;
        }

        .filter-card .input-group:focus-within .form-control,
        .filter-card .input-group:focus-within .form-select,
        .filter-card .input-group:focus-within .filter-control {
            border-color: var(--accent) !important;
            outline: none !important;
        }

        .filter-card form .form-control:not(.input-group *),
        .filter-card form .form-select:not(.input-group *) {
            border-radius: 10px !important;
        }

        .filter-card select option,
        .filter-card select optgroup {
            background-color: var(--surface-2) !important;
            color: var(--text-main) !important;
        }

        .filter-card .filter-btn {
            height: 42px !important;
            min-height: 42px !important;
            max-height: 42px !important;
            border-radius: 10px !important;
            border: 1px solid var(--border-color) !important;
            font-size: 0.88rem !important;
            font-weight: 500 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            background: var(--surface-2) !important;
            color: var(--text-muted) !important;
            box-sizing: border-box !important;
            padding: 0 16px !important;
            line-height: 1 !important;
            transition: all 0.2s ease !important;
        }

        .filter-card .filter-btn:hover {
            background: rgba(var(--accent-rgb), 0.08) !important;
            border-color: var(--accent) !important;
            color: var(--accent) !important;
        }

        .filter-card .filter-btn:active {
            transform: translateY(1px) !important;
        }

        .btn-action,
        .btn-action-icon,
        .btn-table-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            max-width: 32px !important;
            padding: 0 !important;
            margin: 0 !important;
            border-radius: 8px !important;
            border: 1px solid var(--border-color) !important;
            background: var(--surface-2) !important;
            color: var(--text-muted) !important;
            font-size: 0.82rem !important;
            line-height: 1 !important;
            text-decoration: none !important;
            cursor: pointer !important;
            outline: none !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            vertical-align: middle !important;
            box-sizing: border-box !important;
            appearance: none !important;
            -webkit-appearance: none !important;
        }

        .btn-action.w-auto,
        .btn-action.px-2\.5,
        a.btn-action.w-auto,
        button.btn-action.w-auto {
            width: auto !important;
            min-width: auto !important;
            max-width: none !important;
            padding: 0 10px !important;
            gap: 6px !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
        }

        .btn-action.text-secondary,
        a.btn-action.text-secondary,
        button.btn-action.text-secondary {
            background: var(--surface-2) !important;
            border-color: var(--border-color) !important;
            color: var(--text-muted) !important;
        }

        .btn-action.text-secondary:hover,
        a.btn-action.text-secondary:hover,
        button.btn-action.text-secondary:hover,
        .btn-action:hover,
        .btn-action-icon:hover,
        .btn-table-icon:hover {
            background: rgba(var(--accent-rgb), 0.1) !important;
            border-color: var(--accent) !important;
            color: var(--accent) !important;
            transform: translateY(-1.5px) !important;
            box-shadow: 0 4px 10px rgba(var(--accent-rgb), 0.2) !important;
        }

        .btn-action.text-primary,
        a.btn-action.text-primary,
        button.btn-action.text-primary {
            background: rgba(var(--accent-rgb), 0.08) !important;
            border-color: rgba(var(--accent-rgb), 0.22) !important;
            color: var(--accent) !important;
        }

        .btn-action.text-primary:hover,
        a.btn-action.text-primary:hover,
        button.btn-action.text-primary:hover {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            color: #ffffff !important;
            transform: translateY(-1.5px) !important;
            box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.35) !important;
        }

        .btn-action.text-danger,
        a.btn-action.text-danger,
        button.btn-action.text-danger,
        .btn-table-icon.btn-del {
            background: rgba(239, 68, 68, 0.08) !important;
            border-color: rgba(239, 68, 68, 0.22) !important;
            color: #ef4444 !important;
        }

        .btn-action.text-danger:hover,
        a.btn-action.text-danger:hover,
        button.btn-action.text-danger:hover,
        .btn-table-icon.btn-del:hover {
            background: #ef4444 !important;
            border-color: #ef4444 !important;
            color: #ffffff !important;
            transform: translateY(-1.5px) !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35) !important;
        }

        .btn-action.text-success,
        a.btn-action.text-success,
        button.btn-action.text-success {
            background: rgba(16, 185, 129, 0.08) !important;
            border-color: rgba(16, 185, 129, 0.22) !important;
            color: #10b981 !important;
        }

        .btn-action.text-success:hover,
        a.btn-action.text-success:hover,
        button.btn-action.text-success:hover {
            background: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            transform: translateY(-1.5px) !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35) !important;
        }

        .btn-action:active,
        .btn-action-icon:active,
        .btn-table-icon:active {
            transform: translateY(0) scale(0.95) !important;
            box-shadow: none !important;
        }

        .btn-action i,
        .btn-action-icon i,
        .btn-table-icon i {
            pointer-events: none !important;
            transition: transform 0.2s ease !important;
        }

        .btn-action:hover i.fa-arrow-up-right-from-square {
            transform: translate(1px, -1px) !important;
        }

        .btn-action.text-primary:hover i.fa-pen-to-square,
        .btn-action.text-primary:hover i.fa-pen {
            transform: rotate(-8deg) scale(1.08) !important;
        }

        .btn-action.text-danger:hover i.fa-trash,
        .btn-action.text-danger:hover i.fa-trash-can {
            transform: scale(1.12) !important;
        }

        .btn-action.text-success:hover i.fa-circle-plus {
            transform: rotate(90deg) scale(1.08) !important;
        }

        .table td .d-inline-flex,
        .table td .d-flex {
            align-items: center !important;
            gap: 6px !important;
        }

        .table {
            --bs-table-color: var(--text-main) !important;
            --bs-table-bg: transparent !important;
            --bs-table-border-color: var(--border-color) !important;
            color: var(--text-main) !important;
            border-color: var(--border-color) !important;
        }

        .table th {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase !important;
            color: var(--text-muted) !important;
            background: var(--surface) !important;
            border-bottom: 1px solid var(--border-color) !important;
            padding: 12px 16px !important;
            white-space: nowrap !important;
        }

        .table td {
            padding: 12px 16px !important;
            vertical-align: middle !important;
            font-size: 0.88rem !important;
            border-bottom: 1px solid var(--border-color) !important;
        }

        .table tbody tr {
            transition: background-color 0.15s ease !important;
        }

        .table-hover tbody tr:hover,
        .table tbody tr:hover {
            background-color: rgba(var(--accent-rgb), 0.04) !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: var(--text-muted) !important;
            font-size: 0.84rem !important;
            margin: 12px 0 !important;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            background-color: var(--surface-2) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
            border-radius: 8px !important;
            padding: 6px 12px !important;
            font-size: 0.85rem !important;
            outline: none !important;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.15) !important;
        }

        .dataTables_wrapper {
            position: relative !important;
        }

        .dataTables_wrapper.dt-is-processing table.dataTable,
        .dataTables_wrapper.dt-is-processing .table-responsive,
        table.dataTable.dt-table-processing,
        .dataTables_wrapper:has(div.dataTables_processing[style*="display: block"]) table.dataTable,
        .dataTables_wrapper:has(div.dataTables_processing:not([style*="display: none"])) table.dataTable {
            filter: blur(4px) opacity(0.4) !important;
            pointer-events: none !important;
            user-select: none !important;
            transition: filter 0.2s ease, opacity 0.2s ease !important;
        }

        table.dataTable,
        .table-responsive {
            transition: filter 0.2s ease, opacity 0.2s ease !important;
        }

        div.dataTables_processing {
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            width: auto !important;
            min-width: 170px !important;
            max-width: 90vw !important;
            margin: 0 !important;
            padding: 12px 24px !important;
            z-index: 1060 !important;
            border-radius: 12px !important;
            background: rgba(15, 23, 42, 0.9) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            border: 1px solid rgba(var(--accent-rgb), 0.35) !important;
            box-shadow: 0 16px 36px -6px rgba(0, 0, 0, 0.45), 0 0 20px rgba(var(--accent-rgb), 0.15) !important;
            color: #f8fafc !important;
            font-size: 0.84rem !important;
            font-weight: 600 !important;
            letter-spacing: 0.02em !important;
            gap: 10px !important;
            text-align: center !important;
        }

        div.dataTables_processing[style*="display: none"] {
            display: none !important;
        }

        div.dataTables_processing:not([style*="display: none"]) {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .dataTables_wrapper.dt-is-processing div.dataTables_processing {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        [data-bs-theme="light"] div.dataTables_processing,
        html:not([data-bs-theme="dark"]) div.dataTables_processing {
            background: rgba(255, 255, 255, 0.94) !important;
            color: #0f172a !important;
            border-color: rgba(var(--accent-rgb), 0.4) !important;
            box-shadow: 0 16px 36px -6px rgba(0, 0, 0, 0.14), 0 0 20px rgba(var(--accent-rgb), 0.12) !important;
        }

        div.dataTables_processing > div:last-child {
            display: none !important;
        }

        div.dataTables_processing .spinner-border {
            width: 1.15rem !important;
            height: 1.15rem !important;
            border-width: 2px !important;
            color: var(--accent) !important;
            vertical-align: middle !important;
            margin-right: 6px !important;
        }

        div.dataTables_processing .spinner-border ~ .spinner-border {
            display: none !important;
        }

        div.dataTables_processing:not(:has(.spinner-border))::before {
            content: "" !important;
            display: inline-block !important;
            width: 1.15rem !important;
            height: 1.15rem !important;
            border: 2px solid rgba(var(--accent-rgb), 0.25) !important;
            border-top-color: var(--accent) !important;
            border-radius: 50% !important;
            animation: dt-global-spin 0.75s linear infinite !important;
            margin-right: 8px !important;
            vertical-align: middle !important;
        }

        @keyframes dt-global-spin {
            to { transform: rotate(360deg); }
        }

        .page-item.active .page-link {
            background-color: var(--accent) !important;
            border-color: var(--accent) !important;
            color: #ffffff !important;
        }

        .page-link {
            background-color: var(--surface-2) !important;
            border-color: var(--border-color) !important;
            color: var(--text-muted) !important;
            font-size: 0.85rem !important;
            border-radius: 8px !important;
            margin: 0 2px !important;
        }

        .form-control, .form-select {
            background-color: var(--surface-2) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
            border-radius: 10px !important;
            font-size: 0.88rem !important;
            padding: 8px 14px !important;
            transition: all 0.15s ease !important;
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--surface-2) !important;
            border-color: var(--accent) !important;
            color: var(--text-main) !important;
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.18) !important;
            z-index: 3 !important;
        }

        .input-group {
            position: relative !important;
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            width: 100% !important;
            border-radius: 10px !important;
            transition: box-shadow 0.2s ease, border-color 0.2s ease !important;
        }

        .input-group > .form-control,
        .input-group > .form-select {
            position: relative !important;
            flex: 1 1 auto !important;
            width: 1% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
        }

        .input-group > * {
            border-radius: 0 !important;
        }

        .input-group > :first-child,
        .input-group > .form-control:first-child,
        .input-group > .form-select:first-child,
        .input-group > .input-group-text:first-child,
        .input-group > .btn:first-child {
            border-top-left-radius: 10px !important;
            border-bottom-left-radius: 10px !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .input-group > :last-child,
        .input-group > .form-control:last-child,
        .input-group > .form-select:last-child,
        .input-group > .input-group-text:last-child,
        .input-group > .btn:last-child {
            border-top-right-radius: 10px !important;
            border-bottom-right-radius: 10px !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            margin-left: -1px !important;
        }

        .input-group > :not(:first-child):not(:last-child) {
            border-radius: 0 !important;
            margin-left: -1px !important;
        }

        .input-group-text {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background-color: var(--surface-2) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-muted) !important;
            font-size: 0.88rem !important;
            padding: 8px 14px !important;
            white-space: nowrap !important;
            box-sizing: border-box !important;
            transition: all 0.15s ease !important;
        }

        .input-group > .btn,
        .input-group > .btn-outline-secondary,
        .input-group > .toggle-password-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background-color: var(--surface-2) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-muted) !important;
            font-size: 0.88rem !important;
            padding: 8px 14px !important;
            min-width: 44px !important;
            box-shadow: none !important;
            box-sizing: border-box !important;
            transition: all 0.15s ease !important;
            z-index: 1 !important;
            cursor: pointer !important;
        }

        .input-group > .btn:hover,
        .input-group > .btn-outline-secondary:hover,
        .input-group > .toggle-password-btn:hover {
            background-color: rgba(var(--accent-rgb), 0.08) !important;
            border-color: var(--accent) !important;
            color: var(--accent) !important;
            z-index: 2 !important;
        }

        .input-group > .btn:focus,
        .input-group > .btn-outline-secondary:focus,
        .input-group > .toggle-password-btn:focus {
            border-color: var(--accent) !important;
            color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.18) !important;
            z-index: 3 !important;
            outline: none !important;
        }

        .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.15) !important;
        }

        .input-group:focus-within > .form-control,
        .input-group:focus-within > .form-select,
        .input-group:focus-within > .input-group-text,
        .input-group:focus-within > .btn,
        .input-group:focus-within > .btn-outline-secondary,
        .input-group:focus-within > .toggle-password-btn {
            border-color: var(--accent) !important;
        }

        .input-group:focus-within > .form-control:focus,
        .input-group:focus-within > .form-select:focus {
            box-shadow: none !important;
        }

        input[type="color"].form-control-color {
            width: 46px !important;
            min-width: 46px !important;
            max-width: 46px !important;
            padding: 5px !important;
            cursor: pointer !important;
            flex: 0 0 46px !important;
            height: auto !important;
            background-color: var(--surface-2) !important;
            border: 1px solid var(--border-color) !important;
            box-sizing: border-box !important;
        }

        input[type="color"].form-control-color::-webkit-color-swatch-wrapper {
            padding: 0 !important;
        }

        input[type="color"].form-control-color::-webkit-color-swatch {
            border: none !important;
            border-radius: 6px !important;
        }

        input[type="color"].form-control-color::-moz-color-swatch {
            border: none !important;
            border-radius: 6px !important;
        }

        .form-label {
            font-size: 0.82rem !important;
            font-weight: 600 !important;
            color: var(--text-muted) !important;
            margin-bottom: 6px !important;
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: rgba(255, 255, 255, 0.04) !important;
            color: var(--text-main) !important;
            border-color: var(--border-color) !important;
        }

        [data-bs-theme="dark"] .text-dark {
            color: var(--text-main) !important;
        }

        [data-bs-theme="dark"] .border {
            border-color: var(--border-color) !important;
        }

        @media (min-width: 992px) {
            .app-shell {
                position: relative;
                min-height: 100vh;
            }

            .sidebar {
                position: fixed !important;
                top: 0 !important;
                bottom: 0 !important;
                left: 0 !important;
                width: var(--sidebar-w) !important;
                height: 100vh !important;
                z-index: 1040 !important;
                background: var(--surface-2) !important;
                border-right: 1px solid var(--border-color) !important;
                box-shadow: none !important;
                overflow-y: auto !important;
                overflow-x: hidden !important;
                transition: width 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease !important;
            }

            .content {
                margin-left: var(--sidebar-w) !important;
                padding-top: var(--topbar-h) !important;
                width: calc(100% - var(--sidebar-w)) !important;
                min-height: 100vh !important;
                transition: margin-left 0.25s cubic-bezier(0.16, 1, 0.3, 1), width 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }

            .topbar {
                position: fixed !important;
                top: 0 !important;
                left: var(--sidebar-w) !important;
                right: 0 !important;
                height: var(--topbar-h) !important;
                width: calc(100% - var(--sidebar-w)) !important;
                z-index: 1030 !important;
                background: var(--surface) !important;
                backdrop-filter: blur(16px) saturate(180%) !important;
                -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
                border-bottom: 1px solid var(--border-color) !important;
                padding: 0 24px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                transition: left 0.25s cubic-bezier(0.16, 1, 0.3, 1), width 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }

            .sidebar .sidebar-close-btn {
                display: none !important;
            }

            .app-shell.sidebar-collapsed .sidebar {
                width: var(--sidebar-mini-w) !important;
                overflow: hidden !important;
            }

            .app-shell.sidebar-collapsed .content {
                margin-left: var(--sidebar-mini-w) !important;
                width: calc(100% - var(--sidebar-mini-w)) !important;
            }

            .app-shell.sidebar-collapsed .topbar {
                left: var(--sidebar-mini-w) !important;
                width: calc(100% - var(--sidebar-mini-w)) !important;
            }

            .app-shell.sidebar-collapsed .sidebar .sidebar-brand {
                justify-content: center !important;
                padding: 0 !important;
            }

            .app-shell.sidebar-collapsed .sidebar .brand-link {
                justify-content: center !important;
                flex-grow: 0 !important;
            }

            .app-shell.sidebar-collapsed .sidebar .brand-meta,
            .app-shell.sidebar-collapsed .sidebar .brand-text {
                display: none !important;
            }

            .app-shell.sidebar-collapsed .sidebar .nav-group {
                padding: 4px 0 !important;
                border: none !important;
                background: transparent !important;
                margin-bottom: 6px !important;
            }

            .app-shell.sidebar-collapsed .sidebar .nav-group-header {
                display: none !important;
            }

            .app-shell.sidebar-collapsed .sidebar .nav-linkx {
                justify-content: center !important;
                padding: 10px 0 !important;
            }

            .app-shell.sidebar-collapsed .sidebar .nav-linkx .nav-text {
                display: none !important;
            }

            .app-shell.sidebar-collapsed .sidebar .nav-linkx .badge {
                display: none !important;
            }

            .app-shell.sidebar-collapsed .sidebar:hover {
                width: var(--sidebar-w) !important;
                box-shadow: 12px 0 40px rgba(0, 0, 0, 0.5) !important;
                z-index: 1050 !important;
                overflow-y: auto !important;
            }

            .app-shell.sidebar-collapsed .sidebar:hover .sidebar-brand {
                justify-content: space-between !important;
                padding: 0 16px !important;
            }

            .app-shell.sidebar-collapsed .sidebar:hover .brand-link {
                justify-content: flex-start !important;
                flex-grow: 1 !important;
            }

            .app-shell.sidebar-collapsed .sidebar:hover .brand-meta {
                display: flex !important;
            }

            .app-shell.sidebar-collapsed .sidebar:hover .brand-text {
                display: block !important;
            }

            .app-shell.sidebar-collapsed .sidebar:hover .nav-group {
                padding: 6px !important;
                border: 1px solid var(--border-color) !important;
                background: rgba(255, 255, 255, 0.02) !important;
                margin-bottom: 12px !important;
            }

            .app-shell.sidebar-collapsed .sidebar:hover .nav-group-header {
                display: flex !important;
            }

            .app-shell.sidebar-collapsed .sidebar:hover .nav-linkx {
                justify-content: flex-start !important;
                padding: 8px 12px !important;
            }

            .app-shell.sidebar-collapsed .sidebar:hover .nav-linkx .nav-text {
                display: inline-block !important;
            }

            .app-shell.sidebar-collapsed .sidebar:hover .badge {
                display: inline-block !important;
            }
        }

        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed !important;
                top: 0 !important;
                bottom: 0 !important;
                left: 0 !important;
                width: 280px !important;
                max-width: 85vw !important;
                height: 100vh !important;
                transform: translateX(-100%) !important;
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
                z-index: 1060 !important;
                background: var(--surface-2) !important;
                border-right: 1px solid var(--border-color) !important;
                box-shadow: 12px 0 35px rgba(0, 0, 0, 0.3) !important;
                overflow-y: auto !important;
            }

            .sidebar.mobile-open {
                transform: translateX(0) !important;
            }

            .content, .topbar {
                left: 0 !important;
                right: 0 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                width: 100% !important;
            }

            .content {
                padding-top: calc(var(--topbar-h) + 12px) !important;
            }

            .topbar {
                position: fixed !important;
                top: 0 !important;
                height: var(--topbar-h) !important;
                z-index: 1030 !important;
                background: var(--surface) !important;
                backdrop-filter: blur(16px) saturate(180%) !important;
                -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
                border-bottom: 1px solid var(--border-color) !important;
                padding: 0 16px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
        }

        .panel, .user-panel {
            position: fixed !important;
            top: 0 !important;
            height: 100vh !important;
            background: var(--surface-2) !important;
            border-left: 1px solid var(--border-color) !important;
            box-shadow: -18px 0 40px rgba(0, 0, 0, 0.35) !important;
            display: flex !important;
            flex-direction: column !important;
            transition: right 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
            z-index: 1065 !important;
        }

        .panel {
            right: -450px !important;
            width: 420px !important;
            max-width: 90vw !important;
        }

        .panel.open {
            right: 0 !important;
        }

        .user-panel {
            right: -420px !important;
            width: 380px !important;
            max-width: 90vw !important;
            z-index: 1070 !important;
        }

        .user-panel.open {
            right: 0 !important;
        }

        .overlay {
            position: fixed !important;
            inset: 0 !important;
            background-color: rgba(15, 23, 42, 0.5) !important;
            backdrop-filter: blur(8px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(8px) saturate(180%) !important;
            z-index: 1060 !important;
            display: none !important;
        }

        .overlay.show {
            display: block !important;
        }

        .admin-backdrop-overlay {
            position: fixed !important;
            inset: 0 !important;
            background: rgba(15, 23, 42, 0.6) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            z-index: 1055 !important;
            display: none !important;
            opacity: 0 !important;
            transition: opacity 0.25s ease !important;
        }

        .admin-backdrop-overlay.active {
            display: block !important;
            opacity: 1 !important;
        }

        .smart-toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 400px;
            width: calc(100% - 36px);
            pointer-events: none;
        }

        .smart-toast {
            pointer-events: auto;
            position: relative;
            display: flex;
            flex-direction: column;
            border-radius: 14px;
            background: var(--surface-2);
            border: 1px solid var(--border-color);
            box-shadow: 0 16px 36px -6px rgba(0, 0, 0, 0.35);
            color: var(--text-main);
            overflow: hidden;
            transform: translateX(120%) scale(0.95);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .smart-toast.show {
            transform: translateX(0) scale(1);
            opacity: 1;
        }

        .smart-toast-inner {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 16px;
        }

        .smart-toast-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .smart-toast-success .smart-toast-icon {
            background: rgba(16, 185, 129, 0.16);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.28);
        }

        .smart-toast-error .smart-toast-icon,
        .smart-toast-danger .smart-toast-icon {
            background: rgba(239, 68, 68, 0.16);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.28);
        }

        .smart-toast-warning .smart-toast-icon {
            background: rgba(245, 158, 11, 0.16);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.28);
        }

        .smart-toast-info .smart-toast-icon {
            background: rgba(14, 165, 233, 0.16);
            color: #0ea5e9;
            border: 1px solid rgba(14, 165, 233, 0.28);
        }

        .smart-toast-content {
            flex: 1 1 auto;
            min-width: 0;
            padding-top: 1px;
        }

        .smart-toast-title {
            font-weight: 700;
            font-size: 0.88rem;
            color: var(--text-main);
            margin-bottom: 2px;
            line-height: 1.25;
        }

        .smart-toast-message {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.4;
            word-break: break-word;
        }

        .smart-toast-close {
            flex-shrink: 0;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            margin-left: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.15s ease;
        }

        .smart-toast-close:hover {
            color: var(--text-main);
            background: rgba(148, 163, 184, 0.15);
        }

        .smart-toast-progress {
            height: 3px;
            width: 100%;
            background: rgba(148, 163, 184, 0.2);
            overflow: hidden;
        }

        .smart-toast-bar {
            height: 100%;
            width: 100%;
            transform-origin: left;
            transition: width linear;
        }

        .smart-toast-success .smart-toast-bar { background: linear-gradient(90deg, #10b981, #34d399); }
        .smart-toast-error .smart-toast-bar,
        .smart-toast-danger .smart-toast-bar { background: linear-gradient(90deg, #ef4444, #f87171); }
        .smart-toast-warning .smart-toast-bar { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .smart-toast-info .smart-toast-bar { background: linear-gradient(90deg, #0ea5e9, #38bdf8); }

        .smart-confirm-wrapper {
            position: fixed;
            inset: 0;
            z-index: 1000000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .smart-confirm-wrapper.active {
            display: flex;
            opacity: 1;
        }

        .smart-confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1;
        }

        .smart-confirm-dialog {
            position: relative;
            z-index: 2;
            background: var(--surface-2);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 420px;
            padding: 24px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            transform: scale(0.92) translateY(12px);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .smart-confirm-wrapper.active .smart-confirm-dialog {
            transform: scale(1) translateY(0);
        }

        .smart-confirm-icon-box {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 16px;
            background: rgba(239, 68, 68, 0.14);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }

        .smart-confirm-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .smart-confirm-text {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-bottom: 22px;
            line-height: 1.45;
        }

        .smart-confirm-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
        }

        .smart-confirm-actions .btn {
            flex: 1;
        }

        .modal {
            --bs-modal-bg: var(--surface-2);
            --bs-modal-border-color: var(--border-color);
            --bs-modal-color: var(--text-main);
        }

        .modal-dialog {
            max-height: calc(100vh - 40px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin: 1.25rem auto;
        }

        .modal-dialog > form {
            display: flex !important;
            flex-direction: column !important;
            max-height: calc(100vh - 40px) !important;
            height: 100% !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        .modal-content {
            background: var(--surface-2) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 16px !important;
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.45) !important;
            max-height: calc(100vh - 50px) !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            position: relative;
        }

        .modal-content > form,
        .admin-modal-dialog > form {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            max-height: 100% !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        .modal-header,
        .admin-modal-header,
        .modal-content > form > .modal-header {
            flex-shrink: 0 !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 10 !important;
            background: var(--surface-2) !important;
            border-bottom: 1px solid var(--border-color) !important;
            padding: 16px 20px !important;
        }

        .modal-body,
        .admin-modal-body,
        .modal-content > form > .modal-body,
        .modal-content form .modal-body {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
            min-height: 0 !important;
            padding: 20px !important;
            color: var(--text-main) !important;
            scrollbar-width: thin;
        }

        .modal-footer,
        .admin-modal-footer,
        .modal-content > form > .modal-footer,
        .modal-content form .modal-footer {
            flex-shrink: 0 !important;
            position: sticky !important;
            bottom: 0 !important;
            z-index: 10 !important;
            background: var(--surface-2) !important;
            border-top: 1px solid var(--border-color) !important;
            padding: 14px 20px !important;
        }

        .admin-modal-wrapper {
            position: fixed;
            inset: 0;
            z-index: 1070;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .admin-modal-wrapper.active {
            display: flex;
            opacity: 1;
        }

        .admin-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 1;
        }

        .admin-modal-dialog {
            position: relative;
            z-index: 2;
            background: var(--surface-2);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 580px;
            max-height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
            transform: scale(0.94) translateY(10px);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        .admin-modal-dialog.modal-sm {
            max-width: 440px;
        }

        .admin-modal-dialog.modal-md {
            max-width: 580px;
        }

        .admin-modal-dialog.modal-lg {
            max-width: 820px;
        }

        .admin-modal-dialog.modal-xl {
            max-width: 1040px;
        }

        .admin-modal-wrapper.active .admin-modal-dialog {
            transform: scale(1) translateY(0);
        }

        .admin-modal-title-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-modal-header-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(var(--accent-rgb), 0.12);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .admin-modal-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-main);
        }

        .admin-modal-close-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--surface);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .admin-modal-close-btn:hover {
            background: var(--surface-2);
            color: var(--text-main);
            border-color: var(--accent);
        }

        .opt-report-hero {
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: all 0.25s ease;
        }

        [data-bs-theme="dark"] .opt-report-hero {
            background: radial-gradient(circle at 100% 0%, rgba(16, 185, 129, 0.16) 0%, transparent 60%), linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(16, 185, 129, 0.08) 100%);
            border-color: rgba(16, 185, 129, 0.3);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
        }

        [data-bs-theme="light"] .opt-report-hero {
            background: radial-gradient(circle at 100% 0%, rgba(16, 185, 129, 0.12) 0%, transparent 60%), linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%);
            border-color: rgba(16, 185, 129, 0.3);
            box-shadow: 0 4px 16px -2px rgba(16, 185, 129, 0.12);
        }

        .opt-report-card {
            border-radius: 12px;
            padding: 1rem 1.15rem;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        [data-bs-theme="dark"] .opt-report-card {
            background: rgba(15, 23, 42, 0.6);
            border-color: #1e293b;
        }

        [data-bs-theme="dark"] .opt-report-card:hover {
            border-color: rgba(16, 185, 129, 0.45);
            background: rgba(30, 41, 59, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
        }

        [data-bs-theme="light"] .opt-report-card {
            background: #ffffff;
            border-color: #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        [data-bs-theme="light"] .opt-report-card:hover {
            border-color: rgba(16, 185, 129, 0.45);
            box-shadow: 0 6px 16px -2px rgba(16, 185, 129, 0.12);
            transform: translateY(-2px);
        }

        .opt-report-track {
            height: 6px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(148, 163, 184, 0.25);
        }

        [data-bs-theme="dark"] .opt-report-track {
            background: rgba(255, 255, 255, 0.08);
        }

        .form-check.form-switch {
            min-height: 1.6rem;
            display: flex;
            align-items: center;
        }

        .form-check.form-switch:not(.ps-0):not(.justify-content-between) {
            padding-left: 3.4rem !important;
        }

        .form-check.form-switch:not(.ps-0):not(.justify-content-between) .form-check-input {
            margin-left: -3.4rem !important;
        }

        .form-check.form-switch.ps-0 .form-check-input,
        .form-check.form-switch.justify-content-between .form-check-input {
            margin-left: auto !important;
            margin-right: 0 !important;
        }

        .form-check.form-switch .form-check-input,
        .form-switch .form-check-input,
        input[type="checkbox"].form-check-input[role="switch"],
        .form-check-input[role="switch"] {
            width: 2.85rem !important;
            height: 1.55rem !important;
            margin-top: 0 !important;
            border-radius: 999px !important;
            cursor: pointer !important;
            position: relative !important;
            border: 1.5px solid rgba(148, 163, 184, 0.35) !important;
            background-color: rgba(51, 65, 85, 0.65) !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-6 -6 12 12'%3e%3ccircle cx='0' cy='0.9' r='4.3' fill='rgba%280,0,0,0.35%29'/%3e%3ccircle cx='0' cy='0' r='4.2' fill='%23ffffff'/%3e%3c/svg%3e") !important;
            background-position: left 2.5px center !important;
            background-size: 1.25rem 1.25rem !important;
            background-repeat: no-repeat !important;
            transition: background-position 0.28s cubic-bezier(0.16, 1, 0.3, 1),
                        background-color 0.25s ease,
                        background-image 0.25s ease,
                        border-color 0.25s ease,
                        box-shadow 0.25s ease,
                        transform 0.15s ease !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3) !important;
            flex-shrink: 0 !important;
        }

        .form-check.form-switch .form-check-input:hover,
        .form-switch .form-check-input:hover {
            border-color: rgba(148, 163, 184, 0.55) !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.25), 0 0 10px rgba(148, 163, 184, 0.2) !important;
        }

        .form-check.form-switch .form-check-input:active,
        .form-switch .form-check-input:active {
            transform: scale(0.94) !important;
        }

        .form-check.form-switch .form-check-input:focus,
        .form-switch .form-check-input:focus {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb, 79, 70, 229), 0.25), 0 0 14px rgba(59, 130, 246, 0.35) !important;
            border-color: rgba(99, 102, 241, 0.6) !important;
        }

        .form-check.form-switch .form-check-input:checked,
        .form-switch .form-check-input:checked,
        input[type="checkbox"].form-check-input[role="switch"]:checked,
        .form-check-input[role="switch"]:checked {
            background-color: var(--accent, #3b82f6) !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-6 -6 12 12'%3e%3ccircle cx='0' cy='0.9' r='4.3' fill='rgba%280,0,0,0.28%29'/%3e%3ccircle cx='0' cy='0' r='4.2' fill='%23ffffff'/%3e%3ccircle cx='0' cy='-0.8' r='3.2' fill='rgba%28255,255,255,0.32%29'/%3e%3c/svg%3e"), linear-gradient(135deg, var(--accent, #4f46e5) 0%, #3b82f6 55%, #2563eb 100%) !important;
            background-position: right 2.5px center, center !important;
            background-size: 1.25rem 1.25rem, auto !important;
            background-repeat: no-repeat, no-repeat !important;
            border-color: rgba(99, 102, 241, 0.55) !important;
            box-shadow: 0 0 16px rgba(var(--accent-rgb, 59, 130, 246), 0.5), 0 2px 6px rgba(15, 23, 42, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.3) !important;
        }

        .form-check.form-switch .form-check-input:checked:hover,
        .form-switch .form-check-input:checked:hover {
            box-shadow: 0 0 20px rgba(var(--accent-rgb, 59, 130, 246), 0.7), 0 2px 8px rgba(15, 23, 42, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.4) !important;
            filter: brightness(1.08) !important;
        }

        [data-bs-theme="light"] .form-check.form-switch .form-check-input,
        [data-bs-theme="light"] .form-switch .form-check-input,
        [data-theme="light"] .form-check.form-switch .form-check-input,
        [data-theme="light"] .form-switch .form-check-input {
            background-color: #cbd5e1 !important;
            border-color: #94a3b8 !important;
            box-shadow: inset 0 2px 4px rgba(15, 23, 42, 0.08) !important;
        }

        .form-check.form-switch .form-check-label,
        .form-switch .form-check-label {
            cursor: pointer !important;
            user-select: none !important;
            transition: color 0.2s ease !important;
        }

        .form-check:not(.form-switch) .form-check-input[type="checkbox"],
        .form-check-input[type="checkbox"]:not([role="switch"]):not(.form-switch .form-check-input) {
            width: 1.25rem !important;
            height: 1.25rem !important;
            border-radius: 6px !important;
            border: 1.5px solid rgba(148, 163, 184, 0.35) !important;
            background-color: rgba(30, 41, 59, 0.7) !important;
            cursor: pointer !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            margin-top: 0.15rem;
        }

        [data-bs-theme="light"] .form-check:not(.form-switch) .form-check-input[type="checkbox"],
        [data-bs-theme="light"] .form-check-input[type="checkbox"]:not([role="switch"]):not(.form-switch .form-check-input),
        [data-theme="light"] .form-check:not(.form-switch) .form-check-input[type="checkbox"],
        [data-theme="light"] .form-check-input[type="checkbox"]:not([role="switch"]):not(.form-switch .form-check-input) {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }

        .form-check:not(.form-switch) .form-check-input[type="checkbox"]:hover,
        .form-check-input[type="checkbox"]:not([role="switch"]):not(.form-switch .form-check-input):hover {
            border-color: var(--accent, #4f46e5) !important;
            box-shadow: 0 0 8px rgba(var(--accent-rgb, 79, 70, 229), 0.25) !important;
        }

        .form-check:not(.form-switch) .form-check-input[type="checkbox"]:active,
        .form-check-input[type="checkbox"]:not([role="switch"]):not(.form-switch .form-check-input):active {
            transform: scale(0.92) !important;
        }

        .form-check:not(.form-switch) .form-check-input[type="checkbox"]:focus,
        .form-check-input[type="checkbox"]:not([role="switch"]):not(.form-switch .form-check-input):focus {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb, 79, 70, 229), 0.25) !important;
            border-color: var(--accent, #4f46e5) !important;
        }

        .form-check:not(.form-switch) .form-check-input[type="checkbox"]:checked,
        .form-check-input[type="checkbox"]:not([role="switch"]):not(.form-switch .form-check-input):checked {
            background-color: var(--accent, #4f46e5) !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M4 10.5l4 4L16 6'/%3e%3c/svg%3e"), linear-gradient(135deg, var(--accent, #4f46e5) 0%, #3b82f6 100%) !important;
            border-color: var(--accent, #4f46e5) !important;
            box-shadow: 0 0 10px rgba(var(--accent-rgb, 79, 70, 229), 0.45) !important;
        }

        .form-check-input[type="radio"] {
            width: 1.25rem !important;
            height: 1.25rem !important;
            border-radius: 50% !important;
            border: 1.5px solid rgba(148, 163, 184, 0.35) !important;
            background-color: rgba(30, 41, 59, 0.7) !important;
            cursor: pointer !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        [data-bs-theme="light"] .form-check-input[type="radio"],
        [data-theme="light"] .form-check-input[type="radio"] {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }

        .form-check-input[type="radio"]:hover {
            border-color: var(--accent, #4f46e5) !important;
            box-shadow: 0 0 8px rgba(var(--accent-rgb, 79, 70, 229), 0.25) !important;
        }

        .form-check-input[type="radio"]:active {
            transform: scale(0.92) !important;
        }

        .form-check-input[type="radio"]:focus {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb, 79, 70, 229), 0.25) !important;
            border-color: var(--accent, #4f46e5) !important;
        }

        .table-responsive {
            -webkit-overflow-scrolling: touch !important;
            overflow-x: auto !important;
            width: 100% !important;
        }

        .app-shell, .content {
            max-width: 100vw !important;
        }

        @media (max-width: 991.98px) {
            .content {
                overflow-x: hidden !important;
            }
            .sidebar {
                transform: translateX(-100%) !important;
                width: 285px !important;
                max-width: 85vw !important;
            }
            .sidebar.mobile-open {
                transform: translateX(0) !important;
                z-index: 9999 !important;
                box-shadow: 0 0 40px rgba(0, 0, 0, 0.5) !important;
            }
            .admin-sidebar-backdrop {
                position: fixed !important;
                inset: 0 !important;
                background: rgba(15, 23, 42, 0.6) !important;
                backdrop-filter: blur(4px) !important;
                -webkit-backdrop-filter: blur(4px) !important;
                z-index: 9998 !important;
                opacity: 0 !important;
                visibility: hidden !important;
                transition: opacity 0.25s ease, visibility 0.25s ease !important;
            }
            .admin-sidebar-backdrop.active {
                opacity: 1 !important;
                visibility: visible !important;
            }
        }

        @media (max-width: 767.98px) {
            .topbar-store-btn {
                display: none !important;
            }
            .form-control,
            .form-select,
            .select2-container--default .select2-selection--single,
            .select2-container--default .select2-selection--multiple {
                font-size: 16px !important;
            }
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                width: 100% !important;
                float: none !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                margin-bottom: 0.5rem !important;
                height: auto !important;
                padding: 0 !important;
            }
            .dataTables_wrapper .dataTables_filter {
                margin-top: 0.5rem !important;
            }
            .dataTables_wrapper .dataTables_filter input {
                width: 100% !important;
                max-width: 100% !important;
                margin-left: 0 !important;
            }
            .dataTables_wrapper .dataTables_paginate {
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                gap: 4px !important;
                margin-top: 0.75rem !important;
            }
            .panel, .user-panel {
                width: min(380px, 92vw) !important;
                max-width: 92vw !important;
            }
        }

        @media (max-width: 575.98px) {
            .topbar {
                padding: 0 0.5rem !important;
            }
            .topbar-action-btn {
                width: 36px !important;
                height: 36px !important;
                min-width: 36px !important;
            }
            .topbar-studio-btn {
                width: 36px !important;
                height: 36px !important;
                padding: 0 !important;
                justify-content: center !important;
            }
            .topbar-user-card {
                padding: 0 !important;
                background: transparent !important;
                border: none !important;
            }
            .main {
                padding: 0.85rem 0.5rem !important;
            }
            .modal-dialog {
                margin: 0.5rem !important;
                max-width: calc(100vw - 1rem) !important;
            }
            .modal-body {
                padding: 1rem !important;
            }
            .modal-header, .modal-footer {
                padding: 0.75rem 1rem !important;
            }
        }
    </style>
    @yield('css')
    @stack('styles')
</head>
<body>
<div class="app-shell" id="appShell">
    <script>
        if (localStorage.getItem('sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
            document.getElementById('appShell').classList.add('sidebar-collapsed');
        }
    </script>
    <div class="admin-backdrop-overlay" id="adminSidebarBackdrop" onclick="closeAdminSidebar()"></div>
    <div class="overlay" id="overlay" onclick="closeAllPanels()"></div>

    @include('backend.inc.sidebar')
    @include('backend.inc.header')

    <main class="content">
        <div class="main">
            @yield('content')
        </div>
    </main>

    @include('backend.inc.theme')
    @include('backend.inc.userPanel')
</div>

<div class="admin-modal-wrapper" id="adminGlobalModal">
    <div class="admin-modal-overlay" onclick="closeGlobalModal()"></div>
    <div class="admin-modal-dialog" id="adminGlobalModalDialog">
        <div class="admin-modal-header">
            <div class="admin-modal-title-group">
                <div class="admin-modal-header-icon" id="adminGlobalModalIcon">
                    <i class="fa-solid fa-cube"></i>
                </div>
                <h5 class="admin-modal-title" id="adminGlobalModalTitle">Quick Action</h5>
            </div>
            <button type="button" class="admin-modal-close-btn" onclick="closeGlobalModal()" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="admin-modal-body" id="adminGlobalModalBody"></div>
        <div class="admin-modal-footer" id="adminGlobalModalFooter"></div>
    </div>
</div>

<div class="smart-toast-container" id="adminToastContainer"></div>

<div class="smart-confirm-wrapper" id="smartConfirmModal" aria-hidden="true">
    <div class="smart-confirm-overlay" onclick="window.closeSmartConfirm(false)"></div>
    <div class="smart-confirm-dialog" role="dialog" aria-modal="true">
        <div class="smart-confirm-icon-box" id="smartConfirmIcon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="smart-confirm-title" id="smartConfirmTitle">Are you sure?</div>
        <div class="smart-confirm-text" id="smartConfirmText">You will not be able to revert this!</div>
        <div class="smart-confirm-actions">
            <button type="button" class="btn btn-outline-secondary" id="smartConfirmCancelBtn" onclick="window.closeSmartConfirm(false)">Cancel</button>
            <button type="button" class="btn btn-danger" id="smartConfirmOkBtn" onclick="window.closeSmartConfirm(true)">Confirm</button>
        </div>
    </div>
</div>

<script src="{{ asset('backend/lib/jquery-3.7.1.min.js') }}" data-turbo-track="reload" data-turbo-eval="false"></script>
<script src="{{ asset('backend/lib/bootstrap.bundle.min.js') }}" data-turbo-track="reload" data-turbo-eval="false"></script>
<script src="{{ asset('backend/lib/select2.min.js') }}" data-turbo-track="reload" data-turbo-eval="false"></script>
<script src="{{ asset('backend/lib/jquery.dataTables.min.js') }}" data-turbo-track="reload" data-turbo-eval="false"></script>
<script src="{{ asset('backend/lib/template.js') }}" data-turbo-track="reload" data-turbo-eval="false"></script>
<script src="{{ asset('lib/axios.min.js') }}?v=1.7.9" data-turbo-track="reload" data-turbo-eval="false"></script>
<script src="{{ asset('backend/js/admin-shell.js') }}" data-turbo-track="reload" data-turbo-eval="false"></script>

<script data-turbo-eval="false">
(() => {
    if (typeof axios !== 'undefined' && !window.__axiosConfigured) {
        window.__axiosConfigured = true;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        
        axios.interceptors.request.use(function (config) {
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                config.headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
            }
            return config;
        });

        axios.interceptors.response.use(function (response) {
            var method = (response.config.method || '').toUpperCase();
            if (['POST', 'PUT', 'PATCH', 'DELETE'].indexOf(method) !== -1) {
                if (response.data && response.data.message && response.config.autoToast) {
                    window.showToast(response.data.message, 'success');
                }
            }
            return response;
        }, function (error) {
            if (error.config && error.config.skipToast) {
                return Promise.reject(error);
            }
            var status = error.response ? error.response.status : null;
            var data = error.response ? error.response.data : null;
            if (status === 422 && data) {
                if (data.errors) {
                    var errorList = [];
                    for (var key in data.errors) {
                        if (data.errors.hasOwnProperty(key)) {
                            errorList.push(data.errors[key].join(', '));
                        }
                    }
                    window.showToast(errorList.join('<br>'), 'error', 5000);
                } else if (data.message) {
                    window.showToast(data.message, 'error');
                }
            } else if (status === 419) {
                window.showToast('Session expired. Please refresh the page.', 'warning');
            } else if (status === 401 || status === 403) {
                window.showToast((data && data.message) ? data.message : 'Access denied.', 'error');
            } else if (status >= 500) {
                window.showToast('Internal server error. Please try again.', 'error');
            }
            return Promise.reject(error);
        });
    }

    if (typeof jQuery !== 'undefined') {
        $(document).on('processing.dt', function (e, settings, processing) {
            var wrapper = $(settings.nTableWrapper);
            var table = $(settings.nTable);
            if (processing) {
                wrapper.addClass('dt-is-processing');
                table.addClass('dt-table-processing');
            } else {
                wrapper.removeClass('dt-is-processing');
                table.removeClass('dt-table-processing');
            }
        });
    }

    function updateHeaderThemeIcon(theme) {
        var icon = document.getElementById('headerThemeIcon');
        if (!icon) return;
        if (theme === 'dark') {
            icon.className = 'fa-solid fa-sun topbar-theme-icon-sun';
        } else {
            icon.className = 'fa-solid fa-moon topbar-theme-icon-moon';
        }
    }

    window.showOptimizationReportModal = function(data) {
        if (!data || !data.items) return;

        var isDark = document.documentElement.getAttribute('data-bs-theme') !== 'light';

        var colorMap = {
            'Configuration Tree': {
                color: isDark ? '#c084fc' : '#7e22ce',
                bg: isDark ? 'rgba(168, 85, 247, 0.16)' : 'rgba(147, 51, 234, 0.09)',
                border: isDark ? 'rgba(168, 85, 247, 0.35)' : 'rgba(147, 51, 234, 0.25)',
                grad: 'linear-gradient(90deg, #9333ea, #c084fc)'
            },
            'Route Matrix': {
                color: isDark ? '#38bdf8' : '#0284c7',
                bg: isDark ? 'rgba(6, 182, 212, 0.16)' : 'rgba(2, 132, 199, 0.09)',
                border: isDark ? 'rgba(6, 182, 212, 0.35)' : 'rgba(2, 132, 199, 0.25)',
                grad: 'linear-gradient(90deg, #0284c7, #38bdf8)'
            },
            'Blade Views': {
                color: isDark ? '#fbbf24' : '#b45309',
                bg: isDark ? 'rgba(245, 158, 11, 0.16)' : 'rgba(217, 119, 6, 0.09)',
                border: isDark ? 'rgba(245, 158, 11, 0.35)' : 'rgba(217, 119, 6, 0.25)',
                grad: 'linear-gradient(90deg, #d97706, #fbbf24)'
            },
            'Event Registry': {
                color: isDark ? '#818cf8' : '#4338ca',
                bg: isDark ? 'rgba(99, 102, 241, 0.16)' : 'rgba(79, 70, 229, 0.09)',
                border: isDark ? 'rgba(99, 102, 241, 0.35)' : 'rgba(79, 70, 229, 0.25)',
                grad: 'linear-gradient(90deg, #4f46e5, #818cf8)'
            },
            'Application Cache': {
                color: isDark ? '#34d399' : '#047857',
                bg: isDark ? 'rgba(16, 185, 129, 0.16)' : 'rgba(16, 185, 129, 0.09)',
                border: isDark ? 'rgba(16, 185, 129, 0.35)' : 'rgba(16, 185, 129, 0.25)',
                grad: 'linear-gradient(90deg, #059669, #34d399)'
            },
            'OPcache & Realpath': {
                color: isDark ? '#f472b6' : '#be185d',
                bg: isDark ? 'rgba(236, 72, 153, 0.16)' : 'rgba(219, 39, 119, 0.09)',
                border: isDark ? 'rgba(236, 72, 153, 0.35)' : 'rgba(219, 39, 119, 0.25)',
                grad: 'linear-gradient(90deg, #db2777, #f472b6)'
            },
            'Execution Speedup': {
                color: isDark ? '#60a5fa' : '#1d4ed8',
                bg: isDark ? 'rgba(59, 130, 246, 0.16)' : 'rgba(37, 99, 235, 0.09)',
                border: isDark ? 'rgba(59, 130, 246, 0.35)' : 'rgba(37, 99, 235, 0.25)',
                grad: 'linear-gradient(90deg, #2563eb, #60a5fa)'
            },
            'Memory Compacted': {
                color: isDark ? '#2dd4bf' : '#0f766e',
                bg: isDark ? 'rgba(20, 184, 166, 0.16)' : 'rgba(13, 148, 136, 0.09)',
                border: isDark ? 'rgba(20, 184, 166, 0.35)' : 'rgba(13, 148, 136, 0.25)',
                grad: 'linear-gradient(90deg, #0d9488, #2dd4bf)'
            }
        };

        var itemsHtml = '';
        data.items.forEach(function(item) {
            var cfg = colorMap[item.title] || {
                color: isDark ? '#34d399' : '#047857',
                bg: isDark ? 'rgba(16, 185, 129, 0.16)' : 'rgba(16, 185, 129, 0.09)',
                border: isDark ? 'rgba(16, 185, 129, 0.35)' : 'rgba(16, 185, 129, 0.25)',
                grad: 'linear-gradient(90deg, #059669, #34d399)'
            };
            var numMatch = String(item.improvement).match(/[\d.]+/);
            var barWidth = numMatch ? Math.min(100, Math.max(15, parseFloat(numMatch[0]))) : 100;

            itemsHtml +=
                '<div class="col-12 col-md-6">' +
                    '<div class="opt-report-card">' +
                        '<div>' +
                            '<div class="d-flex align-items-center justify-content-between gap-2 mb-2">' +
                                '<div class="d-flex align-items-center min-w-0" style="gap: 13px;">' +
                                    '<div style="width: 40px; height: 40px; border-radius: 10px; background: ' + cfg.bg + '; color: ' + cfg.color + '; border: 1px solid ' + cfg.border + '; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0;">' +
                                        '<i class="fa-solid ' + item.icon + '"></i>' +
                                    '</div>' +
                                    '<div class="min-w-0 d-flex flex-column justify-content-center">' +
                                        '<div class="fw-bold text-body text-truncate" style="font-size: 0.88rem; line-height: 1.15; margin-bottom: 2px;">' + item.title + '</div>' +
                                        '<div class="text-secondary font-monospace text-truncate" style="font-size: 0.72rem; line-height: 1.05;">' + item.command + '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<span class="badge rounded-pill px-2.5 py-1 fw-bold font-monospace flex-shrink-0" style="background: ' + cfg.bg + '; color: ' + cfg.color + '; border: 1px solid ' + cfg.border + '; font-size: 0.8rem;">' +
                                    item.improvement +
                                '</span>' +
                            '</div>' +
                            '<div class="text-secondary mb-2" style="font-size: 0.75rem; line-height: 1.4;">' +
                                item.metric +
                            '</div>' +
                        '</div>' +
                        '<div class="opt-report-track">' +
                            '<div class="progress-bar rounded-pill" role="progressbar" style="height: 100%; width: ' + barWidth + '%; background: ' + cfg.grad + '; transition: width 0.6s ease;"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        });

        var html =
            '<div class="d-flex flex-column gap-3">' +
                '<div class="opt-report-hero d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">' +
                    '<div class="d-flex align-items-center gap-3">' +
                        '<div class="d-flex align-items-center justify-content-center rounded-3 text-white flex-shrink-0 shadow-sm" style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">' +
                            '<i class="fa-solid fa-bolt-lightning fs-4"></i>' +
                        '</div>' +
                        '<div>' +
                            '<div class="d-flex align-items-center gap-2 flex-wrap">' +
                                '<h5 class="fw-bold mb-0 text-body">System Fully Optimized</h5>' +
                                '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 small">' +
                                    '<i class="fa-solid fa-check me-1"></i> Synchronized' +
                                '</span>' +
                            '</div>' +
                            '<div class="text-secondary small mt-1">' +
                                'Flushed &amp; compiled in <strong class="text-body font-monospace">' + data.duration_ms + 'ms</strong> &bull; All buffers synchronized' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="d-flex flex-row flex-sm-column align-items-center align-items-sm-end justify-content-between gap-1.5 flex-shrink-0">' +
                        '<div class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill fw-bold font-monospace shadow-sm" style="background: rgba(16, 185, 129, 0.16); border: 1px solid rgba(16, 185, 129, 0.35); color: ' + (isDark ? '#34d399' : '#047857') + '; font-size: 0.92rem;">' +
                            '<i class="fa-solid fa-arrow-trend-up"></i>' +
                            '<span>+' + data.overall_gain_pct + '% Net Boost</span>' +
                        '</div>' +
                        '<div class="d-flex align-items-center gap-1.5 text-secondary" style="font-size: 0.74rem;">' +
                            '<span class="d-inline-block rounded-circle bg-success" style="width: 7px; height: 7px; box-shadow: 0 0 8px #10b981;"></span>' +
                            '<span>Health: <strong class="text-success fw-bold">100% Prime</strong></span>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="row g-3">' +
                    itemsHtml +
                '</div>' +
            '</div>';

        var footerHtml =
            '<div class="d-flex align-items-center gap-2 text-secondary small me-auto" style="font-size: 0.78rem;">' +
                '<i class="fa-solid fa-shield-halved text-success"></i>' +
                '<span>Verified Security &bull; Audit Log Recorded</span>' +
            '</div>' +
            '<button type="button" class="btn btn-outline-secondary px-3.5 py-2 fw-semibold rounded-3" onclick="closeGlobalModal()">' +
                'Close' +
            '</button>' +
            (window.location.pathname.indexOf('/admin/system/info') !== -1
                ? '<button type="button" class="btn btn-primary px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2 rounded-3" onclick="window.location.reload()"><i class="fa-solid fa-rotate-right"></i><span>Refresh Telemetry</span></button>'
                : '');

        window.openGlobalModal({
            title: 'Performance & Optimization Report',
            icon: 'fa-gauge-high',
            size: 'lg',
            html: html,
            footer: footerHtml
        });
    };

    window.purgeSystemCache = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var btn = document.getElementById('headerClearCacheBtn');
        var icon = document.getElementById('headerClearCacheIcon');
        if (btn && btn.disabled) return;
        if (btn) btn.disabled = true;
        if (icon) icon.className = 'fa-solid fa-spinner fa-spin';

        axios.post('{{ route('admin.system.clear_cache') }}')
        .then(function(res) {
            var data = res.data;
            if (icon) icon.className = 'fa-solid fa-check text-success';
            if (typeof window.showAdminToast === 'function') {
                window.showAdminToast('success', data.message || 'System cache purged successfully!');
            }
            window.showOptimizationReportModal(data);
            setTimeout(function() {
                if (btn) btn.disabled = false;
                if (icon) icon.className = 'fa-solid fa-broom';
            }, 1800);
        })
        .catch(function(err) {
            if (icon) icon.className = 'fa-solid fa-triangle-exclamation text-danger';
            if (typeof window.showAdminToast === 'function') {
                window.showAdminToast('danger', 'Failed to purge cache.');
            }
            setTimeout(function() {
                if (btn) btn.disabled = false;
                if (icon) icon.className = 'fa-solid fa-broom';
            }, 2000);
        });
    };

    window.toggleAdminFullscreen = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var icon = document.getElementById('adminFullscreenIcon');
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().then(function() {
                if (icon) icon.className = 'fa-solid fa-compress';
            }).catch(function() {});
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen().then(function() {
                    if (icon) icon.className = 'fa-solid fa-expand';
                }).catch(function() {});
            }
        }
    };
    document.addEventListener('fullscreenchange', function() {
        var icon = document.getElementById('adminFullscreenIcon');
        if (!icon) return;
        icon.className = document.fullscreenElement ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
    });

    function toggleHeaderTheme(e) {
        if (e) {
            if (typeof e.preventDefault === 'function') e.preventDefault();
            if (typeof e.stopPropagation === 'function') e.stopPropagation();
        }
        var current = document.documentElement.getAttribute('data-bs-theme') || 'dark';
        var next = current === 'dark' ? 'light' : 'dark';
        if (typeof window.setTheme === 'function') {
            window.setTheme(next);
        } else {
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
        }
        if (typeof updateHeaderThemeIcon === 'function') {
            updateHeaderThemeIcon(next);
        }
    }
    window.toggleHeaderTheme = toggleHeaderTheme;
    window.toggleThemeQuick = toggleHeaderTheme;

    function toggleSidebar(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const appShell = document.getElementById('appShell');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('adminSidebarBackdrop');

        if (window.innerWidth < 992) {
            if (!sidebar) return;
            const isOpen = sidebar.classList.contains('mobile-open');
            if (isOpen) {
                sidebar.classList.remove('mobile-open');
                if (backdrop) backdrop.classList.remove('active');
                document.body.style.overflow = '';
            } else {
                sidebar.classList.add('mobile-open');
                if (backdrop) backdrop.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        } else {
            if (!appShell) return;
            appShell.classList.toggle('sidebar-collapsed');
            const isCollapsed = appShell.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
            localStorage.setItem('sidebar-state', isCollapsed ? 'collapsed' : 'open');
        }
    }

    function closeAdminSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('adminSidebarBackdrop');
        if (sidebar) sidebar.classList.remove('mobile-open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            closeAdminSidebar();
        }
    });

    window.smartToast = function(arg1, arg2, arg3) {
        var options = {};
        if (typeof arg1 === 'object' && arg1 !== null) {
            options = Object.assign({}, arg1);
        } else if (typeof arg1 === 'string' && (arg1 === 'success' || arg1 === 'error' || arg1 === 'danger' || arg1 === 'warning' || arg1 === 'info')) {
            options.type = arg1;
            options.message = arg2;
            options.duration = arg3;
        } else {
            options.message = arg1;
            options.type = arg2;
            options.duration = arg3;
        }

        var message = options.message || options.text || options.html || '';
        if (!message) return;
        var cleanMessage = String(message).trim();

        var type = (options.type || options.icon || 'success').toLowerCase();
        if (type === 'danger') type = 'error';
        if (['success', 'error', 'warning', 'info'].indexOf(type) === -1) type = 'info';

        var duration = typeof options.duration === 'number' ? options.duration : (typeof options.timer === 'number' ? options.timer : 4000);
        var title = options.title || null;

        var container = document.getElementById('adminToastContainer');
        if (!container) return;

        var now = Date.now();
        if (window.__lastAdminToastMsg === cleanMessage && (now - (window.__lastAdminToastAt || 0) < 1800)) {
            return;
        }
        window.__lastAdminToastMsg = cleanMessage;
        window.__lastAdminToastAt = now;

        var existingToasts = container.querySelectorAll('.smart-toast');
        for (var i = 0; i < existingToasts.length; i++) {
            var msgEl = existingToasts[i].querySelector('.smart-toast-message');
            if (msgEl && msgEl.innerHTML.trim() === cleanMessage) {
                return;
            }
        }

        if (existingToasts.length >= 4) {
            for (var k = 0; k <= existingToasts.length - 4; k++) {
                if (existingToasts[k] && existingToasts[k].parentNode) {
                    existingToasts[k].parentNode.removeChild(existingToasts[k]);
                }
            }
        }

        var iconMap = {
            success: 'fa-circle-check',
            error: 'fa-circle-xmark',
            warning: 'fa-triangle-exclamation',
            info: 'fa-circle-info'
        };
        var defaultTitleMap = {
            success: 'Success',
            error: 'Action Failed',
            warning: 'Attention',
            info: 'Notice'
        };

        var displayTitle = title !== null ? title : defaultTitleMap[type];

        var toast = document.createElement('div');
        toast.className = 'smart-toast smart-toast-' + type;

        var titleHtml = displayTitle ? '<div class="smart-toast-title">' + displayTitle + '</div>' : '';
        toast.innerHTML =
            '<div class="smart-toast-inner">' +
                '<div class="smart-toast-icon"><i class="fa-solid ' + (iconMap[type] || 'fa-circle-info') + '"></i></div>' +
                '<div class="smart-toast-content">' +
                    titleHtml +
                    '<div class="smart-toast-message">' + message + '</div>' +
                '</div>' +
                '<button type="button" class="smart-toast-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>' +
            '</div>' +
            '<div class="smart-toast-progress"><div class="smart-toast-bar" style="width: 100%;"></div></div>';

        container.appendChild(toast);
        requestAnimationFrame(function () {
            toast.classList.add('show');
        });

        var bar = toast.querySelector('.smart-toast-bar');
        var remaining = duration;
        var start = Date.now();
        var timerId = null;
        var isPaused = false;

        function startTimer() {
            start = Date.now();
            if (bar) {
                bar.style.transition = 'width ' + remaining + 'ms linear';
                bar.style.width = '0%';
            }
            timerId = setTimeout(dismiss, remaining);
        }

        function pauseTimer() {
            if (isPaused) return;
            isPaused = true;
            clearTimeout(timerId);
            var elapsed = Date.now() - start;
            remaining = Math.max(0, remaining - elapsed);
            if (bar) {
                var computed = window.getComputedStyle(bar);
                var curWidth = computed.getPropertyValue('width');
                bar.style.transition = 'none';
                bar.style.width = curWidth;
            }
        }

        function resumeTimer() {
            if (!isPaused) return;
            isPaused = false;
            startTimer();
        }

        function dismiss() {
            clearTimeout(timerId);
            toast.classList.remove('show');
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        }

        var closeBtn = toast.querySelector('.smart-toast-close');
        if (closeBtn) closeBtn.addEventListener('click', dismiss);

        toast.addEventListener('mouseenter', pauseTimer);
        toast.addEventListener('mouseleave', resumeTimer);

        if (duration > 0) {
            startTimer();
        }
    };

    window.showToast = window.smartToast;
    window.showAdminToast = window.smartToast;

    var __smartConfirmResolver = null;

    window.smartConfirm = function(arg1, arg2, arg3) {
        var options = {};
        if (typeof arg1 === 'object' && arg1 !== null) {
            options = Object.assign({}, arg1);
        } else {
            options.title = arg1;
            options.text = arg2;
            options.icon = arg3;
        }

        var modal = document.getElementById('smartConfirmModal');
        if (!modal) {
            var nativeResult = window.confirm((options.title ? options.title + '\n' : '') + (options.text || ''));
            return Promise.resolve({ isConfirmed: nativeResult, isDenied: false, isDismissed: !nativeResult, value: nativeResult });
        }

        var titleEl = document.getElementById('smartConfirmTitle');
        var textEl = document.getElementById('smartConfirmText');
        var iconEl = document.getElementById('smartConfirmIcon');
        var okBtn = document.getElementById('smartConfirmOkBtn');
        var cancelBtn = document.getElementById('smartConfirmCancelBtn');

        if (titleEl) titleEl.textContent = options.title || 'Are you sure?';
        if (textEl) {
            if (options.html) {
                textEl.innerHTML = options.html;
            } else {
                textEl.textContent = options.text || "You will not be able to revert this!";
            }
        }

        var iconType = (options.icon || 'warning').toLowerCase();
        if (iconType === 'danger') iconType = 'error';

        if (iconEl) {
            var iconClassMap = {
                warning: 'fa-triangle-exclamation',
                error: 'fa-circle-xmark',
                success: 'fa-circle-check',
                info: 'fa-circle-info'
            };
            var iconColorMap = {
                warning: { bg: 'rgba(245, 158, 11, 0.14)', color: '#f59e0b', border: 'rgba(245, 158, 11, 0.28)' },
                error: { bg: 'rgba(239, 68, 68, 0.14)', color: '#ef4444', border: 'rgba(239, 68, 68, 0.28)' },
                success: { bg: 'rgba(16, 185, 129, 0.14)', color: '#10b981', border: 'rgba(16, 185, 129, 0.28)' },
                info: { bg: 'rgba(14, 165, 233, 0.14)', color: '#0ea5e9', border: 'rgba(14, 165, 233, 0.28)' }
            };
            var currentStyle = iconColorMap[iconType] || iconColorMap.warning;
            iconEl.style.background = currentStyle.bg;
            iconEl.style.color = currentStyle.color;
            iconEl.style.borderColor = currentStyle.border;
            iconEl.innerHTML = '<i class="fa-solid ' + (iconClassMap[iconType] || 'fa-triangle-exclamation') + '"></i>';
        }

        if (okBtn) {
            okBtn.textContent = options.confirmButtonText || 'Confirm';
            if (iconType === 'info') {
                okBtn.className = 'btn btn-primary';
            } else if (iconType === 'success') {
                okBtn.className = 'btn btn-success';
            } else {
                okBtn.className = 'btn btn-danger';
            }
        }
        if (cancelBtn) {
            cancelBtn.textContent = options.cancelButtonText || 'Cancel';
            cancelBtn.style.display = options.showCancelButton === false ? 'none' : 'inline-block';
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        return new Promise(function(resolve) {
            __smartConfirmResolver = resolve;
        });
    };

    window.closeSmartConfirm = function(confirmed) {
        var modal = document.getElementById('smartConfirmModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
        if (typeof __smartConfirmResolver === 'function') {
            var resolve = __smartConfirmResolver;
            __smartConfirmResolver = null;
            resolve({
                isConfirmed: !!confirmed,
                isDenied: false,
                isDismissed: !confirmed,
                value: !!confirmed
            });
        }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var confirmModal = document.getElementById('smartConfirmModal');
            if (confirmModal && confirmModal.classList.contains('active')) {
                window.closeSmartConfirm(false);
            }
        }
    });

    window.alert = function(msg) {
        window.smartToast({
            type: 'error',
            message: msg
        });
    };

    var smartSwal = {
        fire: function() {
            var args = arguments;
            var options = {};
            if (typeof args[0] === 'object' && args[0] !== null) {
                options = Object.assign({}, args[0]);
            } else if (typeof args[0] === 'string') {
                options.title = args[0];
                if (typeof args[1] === 'string') options.text = args[1];
                if (typeof args[2] === 'string') options.icon = args[2];
            }

            var isConfirm = !!(options.showCancelButton || options.showDenyButton || (options.buttons && options.buttons.length > 1));

            if (isConfirm) {
                return window.smartConfirm(options);
            } else {
                var type = options.icon || 'info';
                var msg = options.text || options.html || options.title || '';
                var title = (options.text || options.html) ? options.title : null;
                var duration = typeof options.timer === 'number' ? options.timer : 3500;
                window.smartToast({
                    type: type,
                    title: title,
                    message: msg,
                    duration: duration
                });
                return Promise.resolve({
                    isConfirmed: true,
                    isDenied: false,
                    isDismissed: false,
                    value: true
                });
            }
        },
        close: function() {
            window.closeSmartConfirm(false);
        },
        showLoading: function() {},
        hideLoading: function() {},
        enableButtons: function() {},
        disableButtons: function() {},
        isVisible: function() {
            var el = document.getElementById('smartConfirmModal');
            return el ? el.classList.contains('active') : false;
        },
        getConfirmButton: function() {
            return document.getElementById('smartConfirmOkBtn');
        },
        getCancelButton: function() {
            return document.getElementById('smartConfirmCancelBtn');
        }
    };

    window.Swal = smartSwal;
    window.swal = smartSwal;
    try {
        Object.defineProperty(window, 'Swal', {
            get: function() { return smartSwal; },
            set: function() {},
            configurable: true
        });
        Object.defineProperty(window, 'swal', {
            get: function() { return smartSwal; },
            set: function() {},
            configurable: true
        });
    } catch(e) {}

    window.openGlobalModal = function(options) {
        options = options || {};
        var modalWrapper = document.getElementById('adminGlobalModal');
        var modalDialog = document.getElementById('adminGlobalModalDialog');
        var modalTitle = document.getElementById('adminGlobalModalTitle');
        var modalIcon = document.getElementById('adminGlobalModalIcon');
        var modalBody = document.getElementById('adminGlobalModalBody');
        var modalFooter = document.getElementById('adminGlobalModalFooter');

        if (!modalWrapper) return;

        modalDialog.className = 'admin-modal-dialog ' + (options.size ? 'modal-' + options.size : 'modal-md');
        modalTitle.textContent = options.title || 'Quick Action';
        modalIcon.innerHTML = '<i class="fa-solid ' + (options.icon || 'fa-cube') + '"></i>';

        if (options.footer) {
            modalFooter.innerHTML = options.footer;
            modalFooter.style.display = 'flex';
        } else {
            modalFooter.innerHTML = '';
            modalFooter.style.display = 'none';
        }

        if (options.url) {
            modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
            modalWrapper.classList.add('active');
            document.body.style.overflow = 'hidden';

            axios.get(options.url, { skipToast: true }).then(function(res) {
                modalBody.innerHTML = res.data;
                bindModalForms(modalBody, options);
                if (typeof options.onLoaded === 'function') options.onLoaded(modalBody);
            }).catch(function() {
                modalBody.innerHTML = '<div class="alert alert-danger mb-0">Failed to load content. Please try again.</div>';
            });
        } else {
            modalBody.innerHTML = options.html || '';
            bindModalForms(modalBody, options);
            modalWrapper.classList.add('active');
            document.body.style.overflow = 'hidden';
            if (typeof options.onLoaded === 'function') options.onLoaded(modalBody);
        }
    };

    window.closeGlobalModal = function() {
        var modalWrapper = document.getElementById('adminGlobalModal');
        if (modalWrapper) {
            modalWrapper.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    function bindModalForms(container, modalOptions) {
        var forms = container.querySelectorAll('form');
        forms.forEach(function(form) {
            if (form.getAttribute('data-bound') === 'true') return;
            form.setAttribute('data-bound', 'true');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                window.submitModalForm(form, modalOptions);
            });
        });
    }

    window.submitModalForm = function(form, options) {
        options = options || {};
        var submitBtn = form.querySelector('button[type="submit"]');
        var origHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Processing...';
        }

        var formData = new FormData(form);
        var method = (form.getAttribute('method') || 'POST').toUpperCase();
        var action = form.getAttribute('action');
        var spoofedMethod = formData.get('_method');
        if (spoofedMethod) method = spoofedMethod.toUpperCase();

        axios({
            method: method,
            url: action,
            data: formData
        }).then(function(res) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = origHtml;
            }
            if (options.closeOnSuccess !== false) {
                window.closeGlobalModal();
            }
            var refreshTarget = form.getAttribute('data-refresh-target') || options.refreshTarget;
            var refreshUrl = form.getAttribute('data-refresh-url') || options.refreshUrl;
            if (refreshTarget && refreshUrl) {
                axios.get(refreshUrl, { skipToast: true }).then(function(targetRes) {
                    var el = document.querySelector(refreshTarget);
                    if (el) el.innerHTML = targetRes.data;
                });
            }
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
                var dt = window.jQuery('.dataTable').DataTable();
                if (dt && typeof dt.ajax !== 'undefined' && typeof dt.ajax.reload === 'function') {
                    dt.ajax.reload(null, false);
                }
            }
            if (typeof options.onSuccess === 'function') options.onSuccess(res);
        }).catch(function() {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = origHtml;
            }
        });
    };

    function initAdminGlobalComponents() {
        document.documentElement.style.overflow = '';
        document.documentElement.style.touchAction = '';
        document.body.style.overflow = '';
        document.body.style.touchAction = '';
        document.body.classList.remove('modal-open', 'overflow-hidden');
        document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach(function(el) {
            el.remove();
        });

        var saved = localStorage.getItem('theme') || 'dark';
        if (typeof updateHeaderThemeIcon === 'function') {
            updateHeaderThemeIcon(saved);
        }

        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                bootstrap.Tooltip.getOrCreateInstance(el);
            });
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el) {
                bootstrap.Popover.getOrCreateInstance(el);
            });
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(el) {
                bootstrap.Dropdown.getOrCreateInstance(el);
            });
        }

        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            window.jQuery('select.select2').each(function() {
                if (!window.jQuery(this).data('select2')) {
                    window.jQuery(this).select2({
                        width: '100%',
                        placeholder: window.jQuery(this).attr('placeholder') || 'Select an option',
                        allowClear: true
                    });
                }
            });
        }

        if (localStorage.getItem('sidebar_collapsed') === 'true' && window.innerWidth >= 992) {
            var shell = document.getElementById('appShell');
            if (shell) shell.classList.add('sidebar-collapsed');
        }
    }

    document.addEventListener('turbo:click', function() {
        if (document.activeElement && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
        }
        document.documentElement.style.overflow = '';
        document.documentElement.style.touchAction = '';
        document.body.style.overflow = '';
        document.body.style.touchAction = '';
        document.body.classList.remove('modal-open', 'overflow-hidden');
    });

    document.addEventListener('turbo:load', initAdminGlobalComponents);

    document.addEventListener('turbo:before-render', function() {
        if (document.activeElement && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
        }
        window.closeGlobalModal();
        closeAdminSidebar();
        if (typeof window.closeAllPanels === 'function') {
            window.closeAllPanels();
        }
        document.querySelectorAll('.modal.show, .offcanvas.show').forEach(function(el) {
            el.classList.remove('show');
        });
        document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach(function(el) {
            el.remove();
        });
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                var tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.dispose();
            });
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el) {
                var pop = bootstrap.Popover.getInstance(el);
                if (pop) pop.dispose();
            });
            document.querySelectorAll('.tooltip, .popover').forEach(function(el) {
                el.remove();
            });
        }
        document.documentElement.removeAttribute('style');
        document.body.classList.remove('modal-open', 'overflow-hidden');
        document.body.removeAttribute('style');
        if (window.Swal && typeof Swal.isVisible === 'function' && Swal.isVisible()) {
            Swal.close();
        }
    });

    document.addEventListener('turbo:before-cache', function() {
        if (document.activeElement && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
        }
        window.closeGlobalModal();
        closeAdminSidebar();
        if (typeof window.closeAllPanels === 'function') {
            window.closeAllPanels();
        }
        document.querySelectorAll('.modal.show, .offcanvas.show').forEach(function(el) {
            el.classList.remove('show');
        });
        document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach(function(el) {
            el.remove();
        });
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                var tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.dispose();
            });
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el) {
                var pop = bootstrap.Popover.getInstance(el);
                if (pop) pop.dispose();
            });
            document.querySelectorAll('.tooltip, .popover').forEach(function(el) {
                el.remove();
            });
        }
        document.documentElement.removeAttribute('style');
        document.body.classList.remove('modal-open', 'overflow-hidden');
        document.body.removeAttribute('style');
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            window.jQuery('select.select2').each(function() {
                if (window.jQuery(this).data('select2')) {
                    window.jQuery(this).select2('destroy');
                }
            });
        }
        if (window.Swal && typeof Swal.isVisible === 'function' && Swal.isVisible()) {
            Swal.close();
        }
    });

    document.addEventListener('click', function(e) {
        var trigger = e.target.closest('[data-modal-open="true"], [data-admin-modal="true"]');
        if (trigger) {
            e.preventDefault();
            window.openGlobalModal({
                title: trigger.getAttribute('data-modal-title') || trigger.getAttribute('title') || 'Action',
                icon: trigger.getAttribute('data-modal-icon') || 'fa-cube',
                size: trigger.getAttribute('data-modal-size') || 'md',
                url: trigger.getAttribute('data-modal-url') || trigger.getAttribute('href'),
                refreshTarget: trigger.getAttribute('data-refresh-target'),
                refreshUrl: trigger.getAttribute('data-refresh-url')
            });
        }
    });

    document.addEventListener('submit', function(e) {
        var form = e.target.closest('form[data-async-form="true"]');
        if (form && !form.closest('#adminGlobalModalBody')) {
            e.preventDefault();
            window.submitModalForm(form);
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeGlobalModal();
            window.closeAdminSidebar();
            if (typeof window.closeAllPanels === 'function') {
                window.closeAllPanels();
            }
        }
    });

    window.initAdminGlobalComponents = initAdminGlobalComponents;
    window.updateHeaderThemeIcon = updateHeaderThemeIcon;
    window.toggleHeaderTheme = toggleHeaderTheme;
    window.toggleThemeQuick = toggleHeaderTheme;
    window.toggleSidebar = toggleSidebar;
    window.toggleAdminSidebar = toggleSidebar;
    window.closeAdminSidebar = closeAdminSidebar;
    window.toggleModuleGroup = function(groupId) {
        var group = document.getElementById(groupId);
        if (group) group.classList.toggle('open');
    };
    window.togglePanel = function(e) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();
        var userPanel = document.getElementById("userPanel");
        var sidebar = document.getElementById("sidebar");
        var studioPanel = document.getElementById("panel");
        if (userPanel) userPanel.classList.remove("open");
        if (sidebar) sidebar.classList.remove("mobile-open");
        if (studioPanel) studioPanel.classList.toggle("open");
        var overlay = document.getElementById("overlay");
        var isOpen = studioPanel && studioPanel.classList.contains("open");
        if (overlay) overlay.classList.toggle("show", isOpen);
        document.body.classList.toggle("no-scroll", isOpen);
    };
    window.toggleUserPanel = function(e) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();
        var studioPanel = document.getElementById("panel");
        var sidebar = document.getElementById("sidebar");
        var userPanel = document.getElementById("userPanel");
        if (studioPanel) studioPanel.classList.remove("open");
        if (sidebar) sidebar.classList.remove("mobile-open");
        if (userPanel) userPanel.classList.toggle("open");
        var overlay = document.getElementById("overlay");
        var isOpen = userPanel && userPanel.classList.contains("open");
        if (overlay) overlay.classList.toggle("show", isOpen);
        document.body.classList.toggle("no-scroll", isOpen);
    };
    window.closeAllPanels = function() {
        var sidebar = document.getElementById("sidebar");
        var studioPanel = document.getElementById("panel");
        var userPanel = document.getElementById("userPanel");
        if (sidebar) sidebar.classList.remove("mobile-open");
        if (studioPanel) studioPanel.classList.remove("open");
        if (userPanel) userPanel.classList.remove("open");
        var overlay = document.getElementById("overlay");
        if (overlay) overlay.classList.remove("show");
        document.body.classList.remove("no-scroll");
    };
})();
</script>

@yield('js')
@stack('scripts')
</body>
</html>