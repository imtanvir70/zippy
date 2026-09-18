@extends('backend.layouts.app')

@section('title', 'Customer Orders Management')

@push('styles')
<style>
    .text-accent {
        color: var(--accent) !important;
    }

    .bg-accent {
        background-color: var(--accent) !important;
    }

    .order-pipeline-bar {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .pipeline-tab-btn {
        flex: 1;
        min-width: 140px;
        padding: 0.65rem 1rem;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background: var(--surface-2);
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .pipeline-tab-btn .badge {
        background-color: var(--surface) !important;
        color: var(--text-muted) !important;
        border: 1px solid var(--border-color) !important;
        font-size: 0.72rem;
        transition: all 0.2s ease;
    }

    .pipeline-tab-btn:hover {
        background: rgba(var(--accent-rgb), 0.06);
        border-color: rgba(var(--accent-rgb), 0.35);
        color: var(--text-main);
    }

    .pipeline-tab-btn.active {
        background: rgba(var(--accent-rgb), 0.12);
        color: var(--accent);
        border-color: var(--accent);
        box-shadow: 0 2px 10px rgba(var(--accent-rgb), 0.2);
        font-weight: 700;
    }

    .pipeline-tab-btn.active .badge {
        background-color: var(--accent) !important;
        color: #ffffff !important;
        border-color: transparent !important;
    }

    .bulk-action-bar {
        background: var(--surface-2);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 0.65rem 1.15rem;
        display: none;
        align-items: center;
        gap: 0.75rem;
        box-shadow: var(--card-shadow);
        color: var(--text-main);
    }

    .admin-modal-wrapper {
        position: fixed;
        inset: 0;
        z-index: 1070;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
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
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 1;
    }

    [data-bs-theme="dark"] .admin-modal-overlay {
        background: rgba(0, 0, 0, 0.82);
    }

    .admin-modal-dialog {
        position: relative;
        z-index: 2;
        background: var(--surface-2) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 16px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45) !important;
        color: var(--text-main) !important;
        transform: scale(0.96) translateY(8px);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    .admin-modal-wrapper.active .admin-modal-dialog {
        transform: scale(1) translateY(0);
    }

    .admin-modal-dialog.modal-xl {
        max-width: 1180px !important;
        width: 96vw !important;
        height: 88vh !important;
        max-height: 860px !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .admin-modal-dialog.modal-lg {
        max-width: 880px !important;
        width: 94vw !important;
        height: 84vh !important;
        max-height: 820px !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .admin-modal-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        padding: 0.9rem 1.35rem !important;
        border-bottom: 1px solid var(--border-color) !important;
        background: var(--surface-2) !important;
        color: var(--text-main) !important;
        flex-shrink: 0 !important;
    }

    .admin-modal-header-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(var(--accent-rgb), 0.12);
        color: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        border: 1px solid rgba(var(--accent-rgb), 0.2);
    }

    .admin-modal-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-main) !important;
        letter-spacing: -0.01em;
    }

    .admin-modal-close-btn {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        border: 1px solid var(--border-color);
        background: var(--surface);
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .admin-modal-close-btn:hover {
        background: var(--surface-2);
        color: var(--text-main);
        border-color: var(--accent);
        transform: scale(1.04);
    }

    .admin-modal-body {
        flex: 1 1 0 !important;
        min-height: 0 !important;
        overflow: hidden !important;
        padding: 0.9rem 1.1rem !important;
        display: flex !important;
        flex-direction: column !important;
        background: var(--surface) !important;
        color: var(--text-main) !important;
    }

    .admin-modal-footer {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        padding: 0.85rem 1.35rem !important;
        border-top: 1px solid var(--border-color) !important;
        background: var(--surface-2) !important;
        color: var(--text-main) !important;
        flex-shrink: 0 !important;
    }

    .admin-modal-wrapper input.form-control,
    .admin-modal-wrapper select.form-select,
    .admin-modal-wrapper .form-control,
    .admin-modal-wrapper .form-select,
    .admin-modal-dialog input.form-control,
    .admin-modal-dialog select.form-select,
    .admin-modal-dialog .form-control,
    .admin-modal-dialog .form-select {
        min-height: 36px !important;
        height: 36px !important;
        max-height: 36px !important;
        padding: 0.35rem 0.75rem !important;
        font-size: 0.84rem !important;
        line-height: 1.4 !important;
        border-radius: 8px !important;
        background-color: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        box-shadow: none !important;
    }

    .admin-modal-wrapper input.form-control:focus,
    .admin-modal-wrapper select.form-select:focus,
    .admin-modal-dialog input.form-control:focus,
    .admin-modal-dialog select.form-select:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 2.5px rgba(var(--accent-rgb), 0.18) !important;
    }

    .admin-modal-wrapper textarea.form-control,
    .admin-modal-dialog textarea.form-control {
        min-height: 52px !important;
        height: 52px !important;
        max-height: 70px !important;
        padding: 0.4rem 0.75rem !important;
        font-size: 0.84rem !important;
        line-height: 1.35 !important;
        resize: none !important;
        border-radius: 8px !important;
        background-color: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
    }

    .admin-modal-wrapper .input-group,
    .admin-modal-dialog .input-group {
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
    }

    .admin-modal-wrapper .input-group .form-control,
    .admin-modal-dialog .input-group .form-control {
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
    }

    .admin-modal-wrapper .input-group .btn,
    .admin-modal-dialog .input-group .btn,
    .admin-modal-wrapper .input-group .input-group-text,
    .admin-modal-dialog .input-group .input-group-text {
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
        padding: 0 0.75rem !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .admin-modal-dialog .form-label {
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        color: var(--text-muted) !important;
        margin-bottom: 4px !important;
        display: block !important;
    }

    .edit-modal-layout, .view-modal-layout {
        display: grid !important;
        grid-template-columns: 395px 1fr !important;
        gap: 14px !important;
        height: 100% !important;
        min-height: 0 !important;
        overflow: hidden !important;
    }

    .panel-left-side, .panel-right-side {
        background: var(--surface-2) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 14px !important;
        height: 100% !important;
        min-height: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
    }

    .panel-header {
        padding: 0.75rem 1rem !important;
        background: var(--surface) !important;
        border-bottom: 1px solid var(--border-color) !important;
        flex-shrink: 0 !important;
        color: var(--text-main) !important;
    }

    .panel-scroll-content {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overflow-y: auto !important;
        padding: 0.9rem !important;
    }

    .panel-sticky-footer {
        flex-shrink: 0 !important;
        padding: 0.75rem 0.95rem !important;
        background: var(--surface) !important;
        border-top: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
    }

    .product-search-box {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
    }

    .product-search-box .search-icon {
        position: absolute !important;
        left: 11px !important;
        color: var(--text-muted) !important;
        font-size: 0.82rem !important;
        pointer-events: none !important;
    }

    .product-search-box input {
        width: 100% !important;
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
        border-radius: 8px !important;
        padding-left: 32px !important;
        padding-right: 12px !important;
        background: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        font-size: 0.84rem !important;
        color: var(--text-main) !important;
    }

    .product-search-box input:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 2px rgba(var(--accent-rgb), 0.18) !important;
    }

    .product-row-card {
        background: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 12px !important;
        padding: 0.65rem 0.85rem !important;
        margin-bottom: 0.55rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        width: 100% !important;
        box-sizing: border-box !important;
        color: var(--text-main) !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease !important;
    }

    .product-row-card:hover {
        border-color: rgba(var(--accent-rgb), 0.35) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }

    .product-thumb-img {
        width: 50px !important;
        height: 50px !important;
        border-radius: 9px !important;
        border: 1px solid var(--border-color) !important;
        background: var(--surface-2) !important;
        object-fit: cover !important;
        flex-shrink: 0 !important;
    }

    .qty-stepper {
        display: inline-flex !important;
        align-items: center !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 8px !important;
        background: var(--surface-2) !important;
        height: 30px !important;
        overflow: hidden !important;
        flex-shrink: 0 !important;
    }

    .qty-stepper button {
        width: 28px !important;
        height: 30px !important;
        border: none !important;
        background: transparent !important;
        color: var(--text-muted) !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        padding: 0 !important;
        transition: background 0.15s ease, color 0.15s ease !important;
    }

    .qty-stepper button:hover {
        background: rgba(var(--accent-rgb), 0.14) !important;
        color: var(--accent) !important;
    }

    .qty-stepper input {
        width: 32px !important;
        height: 30px !important;
        min-height: 30px !important;
        max-height: 30px !important;
        border: none !important;
        background: transparent !important;
        text-align: center !important;
        font-family: monospace !important;
        font-weight: 700 !important;
        font-size: 0.88rem !important;
        color: var(--text-main) !important;
        padding: 0 !important;
        pointer-events: none !important;
    }

    .item-delete-btn {
        width: 30px !important;
        height: 30px !important;
        border-radius: 8px !important;
        border: 1px solid rgba(239, 68, 68, 0.25) !important;
        background: rgba(239, 68, 68, 0.08) !important;
        color: #ef4444 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        padding: 0 !important;
        transition: all 0.15s ease !important;
    }

    .item-delete-btn:hover {
        background: #ef4444 !important;
        color: #ffffff !important;
        border-color: #ef4444 !important;
    }

    .billing-summary-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 8px !important;
        width: 100% !important;
    }

    .summary-tile {
        background: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 10px !important;
        padding: 6px 8px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: center !important;
        min-height: 54px !important;
        height: 54px !important;
        text-align: center !important;
        box-sizing: border-box !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }

    .summary-tile-label {
        font-size: 0.62rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        color: var(--text-muted) !important;
        margin-bottom: 2px !important;
        line-height: 1 !important;
        white-space: nowrap !important;
    }

    .summary-tile-value {
        font-size: 0.95rem !important;
        font-weight: 800 !important;
        font-family: monospace !important;
        color: var(--text-main) !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
    }

    .summary-tile-editable:focus-within {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 2px rgba(var(--accent-rgb), 0.18) !important;
    }

    .summary-tile-input-box {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 22px !important;
        gap: 2px !important;
    }

    .summary-tile-input-box .currency-sign {
        font-size: 0.76rem !important;
        font-family: monospace !important;
        font-weight: 700 !important;
        color: var(--text-muted) !important;
    }

    .summary-inline-input {
        width: 58px !important;
        height: 22px !important;
        min-height: 22px !important;
        max-height: 22px !important;
        border: none !important;
        background: transparent !important;
        text-align: center !important;
        font-family: monospace !important;
        font-weight: 800 !important;
        font-size: 0.92rem !important;
        color: var(--text-main) !important;
        padding: 0 !important;
        outline: none !important;
        box-shadow: none !important;
        -moz-appearance: textfield !important;
    }

    .summary-inline-input::-webkit-outer-spin-button,
    .summary-inline-input::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0 !important;
    }

    .summary-tile-total {
        background: linear-gradient(135deg, rgba(var(--accent-rgb), 0.14), rgba(var(--accent-rgb), 0.04)) !important;
        border-color: rgba(var(--accent-rgb), 0.35) !important;
    }

    .summary-tile-total .summary-tile-label {
        color: var(--accent) !important;
        font-weight: 800 !important;
    }

    .summary-tile-total .summary-tile-value {
        color: var(--accent) !important;
        font-size: 1rem !important;
    }

    .product-search-box {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
    }

    .product-search-box .search-icon {
        position: absolute !important;
        left: 14px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: var(--text-muted) !important;
        font-size: 0.85rem !important;
        pointer-events: none !important;
        z-index: 5 !important;
        transition: color 0.15s ease !important;
    }

    .product-search-box:focus-within .search-icon {
        color: var(--accent) !important;
    }

    .product-search-box input.form-control {
        padding-left: 38px !important;
        padding-right: 14px !important;
        height: 40px !important;
        background: var(--surface) !important;
        border: 1.5px solid var(--border-color) !important;
        border-radius: 9px !important;
        color: var(--text-main) !important;
        font-size: 0.86rem !important;
        font-weight: 500 !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    }

    .product-search-box input.form-control:focus {
        border-color: var(--accent) !important;
        background: var(--surface-2) !important;
        box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.18) !important;
        color: var(--text-main) !important;
    }

    .product-search-box input.form-control::placeholder {
        color: var(--text-muted) !important;
        font-weight: 400 !important;
        opacity: 0.85 !important;
    }

    .search-product-dropdown {
        position: absolute !important;
        top: calc(100% + 6px) !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1080 !important;
        background: var(--surface-2) !important;
        border: 1.5px solid var(--border-color) !important;
        border-radius: 12px !important;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.28) !important;
        max-height: 260px !important;
        overflow-y: auto !important;
        display: none;
        backdrop-filter: blur(8px) !important;
    }

    .search-product-item {
        padding: 10px 14px !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        background: var(--surface-2) !important;
        border-bottom: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        transition: all 0.15s ease !important;
    }

    .search-product-item:last-child {
        border-bottom: none !important;
    }

    .search-product-item:hover {
        background: rgba(var(--accent-rgb), 0.1) !important;
    }

    .fraud-status-card {
        background: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 10px !important;
        padding: 0.75rem 0.9rem !important;
        margin-top: 0.55rem !important;
        margin-bottom: 0.55rem !important;
    }

    .info-block-card {
        background: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 10px !important;
        padding: 0.75rem 0.9rem !important;
    }

    .instruction-tag {
        font-size: 0.72rem !important;
        font-weight: 500 !important;
        padding: 0.25rem 0.6rem !important;
        border-radius: 7px !important;
        border: 1px solid var(--border-color) !important;
        background: var(--surface) !important;
        color: var(--text-muted) !important;
        cursor: pointer !important;
        user-select: none !important;
        transition: all 0.15s ease !important;
    }

    .instruction-tag:hover {
        background: var(--surface-2) !important;
        color: var(--text-main) !important;
        border-color: rgba(var(--accent-rgb), 0.4) !important;
    }

    .instruction-tag.active {
        background: var(--accent) !important;
        border-color: var(--accent) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 6px rgba(var(--accent-rgb), 0.25) !important;
    }

    .zone-preset-btn {
        flex: 1;
        font-size: 0.72rem !important;
        font-weight: 600 !important;
        padding: 0.35rem 0.5rem !important;
        border-radius: 7px !important;
        border: 1px solid var(--border-color) !important;
        background: var(--surface) !important;
        color: var(--text-muted) !important;
        text-align: center !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        white-space: nowrap !important;
    }

    .zone-preset-btn:hover {
        background: var(--surface-2) !important;
        color: var(--text-main) !important;
        border-color: rgba(var(--accent-rgb), 0.4) !important;
    }

    .zone-preset-btn.active {
        background: var(--accent) !important;
        border-color: var(--accent) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(var(--accent-rgb), 0.25) !important;
    }

    .custom-scroll::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    .custom-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scroll::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 6px;
    }

    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: var(--text-muted);
    }

    .table-card {
        background: var(--surface-2) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 10px !important;
    }

    #ordersTable {
        border-collapse: collapse !important;
        width: 100% !important;
        margin-bottom: 0 !important;
        color: var(--text-main) !important;
    }

    #ordersTable thead th {
        background: var(--tableHeader) !important;
        color: var(--text-muted) !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        border-bottom: 1px solid var(--border-color) !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        padding: 0.85rem 1rem !important;
        vertical-align: middle !important;
        text-align: left !important;
    }

    #ordersTable thead th:first-child {
        text-align: center !important;
        width: 44px !important;
        min-width: 44px !important;
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }

    #ordersTable thead th:last-child {
        text-align: right !important;
        padding-right: 1.25rem !important;
    }

    #ordersTable thead th.sorting,
    #ordersTable thead th.sorting_asc,
    #ordersTable thead th.sorting_desc {
        cursor: pointer !important;
        position: relative !important;
        padding-right: 28px !important;
        user-select: none !important;
        transition: color 0.15s ease, background-color 0.15s ease !important;
    }

    #ordersTable thead th.sorting:hover,
    #ordersTable thead th.sorting_asc,
    #ordersTable thead th.sorting_desc {
        color: var(--accent) !important;
    }

    #ordersTable thead th.sorting::after,
    #ordersTable thead th.sorting_asc::after,
    #ordersTable thead th.sorting_desc::after {
        position: absolute !important;
        right: 10px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        font-family: "Font Awesome 6 Free" !important;
        font-weight: 900 !important;
        font-size: 0.74rem !important;
        opacity: 0.3 !important;
        content: "\f0dc" !important;
    }

    #ordersTable thead th.sorting:before,
    #ordersTable thead th.sorting_asc:before,
    #ordersTable thead th.sorting_desc:before {
        display: none !important;
    }

    #ordersTable thead th.sorting_asc::after {
        content: "\f0de" !important;
        opacity: 1 !important;
        color: var(--accent) !important;
    }

    #ordersTable thead th.sorting_desc::after {
        content: "\f0dd" !important;
        opacity: 1 !important;
        color: var(--accent) !important;
    }

    .filter-ctrl-item {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .filter-ctrl-label {
        font-size: 0.76rem !important;
        font-weight: 700 !important;
        color: var(--text-muted) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.03em !important;
        white-space: nowrap !important;
    }

    .filter-ctrl-select {
        background: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        padding: 4px 24px 4px 10px !important;
        height: 32px !important;
        cursor: pointer !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .filter-ctrl-select:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 2px rgba(var(--accent-rgb), 0.2) !important;
    }

    .bottom-length-select {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .bottom-length-select select {
        padding: 2px 20px 2px 8px !important;
        font-size: 0.75rem !important;
        border-radius: 6px !important;
        height: 28px !important;
        border: 1px solid var(--border-color) !important;
        background-color: var(--surface) !important;
        color: var(--text-main) !important;
        cursor: pointer !important;
    }

    #ordersTable tbody tr {
        background: var(--surface-2) !important;
        color: var(--text-main) !important;
        transition: background-color 0.15s ease !important;
    }

    #ordersTable tbody tr:hover td {
        background-color: rgba(var(--accent-rgb), 0.04) !important;
    }

    #ordersTable tbody tr td {
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        border-bottom: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        background: transparent !important;
        padding: 0.85rem 1rem !important;
        text-align: left !important;
        vertical-align: middle !important;
    }

    #ordersTable tbody tr td:first-child {
        text-align: center !important;
        width: 44px !important;
        min-width: 44px !important;
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }

    #ordersTable tbody tr td:last-child {
        text-align: right !important;
        padding-right: 1.25rem !important;
    }

    .order-meta-cell {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 5px !important;
        text-align: left !important;
    }

    .order-id-link {
        font-family: var(--font-monospace, monospace) !important;
        font-size: 0.88rem !important;
        font-weight: 700 !important;
        color: var(--accent) !important;
        text-decoration: none !important;
        letter-spacing: 0.02em !important;
        line-height: 1.2 !important;
    }

    .order-id-link:hover {
        text-decoration: underline !important;
        opacity: 0.85 !important;
    }

    .order-status-pill {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        padding: 2px 8px !important;
        border-radius: 9999px !important;
        font-size: 0.68rem !important;
        font-weight: 600 !important;
        line-height: 1.2 !important;
        letter-spacing: 0.02em !important;
        white-space: nowrap !important;
    }

    .order-status-pill .status-dot {
        width: 6px !important;
        height: 6px !important;
        border-radius: 50% !important;
        display: inline-block !important;
        flex-shrink: 0 !important;
    }

    .status-confirmed {
        background: rgba(16, 185, 129, 0.12) !important;
        color: #10b981 !important;
        border: 1px solid rgba(16, 185, 129, 0.25) !important;
    }
    .status-confirmed .status-dot {
        background-color: #10b981 !important;
    }

    .status-to-call {
        background: rgba(245, 158, 11, 0.12) !important;
        color: #f59e0b !important;
        border: 1px solid rgba(245, 158, 11, 0.25) !important;
    }
    .status-to-call .status-dot {
        background-color: #f59e0b !important;
    }

    .status-no-answer {
        background: rgba(249, 115, 22, 0.12) !important;
        color: #f97316 !important;
        border: 1px solid rgba(249, 115, 22, 0.25) !important;
    }
    .status-no-answer .status-dot {
        background-color: #f97316 !important;
    }

    .status-in-courier {
        background: rgba(59, 130, 246, 0.12) !important;
        color: #3b82f6 !important;
        border: 1px solid rgba(59, 130, 246, 0.25) !important;
    }
    .status-in-courier .status-dot {
        background-color: #3b82f6 !important;
    }

    .status-delivered {
        background: rgba(34, 197, 94, 0.12) !important;
        color: #22c55e !important;
        border: 1px solid rgba(34, 197, 94, 0.25) !important;
    }
    .status-delivered .status-dot {
        background-color: #22c55e !important;
    }

    .status-cancelled {
        background: rgba(239, 68, 68, 0.12) !important;
        color: #ef4444 !important;
        border: 1px solid rgba(239, 68, 68, 0.25) !important;
    }
    .status-cancelled .status-dot {
        background-color: #ef4444 !important;
    }

    .order-time-stamp {
        font-size: 0.72rem !important;
        color: var(--text-muted) !important;
        font-family: var(--font-monospace, monospace) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        line-height: 1.2 !important;
    }

    .order-customer-cell {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 4px !important;
        text-align: left !important;
        min-width: 0 !important;
    }

    .order-customer-name-row {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
        text-align: left !important;
    }

    .order-customer-name {
        font-size: 0.88rem !important;
        font-weight: 700 !important;
        color: var(--text-main) !important;
        text-align: left !important;
        line-height: 1.2 !important;
    }

    .location-badge {
        display: inline-flex !important;
        align-items: center !important;
        padding: 1px 7px !important;
        border-radius: 9999px !important;
        font-size: 0.68rem !important;
        font-weight: 600 !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
    }

    .location-inside {
        background: rgba(14, 165, 233, 0.12) !important;
        color: #0284c7 !important;
        border: 1px solid rgba(14, 165, 233, 0.25) !important;
    }

    .location-outside {
        background: rgba(139, 92, 246, 0.12) !important;
        color: #8b5cf6 !important;
        border: 1px solid rgba(139, 92, 246, 0.25) !important;
    }

    [data-bs-theme="dark"] .location-inside {
        color: #38bdf8 !important;
    }

    [data-bs-theme="dark"] .location-outside {
        color: #a78bfa !important;
    }

    .order-phone-row {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        text-align: left !important;
    }

    .order-phone-link {
        color: #10b981 !important;
        font-family: var(--font-monospace, monospace) !important;
        font-weight: 700 !important;
        font-size: 0.84rem !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        line-height: 1.2 !important;
    }

    .order-phone-link:hover {
        text-decoration: underline !important;
    }

    .btn-copy-chip {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        color: var(--text-muted) !important;
        font-size: 0.74rem !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        line-height: 1 !important;
    }

    .btn-copy-chip:hover {
        color: var(--text-main) !important;
    }

    .order-address-text {
        font-size: 0.75rem !important;
        color: var(--text-muted) !important;
        line-height: 1.35 !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        word-break: break-word !important;
        text-align: left !important;
    }

    .order-product-cell {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 12px !important;
        text-align: left !important;
        min-width: 0 !important;
    }

    .order-product-thumb {
        width: 44px !important;
        height: 44px !important;
        min-width: 44px !important;
        max-width: 44px !important;
        border-radius: 8px !important;
        object-fit: cover !important;
        border: 1px solid var(--border-color) !important;
        flex-shrink: 0 !important;
        background: var(--surface) !important;
    }

    .order-product-info {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        justify-content: center !important;
        text-align: left !important;
        min-width: 0 !important;
        flex: 1 1 auto !important;
    }

    .order-product-title {
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        line-height: 1.35 !important;
        color: var(--text-main) !important;
        text-align: left !important;
        margin-bottom: 4px !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        word-break: break-word !important;
    }

    .order-product-meta {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 6px !important;
        flex-wrap: wrap !important;
        text-align: left !important;
    }

    .product-qty-pill {
        font-family: var(--font-monospace, monospace) !important;
        font-size: 0.68rem !important;
        font-weight: 600 !important;
        padding: 2px 7px !important;
        border-radius: 9999px !important;
        background: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        line-height: 1.2 !important;
    }

    .product-more-pill {
        font-size: 0.68rem !important;
        font-weight: 600 !important;
        padding: 2px 8px !important;
        border-radius: 9999px !important;
        background: rgba(var(--accent-rgb), 0.1) !important;
        color: var(--accent) !important;
        border: 1px solid rgba(var(--accent-rgb), 0.25) !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        line-height: 1.2 !important;
    }

    .product-more-pill:hover {
        background: rgba(var(--accent-rgb), 0.18) !important;
        color: var(--accent) !important;
    }

    .order-payment-cell {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 4px !important;
        text-align: left !important;
    }

    .order-amount-row {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        text-align: left !important;
    }

    .order-amount {
        font-family: var(--font-monospace, monospace) !important;
        font-size: 0.94rem !important;
        font-weight: 700 !important;
        color: var(--text-main) !important;
        text-align: left !important;
        line-height: 1.2 !important;
    }

    .payment-badge-pill {
        font-family: var(--font-monospace, monospace) !important;
        font-size: 0.66rem !important;
        font-weight: 700 !important;
        padding: 2px 6px !important;
        border-radius: 9999px !important;
        letter-spacing: 0.04em !important;
        line-height: 1.2 !important;
    }

    .payment-cod {
        background: rgba(245, 158, 11, 0.12) !important;
        color: #f59e0b !important;
        border: 1px solid rgba(245, 158, 11, 0.25) !important;
    }

    .payment-paid {
        background: rgba(16, 185, 129, 0.12) !important;
        color: #10b981 !important;
        border: 1px solid rgba(16, 185, 129, 0.25) !important;
    }

    .order-courier-pill {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        font-family: var(--font-monospace, monospace) !important;
        font-size: 0.71rem !important;
        font-weight: 600 !important;
        padding: 2px 7px !important;
        border-radius: 5px !important;
        background: var(--surface) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        cursor: pointer !important;
        max-width: 100% !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        user-select: all !important;
        line-height: 1.2 !important;
    }

    .order-courier-pill:hover {
        border-color: var(--accent) !important;
    }

    .order-courier-name {
        font-size: 0.72rem !important;
        color: var(--text-muted) !important;
        font-weight: 500 !important;
        line-height: 1.2 !important;
    }

    .order-actions-cell {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
        flex-wrap: nowrap !important;
    }

    .call-status-pill {
        display: inline-flex !important;
        align-items: center !important;
        gap: 3px !important;
        padding: 2px 4px 2px 7px !important;
        border-radius: 9999px !important;
        border: 1.5px solid transparent !important;
        height: 26px !important;
        transition: all 0.2s ease !important;
        white-space: nowrap !important;
    }

    .call-status-pill .call-icon {
        font-size: 0.68rem !important;
        line-height: 1 !important;
        flex-shrink: 0 !important;
    }

    .call-status-pill .call-status-select {
        appearance: none !important;
        -webkit-appearance: none !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 1px center !important;
        background-size: 7px 7px !important;
        padding: 0 12px 0 2px !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        border: none !important;
        background-color: transparent !important;
        cursor: pointer !important;
        outline: none !important;
        height: 100% !important;
        line-height: 1 !important;
        color: inherit !important;
    }

    .call-status-pill.status-confirmed {
        background-color: rgba(16, 185, 129, 0.12) !important;
        color: #10b981 !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
    }

    .call-status-pill.status-to-call {
        background-color: rgba(245, 158, 11, 0.12) !important;
        color: #f59e0b !important;
        border-color: rgba(245, 158, 11, 0.3) !important;
    }

    .call-status-pill.status-no-answer {
        background-color: rgba(249, 115, 22, 0.12) !important;
        color: #f97316 !important;
        border-color: rgba(249, 115, 22, 0.3) !important;
    }

    .call-status-pill.status-cancelled {
        background-color: rgba(239, 68, 68, 0.12) !important;
        color: #ef4444 !important;
        border-color: rgba(239, 68, 68, 0.3) !important;
    }

    .call-status-pill option {
        background: var(--surface, #ffffff) !important;
        color: var(--text-main, #1e293b) !important;
        font-weight: 600 !important;
    }

    .btn-action-primary {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 5px !important;
        padding: 4px 10px !important;
        border-radius: 6px !important;
        font-size: 0.74rem !important;
        font-weight: 700 !important;
        border: none !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        white-space: nowrap !important;
        line-height: 1.2 !important;
        height: 28px !important;
    }

    .btn-action-courier {
        background: var(--accent) !important;
        color: #ffffff !important;
        box-shadow: 0 1px 2px rgba(var(--accent-rgb), 0.2) !important;
    }

    .btn-action-courier:hover {
        opacity: 0.9 !important;
        color: #ffffff !important;
    }

    .btn-action-label {
        width: 28px !important;
        height: 28px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .btn-action-secondary {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 4px 9px !important;
        border-radius: 6px !important;
        font-size: 0.76rem !important;
        font-weight: 600 !important;
        background: var(--surface) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-color) !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        white-space: nowrap !important;
        line-height: 1.2 !important;
    }

    .btn-action-secondary:hover {
        border-color: var(--text-muted) !important;
        color: var(--text-main) !important;
    }

    .btn-group-actions {
        display: inline-flex !important;
        align-items: center !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 6px !important;
        background: var(--surface) !important;
        overflow: hidden !important;
    }

    .btn-action-icon {
        width: 28px !important;
        height: 28px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: none !important;
        background: transparent !important;
        color: var(--text-muted) !important;
        font-size: 0.74rem !important;
        cursor: pointer !important;
        transition: all 0.12s ease !important;
    }

    .btn-action-icon:hover {
        background: var(--surface-2) !important;
        color: var(--text-main) !important;
    }

    .btn-action-danger:hover {
        background: rgba(239, 68, 68, 0.1) !important;
        color: #ef4444 !important;
    }

    .tooltip {
        pointer-events: none !important;
        z-index: 1080 !important;
    }

    .tooltip .tooltip-inner {
        background: #0f172a !important;
        color: #f8fafc !important;
        font-size: 0.72rem !important;
        font-weight: 600 !important;
        padding: 5px 10px !important;
        border-radius: 6px !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        box-shadow: 0 10px 25px -4px rgba(0, 0, 0, 0.4) !important;
        letter-spacing: 0.01em !important;
        white-space: nowrap !important;
    }

    .tooltip.bs-tooltip-top .tooltip-arrow::before {
        border-top-color: #0f172a !important;
    }

    .tooltip.bs-tooltip-bottom .tooltip-arrow::before {
        border-bottom-color: #0f172a !important;
    }

    .form-control, .form-select {
        background-color: var(--input-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-main) !important;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.2) !important;
        color: var(--text-main) !important;
    }

    .input-group-text {
        background-color: var(--input-group-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-muted) !important;
    }

    .filter-card {
        background: var(--surface-2) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 12px !important;
        padding: 0.85rem 1rem !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03) !important;
    }

    .orders-search-wrapper {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
    }

    .orders-search-wrapper .search-icon {
        position: absolute !important;
        left: 14px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: var(--text-muted) !important;
        font-size: 0.88rem !important;
        pointer-events: none !important;
        z-index: 5 !important;
        transition: color 0.15s ease !important;
    }

    .orders-search-wrapper:focus-within .search-icon {
        color: var(--accent) !important;
    }

    .orders-search-wrapper input.form-control {
        padding-left: 40px !important;
        padding-right: 14px !important;
        height: 42px !important;
        background: var(--surface) !important;
        border: 1.5px solid var(--border-color) !important;
        border-radius: 10px !important;
        color: var(--text-main) !important;
        font-size: 0.88rem !important;
        font-weight: 500 !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    }

    .orders-search-wrapper input.form-control:focus {
        border-color: var(--accent) !important;
        background: var(--surface-2) !important;
        box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.18) !important;
        color: var(--text-main) !important;
    }

    .orders-search-wrapper input.form-control::placeholder {
        color: var(--text-muted) !important;
        font-weight: 400 !important;
        opacity: 0.85 !important;
    }

    .date-filter-group {
        display: flex !important;
        align-items: center !important;
        background: var(--surface) !important;
        border: 1.5px solid var(--border-color) !important;
        border-radius: 10px !important;
        overflow: hidden !important;
        height: 42px !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        transition: border-color 0.2s ease !important;
    }

    .date-filter-group:focus-within {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.18) !important;
    }

    .date-filter-group input.form-control {
        border: none !important;
        background: transparent !important;
        height: 100% !important;
        font-size: 0.84rem !important;
        box-shadow: none !important;
        padding: 0 10px !important;
        color: var(--text-main) !important;
    }

    .date-filter-group .date-separator {
        color: var(--text-muted) !important;
        font-size: 0.76rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        padding: 0 4px !important;
    }

    .date-filter-group .btn-filter-apply {
        border: none !important;
        background: transparent !important;
        color: var(--text-muted) !important;
        padding: 0 14px !important;
        height: 100% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        border-left: 1px solid var(--border-color) !important;
    }

    .date-filter-group .btn-filter-apply:hover {
        background: var(--surface-2) !important;
        color: var(--accent) !important;
    }

    [data-bs-theme="dark"] .bg-secondary-subtle {
        background-color: rgba(148, 163, 184, 0.15) !important;
        color: #e2e8f0 !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
    }

    [data-bs-theme="light"] .bg-secondary-subtle {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        border-color: #cbd5e1 !important;
    }

    [data-bs-theme="dark"] .bg-success-subtle {
        background-color: rgba(16, 185, 129, 0.15) !important;
        color: #34d399 !important;
        border-color: rgba(16, 185, 129, 0.25) !important;
    }

    [data-bs-theme="light"] .bg-success-subtle {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
        border-color: #a7f3d0 !important;
    }

    [data-bs-theme="dark"] .bg-warning-subtle {
        background-color: rgba(245, 158, 11, 0.15) !important;
        color: #fbbf24 !important;
        border-color: rgba(245, 158, 11, 0.25) !important;
    }

    [data-bs-theme="light"] .bg-warning-subtle {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border-color: #fde68a !important;
    }

    [data-bs-theme="dark"] .bg-danger-subtle {
        background-color: rgba(239, 68, 68, 0.15) !important;
        color: #f87171 !important;
        border-color: rgba(239, 68, 68, 0.25) !important;
    }

    [data-bs-theme="light"] .bg-danger-subtle {
        background-color: #fee2e2 !important;
        color: #991b1b !important;
        border-color: #fecaca !important;
    }

    [data-bs-theme="dark"] .bg-info-subtle {
        background-color: rgba(14, 165, 233, 0.15) !important;
        color: #38bdf8 !important;
        border-color: rgba(14, 165, 233, 0.25) !important;
    }

    [data-bs-theme="light"] .bg-info-subtle {
        background-color: #e0f2fe !important;
        color: #075985 !important;
        border-color: #bae6fd !important;
    }

    @media (max-width: 991.98px) {
        .edit-modal-layout, .view-modal-layout {
            grid-template-columns: 1fr !important;
            overflow-y: auto !important;
        }
        .panel-left-side, .panel-right-side {
            height: auto !important;
            overflow: visible !important;
        }
        .panel-scroll-content {
            overflow: visible !important;
        }
    }
</style>
@endpush

@section('content')
<div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0">Customer Orders Management</h3>
            <small class="text-muted">Tele-call verification, packaging pipeline, and courier dispatch engine</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetAllFilters()" data-bs-toggle="tooltip" data-bs-title="Reset all filters & sorting">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshOrdersTable()" data-bs-toggle="tooltip" data-bs-title="Refresh order table data">
                <i class="fa-solid fa-arrows-rotate me-1" id="refreshIcon"></i> Refresh
            </button>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center p-3">
            <small class="text-muted d-block mb-1">To Call (Pending)</small>
            <h3 class="fw-bold mb-0 text-warning" id="statCardToCall">{{ $workflowCounts['to_call'] }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center p-3">
            <small class="text-muted d-block mb-1">Ready to Pack</small>
            <h3 class="fw-bold mb-0 text-primary" id="statCardToPack">{{ $workflowCounts['to_pack'] }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center p-3">
            <small class="text-muted d-block mb-1">In Logistics Transit</small>
            <h3 class="fw-bold mb-0 text-info" id="statCardInTransit">{{ $workflowCounts['in_transit'] }}</h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card text-center p-3">
            <small class="text-muted d-block mb-1">Today's Volume</small>
            <h3 class="fw-bold mb-0 text-success" id="statCardTodayValue">৳ {{ number_format($todayStats['total_value'], 0) }}</h3>
        </div>
    </div>
</div>

<div class="card p-2 mb-4">
    <div class="order-pipeline-bar" id="stageTabsBar">
        <button type="button" class="pipeline-tab-btn active" data-stage="all">
            <i class="fa-solid fa-layer-group text-primary"></i>
            <span>All Orders</span>
            <span class="badge rounded-pill font-monospace">{{ $workflowCounts['all'] }}</span>
        </button>
        <button type="button" class="pipeline-tab-btn" data-stage="to_call">
            <i class="fa-solid fa-phone-volume text-warning"></i>
            <span>To Call</span>
            <span class="badge rounded-pill font-monospace">{{ $workflowCounts['to_call'] }}</span>
        </button>
        <button type="button" class="pipeline-tab-btn" data-stage="to_pack">
            <i class="fa-solid fa-box-archive text-info"></i>
            <span>Ready to Pack</span>
            <span class="badge rounded-pill font-monospace">{{ $workflowCounts['to_pack'] }}</span>
        </button>
        <button type="button" class="pipeline-tab-btn" data-stage="in_transit">
            <i class="fa-solid fa-truck-fast text-success"></i>
            <span>In Transit</span>
            <span class="badge rounded-pill font-monospace">{{ $workflowCounts['in_transit'] }}</span>
        </button>
        <button type="button" class="pipeline-tab-btn" data-stage="completed">
            <i class="fa-solid fa-circle-check text-success"></i>
            <span>Delivered</span>
            <span class="badge rounded-pill font-monospace">{{ $workflowCounts['completed'] }}</span>
        </button>
        <button type="button" class="pipeline-tab-btn" data-stage="issues">
            <i class="fa-solid fa-ban text-danger"></i>
            <span>Cancelled</span>
            <span class="badge rounded-pill font-monospace">{{ $workflowCounts['issues'] }}</span>
        </button>
    </div>
</div>

<div class="bulk-action-bar mb-4" id="bulkActionBar">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary rounded-pill px-2.5 py-1 font-monospace" id="selectedCountBadge">0 selected</span>
        <span class="small fw-bold text-body">Bulk Actions:</span>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
        <button type="button" class="btn btn-sm btn-success px-3 py-1 fw-bold" onclick="executeBulkCallStatus('confirmed')">
            <i class="fa-solid fa-check me-1"></i> Confirm Calls
        </button>
        <button type="button" class="btn btn-sm btn-outline-warning px-3 py-1 fw-semibold" onclick="executeBulkCallStatus('no_answer')">
            <i class="fa-solid fa-phone-slash me-1"></i> No Answer
        </button>
        <button type="button" class="btn btn-sm btn-primary px-3 py-1 fw-semibold" onclick="openBulkCourierModal()">
            <i class="fa-solid fa-truck-fast me-1"></i> Handover Courier
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1" onclick="clearSelection()" data-bs-toggle="tooltip" data-bs-title="Deselect All">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<div class="card mb-4 filter-card">
    <div class="row g-2 align-items-center mb-2">
        <div class="col-lg-7 col-md-6 col-12">
            <div class="orders-search-wrapper">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="customSearchInput" class="form-control" placeholder="Search by order #, customer name, phone number, product, or address...">
            </div>
        </div>

        <div class="col-lg-5 col-md-6 col-12">
            <div class="date-filter-group">
                <input type="date" id="filterDateFrom" class="form-control" data-bs-toggle="tooltip" data-bs-title="From Date">
                <span class="date-separator">to</span>
                <input type="date" id="filterDateTo" class="form-control" data-bs-toggle="tooltip" data-bs-title="To Date">
                <button type="button" class="btn-filter-apply" onclick="applyAdvancedFilters()" data-bs-toggle="tooltip" data-bs-title="Apply Date Filter">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top border-secondary border-opacity-10">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-1.5 filter-ctrl-item">
                <span class="filter-ctrl-label"><i class="fa-solid fa-arrow-down-wide-short me-1 text-accent"></i>Sort by:</span>
                <select id="customSortOrder" class="form-select form-select-sm filter-ctrl-select" onchange="handleSortChange(this.value)">
                    <option value="newest" selected>Newest Orders First</option>
                    <option value="oldest">Oldest Orders First</option>
                    <option value="amount_high">Amount: High to Low</option>
                    <option value="amount_low">Amount: Low to High</option>
                    <option value="customer_asc">Customer: A to Z</option>
                </select>
            </div>
            <div class="d-flex align-items-center gap-1.5 filter-ctrl-item">
                <span class="filter-ctrl-label"><i class="fa-solid fa-list-ol me-1 text-accent"></i>Show:</span>
                <select id="customPageLength" class="form-select form-select-sm filter-ctrl-select" onchange="handlePageLengthChange(this.value)">
                    <option value="15" selected>15 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card table-card mb-4 overflow-hidden border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle custom-orders-table mb-0" id="ordersTable" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 44px; text-align: center;" class="text-center">
                        <input type="checkbox" class="form-check-input" id="selectAllCheckbox" data-bs-toggle="tooltip" data-bs-title="Select All">
                    </th>
                    <th style="width: 16%; text-align: left;" class="text-start">Order & Status</th>
                    <th style="width: 23%; text-align: left;" class="text-start">Customer & Destination</th>
                    <th style="width: 27%; text-align: left;" class="text-start">Ordered Items</th>
                    <th style="width: 14%; text-align: left;" class="text-start">Total & COD</th>
                    <th style="width: 20%; text-align: right;" class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-modal-wrapper" id="quickOrderModal">
    <div class="admin-modal-overlay" onclick="closeAdminModal('quickOrderModal')"></div>
    <div class="admin-modal-dialog modal-xl">
        <div class="admin-modal-header">
            <div class="d-flex align-items-center gap-3">
                <div class="admin-modal-header-icon flex-shrink-0">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="admin-modal-title font-monospace mb-0" id="modalOrderNumber">Order Details</h5>
                        <span class="badge bg-secondary-subtle text-body border rounded-pill px-2.5 py-0.5" id="modalStageBadge">Verified</span>
                        <span class="badge bg-success-subtle text-success border rounded-pill px-2 py-0.5 font-monospace" id="modalFraudBadge"><i class="fa-solid fa-shield-halved me-1"></i>Safe Buyer</span>
                    </div>
                    <small class="text-muted font-monospace" id="modalOrderDate"></small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="text-end">
                    <span class="text-muted small d-block font-monospace" style="font-size: 0.72rem; line-height: 1.1;">PAYABLE COD</span>
                    <span class="fw-bold font-monospace text-primary fs-5" id="modalHeaderTotal" style="line-height: 1.2;">৳ 0</span>
                </div>
                <button type="button" class="admin-modal-close-btn ms-2" onclick="closeAdminModal('quickOrderModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        <div class="admin-modal-body" id="quickOrderModalBody">
            <div class="admin-modal-loader">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
        <div class="admin-modal-footer" id="quickOrderModalFooter">
        </div>
    </div>
</div>

<div class="admin-modal-wrapper" id="editOrderModal">
    <div class="admin-modal-overlay" onclick="closeAdminModal('editOrderModal')"></div>
    <div class="admin-modal-dialog modal-xl">
        <div class="admin-modal-header">
            <div class="d-flex align-items-center gap-3">
                <div class="admin-modal-header-icon flex-shrink-0">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="admin-modal-title mb-0" id="editModalOrderNumber">Edit Order</h5>
                        <span class="badge bg-warning-subtle text-warning border rounded-pill px-2.5 py-0.5" id="editModalStatusBadge">Pending</span>
                        <span class="badge bg-success-subtle text-success border rounded-pill px-2 py-0.5 font-monospace" id="editModalFraudBadge"><i class="fa-solid fa-shield-halved me-1"></i>Safe Buyer</span>
                    </div>
                    <small class="text-muted font-monospace" id="editModalOrderDate"></small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="text-end">
                    <span class="text-muted small d-block font-monospace" style="font-size: 0.72rem; line-height: 1.1;">PAYABLE COD</span>
                    <span class="fw-bold font-monospace text-primary fs-5" id="editModalHeaderTotal" style="line-height: 1.2;">৳ 0</span>
                </div>
                <button type="button" class="admin-modal-close-btn ms-2" onclick="closeAdminModal('editOrderModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        <div class="admin-modal-body" id="editOrderModalBody">
            <div class="admin-modal-loader">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-secondary px-3.5 py-1.5 fw-semibold" onclick="closeAdminModal('editOrderModal')">
                <i class="fa-solid fa-xmark me-1"></i> Discard
            </button>
            <button type="button" class="btn btn-sm btn-primary px-4 py-1.5 fw-bold d-flex align-items-center gap-1.5 ms-auto shadow-sm" onclick="saveOrderEdits()">
                <i class="fa-solid fa-check"></i>
                <span>Save Changes & Sync Order</span>
            </button>
        </div>
    </div>
</div>

<iframe id="printInvoiceFrame" style="position: absolute; width: 0; height: 0; border: 0; visibility: hidden;"></iframe>
@endsection

@push('scripts')
<script>
(() => {
    let ordersTable = null;
    let activeStage = 'all';
    let searchDebounceTimer = null;
    let selectedOrderIds = [];
    let currentEditingOrderId = null;
    let currentEditItems = [];
    let productSearchTimer = null;

    function openAdminModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.classList.add('admin-no-scroll');
        }
    }

    function closeAdminModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.classList.remove('admin-no-scroll');
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.admin-modal-wrapper.active').forEach(m => closeAdminModal(m.id));
        }
    });

    function initOrdersIndex() {
        const tableEl = document.getElementById('ordersTable');
        if (!tableEl) return;

        if (window.VanillaDataTable && VanillaDataTable.isDataTable('#ordersTable')) {
            VanillaDataTable.getInstance('#ordersTable').destroy();
        }

        ordersTable = new VanillaDataTable('#ordersTable', {
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12'tr>>" +
                 "<'d-flex align-items-center justify-content-between p-3 border-top border-secondary border-opacity-25 flex-wrap gap-3'<'d-flex align-items-center gap-3'<'small text-muted font-monospace'i><'bottom-length-select d-flex align-items-center gap-1.5 small text-muted font-monospace'<'text-nowrap'l>>><'pagination-erp'p>>",
            ajax: {
                url: "{{ route('admin.orders.index') }}",
                data: function (d) {
                    d.stage = activeStage;
                    const dFrom = document.getElementById('filterDateFrom');
                    const dTo = document.getElementById('filterDateTo');
                    d.date_from = dFrom ? dFrom.value : '';
                    d.date_to = dTo ? dTo.value : '';
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, className: 'text-center align-middle', width: '44px' },
                { data: 'order_info', name: 'orders.id', orderable: true, className: 'text-start align-middle', width: '16%' },
                { data: 'customer_info', name: 'orders.customer_name', orderable: true, className: 'text-start align-middle', width: '23%' },
                { data: 'product_items', name: 'orders.id', orderable: false, searchable: false, className: 'text-start align-middle', width: '27%' },
                { data: 'total_payment', name: 'orders.total', orderable: true, className: 'text-start align-middle', width: '14%' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-end align-middle pe-3', width: '20%' }
            ],
            lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
            pageLength: 15,
            drawCallback: function (settings) {
                updateBulkBarState();
                const json = settings.json;
                if (json && json.workflowCounts) {
                    updateWorkflowBadges(json.workflowCounts, json.todayStats);
                }
                initTooltips();
                const currentLen = ordersTable ? ordersTable.page.len() : 15;
                const pageSel = document.getElementById('customPageLength');
                if (pageSel && pageSel.value != currentLen) {
                    pageSel.value = currentLen;
                }
            },
            language: {
                zeroRecords: '<div class="text-center py-5 text-muted font-monospace"><i class="fa-solid fa-box-open fa-2x mb-2 opacity-25"></i><br>No orders found in this workflow stage</div>',
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading orders...',
                lengthMenu: 'Show _MENU_ / page',
                paginate: {
                    next: '<i class="fa-solid fa-chevron-right fa-xs"></i>',
                    previous: '<i class="fa-solid fa-chevron-left fa-xs"></i>'
                }
            }
        });
        window.ordersTable = ordersTable;

        ordersTable.on('order.dt', function () {
            if (!ordersTable) return;
            const order = ordersTable.order();
            if (order && order.length) {
                const colIdx = order[0][0];
                const dir = order[0][1];
                const sortSelect = document.getElementById('customSortOrder');
                if (sortSelect) {
                    if (colIdx === 1 && dir === 'desc') sortSelect.value = 'newest';
                    else if (colIdx === 1 && dir === 'asc') sortSelect.value = 'oldest';
                    else if (colIdx === 4 && dir === 'desc') sortSelect.value = 'amount_high';
                    else if (colIdx === 4 && dir === 'asc') sortSelect.value = 'amount_low';
                    else if (colIdx === 2 && dir === 'asc') sortSelect.value = 'customer_asc';
                }
            }
        });

        ordersTable.on('xhr.dt', function (e, settings, json) {
            if (json && json.workflowCounts) {
                updateWorkflowBadges(json.workflowCounts, json.todayStats);
            }
        });

        const customSearch = document.getElementById('customSearchInput');
        if (customSearch) {
            customSearch.oninput = function () {
                const val = this.value;
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    if (ordersTable) ordersTable.search(val).draw();
                }, 250);
            };
        }

        document.querySelectorAll('#stageTabsBar .pipeline-tab-btn').forEach(btn => {
            btn.onclick = function () {
                document.querySelectorAll('#stageTabsBar .pipeline-tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeStage = this.dataset.stage;
                clearSelection();
                if (ordersTable) ordersTable.draw();
            };
        });

        const selectAllCb = document.getElementById('selectAllCheckbox');
        if (selectAllCb) {
            selectAllCb.onchange = function () {
                const isChecked = this.checked;
                document.querySelectorAll('.order-row-checkbox').forEach(cb => cb.checked = isChecked);
                syncSelectedOrders();
            };
        }

        if (tableEl) {
            tableEl.onchange = function (e) {
                if (e.target && e.target.classList.contains('order-row-checkbox')) {
                    syncSelectedOrders();
                }
            };
        }
    }

    function syncSelectedOrders() {
        selectedOrderIds = [];
        document.querySelectorAll('.order-row-checkbox:checked').forEach(cb => {
            selectedOrderIds.push(cb.value);
        });
        updateBulkBarState();
    }

    function updateBulkBarState() {
        const count = selectedOrderIds.length;
        const bar = document.getElementById('bulkActionBar');
        const badge = document.getElementById('selectedCountBadge');
        if (badge) badge.innerText = `${count} selected`;
        if (bar) {
            bar.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    function clearSelection() {
        selectedOrderIds = [];
        const selectAllCb = document.getElementById('selectAllCheckbox');
        if (selectAllCb) selectAllCb.checked = false;
        document.querySelectorAll('.order-row-checkbox').forEach(cb => { cb.checked = false; });
        updateBulkBarState();
    }

    function updateWorkflowBadges(counts, todayStats) {
        if (!counts) return;
        document.querySelectorAll('#stageTabsBar .pipeline-tab-btn').forEach(btn => {
            const stage = btn.dataset.stage;
            if (counts[stage] !== undefined) {
                const b = btn.querySelector('.badge');
                if (b) b.textContent = counts[stage];
            }
        });
        if (counts.to_call !== undefined) {
            const el = document.getElementById('statCardToCall');
            if (el) el.innerText = counts.to_call;
        }
        if (counts.to_pack !== undefined) {
            const el = document.getElementById('statCardToPack');
            if (el) el.innerText = counts.to_pack;
        }
        if (counts.in_transit !== undefined) {
            const el = document.getElementById('statCardInTransit');
            if (el) el.innerText = counts.in_transit;
        }
        if (todayStats && todayStats.total_value !== undefined) {
            const el = document.getElementById('statCardTodayValue');
            if (el) el.innerText = '৳ ' + Math.round(Number(todayStats.total_value) || 0).toLocaleString();
        }
    }

    function fetchPipelineCounts() {
        axios.get("{{ route('admin.orders.index') }}", { params: { get_pipeline_counts: 1 } })
            .then(res => {
                if (res.data && res.data.success && res.data.workflowCounts) {
                    updateWorkflowBadges(res.data.workflowCounts, res.data.todayStats);
                }
            })
            .catch(() => {});
    }

    function quickChangeCallStatus(orderId, selectEl) {
        const prevStatus = selectEl.getAttribute('data-prev') || 'pending_call';
        const newStatus = selectEl.value;

        if (newStatus === prevStatus) return;

        if (newStatus === 'cancelled_on_call') {
            Swal.fire({
                title: 'Cancel this order?',
                text: 'Are you sure you want to mark this order as cancelled?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel Order',
                cancelButtonText: 'No, Keep It',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    performCallStatusChange(orderId, selectEl, newStatus);
                } else {
                    selectEl.value = prevStatus;
                }
            });
            return;
        }

        performCallStatusChange(orderId, selectEl, newStatus);
    }

    function performCallStatusChange(orderId, selectEl, newStatus) {
        selectEl.disabled = true;
        axios.post(`/admin/orders/${orderId}/ajax-call-status`, { call_status: newStatus })
            .then(res => {
                selectEl.disabled = false;
                if (res.data && res.data.success) {
                    selectEl.setAttribute('data-prev', newStatus);
                    if (res.data.workflowCounts) {
                        updateWorkflowBadges(res.data.workflowCounts, res.data.todayStats);
                    }
                    const pillEl = selectEl.closest('.call-status-pill') || selectEl;
                    pillEl.classList.remove('status-to-call', 'status-confirmed', 'status-no-answer', 'status-cancelled');
                    if (newStatus === 'confirmed') {
                        pillEl.classList.add('status-confirmed');
                        showToast('success', 'Order confirmed & ready');
                    } else if (newStatus === 'no_answer') {
                        pillEl.classList.add('status-no-answer');
                        showToast('warning', 'Marked as No Answer');
                    } else if (newStatus === 'cancelled_on_call') {
                        pillEl.classList.add('status-cancelled');
                        showToast('info', 'Order cancelled');
                    } else {
                        pillEl.classList.add('status-to-call');
                        showToast('success', 'Status set to To Call');
                    }
                    if (ordersTable) ordersTable.ajax.reload(null, false);
                } else {
                    selectEl.value = selectEl.getAttribute('data-prev') || 'pending_call';
                    Swal.fire('Error', res.data.message || 'Failed to update call status', 'error');
                }
            })
            .catch(() => {
                selectEl.disabled = false;
                selectEl.value = selectEl.getAttribute('data-prev') || 'pending_call';
                Swal.fire('Error', 'Server connection error', 'error');
            });
    }

    window.quickChangeCallStatus = quickChangeCallStatus;

    function fastConfirmCall(orderId) {
        axios.post(`/admin/orders/${orderId}/ajax-call-status`, { call_status: 'confirmed' })
            .then(res => {
                if (res.data && res.data.success) {
                    if (res.data.workflowCounts) {
                        updateWorkflowBadges(res.data.workflowCounts, res.data.todayStats);
                    }
                    showToast('success', 'Order confirmed & ready to pack');
                    if (ordersTable) ordersTable.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.data.message || 'Failed to update', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Server connection error', 'error');
            });
    }

    function fastNoAnswer(orderId) {
        axios.post(`/admin/orders/${orderId}/ajax-call-status`, { call_status: 'no_answer' })
            .then(res => {
                if (res.data && res.data.success) {
                    if (res.data.workflowCounts) {
                        updateWorkflowBadges(res.data.workflowCounts, res.data.todayStats);
                    }
                    showToast('warning', 'Marked as No Answer');
                    if (ordersTable) ordersTable.ajax.reload(null, false);
                }
            });
    }

    function fastCancelCall(orderId) {
        Swal.fire({
            title: 'Cancel this order?',
            text: 'Are you sure you want to mark this order as cancelled?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel Order',
            cancelButtonText: 'No, Keep It',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/admin/orders/${orderId}/ajax-call-status`, { call_status: 'cancelled_on_call' })
                    .then(() => {
                        axios.post(`/admin/orders/${orderId}/ajax-status`, { status: 'cancelled' }).then(() => {
                            showToast('info', 'Order cancelled');
                            if (ordersTable) ordersTable.ajax.reload(null, false);
                        });
                    });
            }
        });
    }

    function fastCourierDispatch(orderId) {
        Swal.fire({
            title: 'Dispatch to Courier',
            text: 'Select courier partner for immediate handover:',
            input: 'select',
            inputOptions: {
                'steadfast': 'Steadfast Courier',
                'pathao': 'Pathao Courier',
                'redx': 'RedX Logistics'
            },
            inputValue: 'steadfast',
            showCancelButton: true,
            confirmButtonText: 'Handover & Dispatch',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const provider = result.value;
                axios.post(`/admin/logistics/dispatch/${orderId}`, { provider: provider })
                    .then(res => {
                        if (res.data && res.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Parcel Dispatched!',
                                text: 'Consignment ID: ' + (res.data.tracking_code || 'Generated'),
                                timer: 2000,
                                showConfirmButton: false
                            });
                            if (ordersTable) ordersTable.ajax.reload(null, false);
                        } else {
                            axios.post(`/admin/orders/${orderId}/ajax-status`, { status: 'shipped' })
                                .then(() => {
                                    showToast('success', 'Status updated to Shipped');
                                    if (ordersTable) ordersTable.ajax.reload(null, false);
                                });
                        }
                    })
                    .catch(() => {
                        axios.post(`/admin/orders/${orderId}/ajax-status`, { status: 'shipped' })
                            .then(() => {
                                showToast('success', 'Status updated to Shipped');
                                if (ordersTable) ordersTable.ajax.reload(null, false);
                            });
                    });
            }
        });
    }

    function executeBulkCallStatus(status) {
        if (!selectedOrderIds.length) return;
        
        axios.post('/admin/orders/bulk-call-status', {
            order_ids: selectedOrderIds,
            call_status: status
        }).then(res => {
            if (res.data && res.data.success) {
                showToast('success', res.data.message || 'Bulk update completed');
                clearSelection();
                if (ordersTable) ordersTable.ajax.reload(null, false);
            }
        }).catch(() => {
            Swal.fire('Error', 'Bulk update failed', 'error');
        });
    }

    function openBulkCourierModal() {
        if (!selectedOrderIds.length) return;

        Swal.fire({
            title: `Dispatch ${selectedOrderIds.length} Orders`,
            text: 'Select courier service for all selected parcels:',
            input: 'select',
            inputOptions: {
                'steadfast': 'Steadfast Courier',
                'pathao': 'Pathao Logistics',
                'redx': 'RedX Delivery'
            },
            inputValue: 'steadfast',
            showCancelButton: true,
            confirmButtonText: 'Dispatch All',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: (provider) => {
                return axios.post('/admin/logistics/bulk-dispatch', {
                    order_ids: selectedOrderIds,
                    provider: provider,
                    _token: '{{ csrf_token() }}'
                }).then(res => {
                    return res.data;
                }).catch(err => {
                    Swal.showValidationMessage(err.response?.data?.message || 'Bulk dispatch failed');
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const data = result.value;
                Swal.fire({
                    icon: data.success ? 'success' : 'warning',
                    title: 'Bulk Dispatch Completed',
                    text: data.message || `${data.success_count || 0} orders booked.`
                });
                clearSelection();
                if (ordersTable) ordersTable.ajax.reload(null, false);
            }
        });
    }

    function renderFraudCheckCard(ord, courierProfile) {
        const score = Number(ord.fraud_score) || 0;
        const fraudStatus = ord.fraud_status || (score >= 60 ? 'high_risk' : (score >= 30 ? 'suspicious' : 'safe'));
        const summary = (courierProfile && courierProfile.data && courierProfile.data.summary) ? courierProfile.data.summary : null;
        const reports = (courierProfile && courierProfile.reports) ? courierProfile.reports : [];
        const reportsCount = reports.length;

        let badgeClass = 'bg-success-subtle text-success border-success-subtle';
        let iconClass = 'fa-solid fa-shield-halved';
        let statusText = 'Low Risk (Safe)';

        if (fraudStatus === 'high_risk' || score >= 60 || reportsCount > 0) {
            badgeClass = 'bg-danger-subtle text-danger border-danger-subtle';
            iconClass = 'fa-solid fa-triangle-exclamation';
            statusText = `High Risk (${score}/100)`;
        } else if (fraudStatus === 'suspicious' || score >= 30) {
            badgeClass = 'bg-warning-subtle text-warning border-warning-subtle';
            iconClass = 'fa-solid fa-circle-exclamation';
            statusText = `Review Needed (${score}/100)`;
        }

        let courierInfoHtml = '';
        if (summary && summary.total_parcel > 0) {
            const total = summary.total_parcel;
            const successRatio = Math.round(summary.success_ratio || 0);
            const cancelled = summary.cancelled_parcel || 0;
            const delivered = total - cancelled;
            const ratioClass = successRatio >= 80 ? 'text-success' : (successRatio >= 60 ? 'text-warning' : 'text-danger');

            courierInfoHtml = `
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2 pt-2 border-top border-secondary border-opacity-10 small font-monospace" style="font-size: 0.74rem;">
                    <div>
                        <span class="text-muted">Courier Success:</span>
                        <span class="fw-bold ${ratioClass} ms-1">${successRatio}%</span>
                        <span class="text-muted ms-1">(${delivered}/${total} delivered)</span>
                    </div>
                    ${reportsCount > 0 ? `<span class="badge bg-danger text-white px-2 py-0.5 rounded-pill">${reportsCount} Merchant Report(s)</span>` : `<span class="badge bg-success-subtle text-success px-2 py-0.5 border rounded-pill">0 Reports</span>`}
                </div>
            `;
        } else {
            courierInfoHtml = `
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2 pt-2 border-top border-secondary border-opacity-10 small font-monospace" style="font-size: 0.73rem;">
                    <span class="text-muted"><i class="fa-regular fa-circle-check text-success me-1"></i>Courier Profile: Clean (No negative records)</span>
                    <span class="badge bg-secondary-subtle text-body border px-2 py-0.5 rounded-pill font-monospace">Verified</span>
                </div>
            `;
        }

        let notesHtml = '';
        if (ord.fraud_notes) {
            notesHtml = `<div class="text-muted small mt-2 pt-1 font-monospace text-truncate" style="font-size: 0.72rem;" title="${ord.fraud_notes}"><i class="fa-solid fa-circle-info me-1 text-info"></i>${ord.fraud_notes}</div>`;
        }

        return `
            <div class="fraud-status-card">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span class="small fw-bold text-uppercase text-muted d-flex align-items-center gap-1.5" style="font-size: 0.73rem;">
                        <i class="fa-solid fa-shield-halved text-primary"></i>
                        <span>Fraud & Delivery Risk</span>
                    </span>
                    <span class="badge ${badgeClass} border rounded-pill px-2.5 py-0.5 font-monospace fw-bold" style="font-size: 0.72rem;">
                        <i class="${iconClass} me-1"></i>${statusText}
                    </span>
                </div>
                ${courierInfoHtml}
                ${notesHtml}
            </div>
        `;
    }

    function openQuickOrderModal(orderId) {
        document.getElementById('modalOrderNumber').innerText = 'Loading...';
        document.getElementById('modalOrderDate').innerText = '';
        document.getElementById('modalHeaderTotal').innerText = '৳ 0';
        document.getElementById('quickOrderModalBody').innerHTML = `
            <div class="admin-modal-loader">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        `;
        document.getElementById('quickOrderModalFooter').innerHTML = '';
        openAdminModal('quickOrderModal');

        axios.get(`/admin/orders/${orderId}/ajax-details`)
            .then(res => {
                if (!res.data || !res.data.success) {
                    document.getElementById('quickOrderModalBody').innerHTML = '<div class="alert alert-danger">Failed to load order details.</div>';
                    return;
                }

                const ord = res.data.order;
                const items = res.data.items || [];
                const urls = res.data.urls || {};
                const courierProfile = res.data.courierProfile || null;

                document.getElementById('modalOrderNumber').innerText = `#${ord.order_number || ord.id}`;
                document.getElementById('modalOrderDate').innerText = ord.created_at_formatted ? `Placed: ${ord.created_at_formatted}` : '';
                document.getElementById('modalHeaderTotal').innerText = `৳ ${Math.round(ord.total).toLocaleString()}`;

                const stageBadge = document.getElementById('modalStageBadge');
                if (stageBadge) {
                    if (ord.call_status === 'confirmed') {
                        stageBadge.innerText = 'Confirmed';
                        stageBadge.className = 'badge bg-success-subtle text-success border rounded-pill px-2.5 py-0.5';
                    } else if (ord.call_status === 'no_answer') {
                        stageBadge.innerText = 'No Answer';
                        stageBadge.className = 'badge bg-warning-subtle text-warning border rounded-pill px-2.5 py-0.5';
                    } else {
                        stageBadge.innerText = 'Pending Call';
                        stageBadge.className = 'badge bg-warning-subtle text-warning border rounded-pill px-2.5 py-0.5';
                    }
                }

                const fraudBadge = document.getElementById('modalFraudBadge');
                if (fraudBadge) {
                    const score = Number(ord.fraud_score) || 0;
                    const reports = (courierProfile && courierProfile.reports) ? courierProfile.reports : [];
                    if (ord.fraud_status === 'high_risk' || score >= 60 || reports.length > 0) {
                        fraudBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5 font-monospace';
                        fraudBadge.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i>High Risk (${score})`;
                    } else if (ord.fraud_status === 'suspicious' || score >= 30) {
                        fraudBadge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-0.5 font-monospace';
                        fraudBadge.innerHTML = `<i class="fa-solid fa-circle-exclamation me-1"></i>Review (${score})`;
                    } else {
                        fraudBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 font-monospace';
                        fraudBadge.innerHTML = `<i class="fa-solid fa-shield-halved me-1"></i>Safe Buyer`;
                    }
                }

                let itemsHtml = '';
                items.forEach(it => {
                    itemsHtml += `
                        <div class="product-row-card">
                            <div class="d-flex align-items-center gap-3 overflow-hidden flex-grow-1" style="min-width: 0;">
                                <img src="${it.product_image}" class="product-thumb-img" onerror="this.src='/images/product-placeholder.svg'">
                                <div class="overflow-hidden flex-grow-1" style="min-width: 0;">
                                    <div class="fw-bold text-body text-truncate mb-1" style="font-size: 0.88rem;" title="${it.product_title}">${it.product_title}</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary-subtle text-body font-monospace border" style="font-size: 0.72rem;">Qty: ${it.quantity}</span>
                                        <span class="text-muted small font-monospace" style="font-size: 0.78rem;">৳ ${Math.round(it.unit_price).toLocaleString()} each</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="fw-bold font-monospace text-body" style="font-size: 0.98rem;">৳ ${Math.round(it.total_price).toLocaleString()}</span>
                            </div>
                        </div>
                    `;
                });

                document.getElementById('quickOrderModalBody').innerHTML = `
                    <div class="view-modal-layout">
                        <div class="panel-left-side">
                            <div class="panel-header">
                                <span class="small fw-bold text-uppercase text-muted d-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                                    <i class="fa-solid fa-user-shield text-primary"></i>
                                    <span>Recipient & Shipping Info</span>
                                </span>
                            </div>

                            <div class="panel-scroll-content custom-scroll" id="quickOrderLeftScroll">
                                <div class="d-flex flex-column gap-2">
                                    <div class="info-block-card">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <span class="fw-bold text-body fs-6">${ord.customer_name || 'Guest Customer'}</span>
                                            <span class="badge bg-secondary-subtle text-body border font-monospace" style="font-size: 0.72rem;">${ord.district || 'Dhaka'}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="tel:${ord.customer_phone}" class="btn btn-sm btn-outline-success px-2.5 py-0.5 rounded-pill font-monospace fw-bold d-inline-flex align-items-center gap-1" style="font-size: 0.8rem; height: 28px;">
                                                <i class="fa-solid fa-phone fa-xs"></i>
                                                <span>${ord.customer_phone || 'No phone'}</span>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary p-0 rounded-circle" onclick="copyPhoneText('${ord.customer_phone || ''}')" data-bs-toggle="tooltip" data-bs-title="Copy Phone" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-regular fa-copy fa-xs"></i>
                                            </button>
                                        </div>
                                    </div>

                                    ${renderFraudCheckCard(ord, courierProfile)}

                                    <div class="info-block-card">
                                        <span class="small fw-bold text-uppercase text-muted d-flex align-items-center gap-1 mb-1.5" style="font-size: 0.68rem;">
                                            <i class="fa-solid fa-location-dot text-danger"></i>
                                            <span>Delivery Address</span>
                                        </span>
                                        <div class="text-body small" style="line-height: 1.45; font-size: 0.84rem;">${ord.customer_address || 'No address specified'}</div>
                                    </div>

                                    ${ord.call_note || ord.notes ? `
                                        <div class="info-block-card">
                                            <span class="small fw-bold text-uppercase text-muted d-flex align-items-center gap-1 mb-1.5" style="font-size: 0.68rem;">
                                                <i class="fa-solid fa-clipboard-list text-primary"></i>
                                                <span>Special Instructions / Call Note</span>
                                            </span>
                                            <div class="text-body small" style="font-size: 0.82rem; line-height: 1.4;">${ord.call_note || ord.notes}</div>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>

                        <div class="panel-right-side">
                            <div class="panel-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="small fw-bold text-uppercase text-muted d-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-cart-shopping text-primary"></i>
                                        <span>Ordered Products (${items.length})</span>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-primary px-2.5 py-0.5 rounded-pill fw-semibold" onclick="closeAdminModal('quickOrderModal'); openEditOrderModal(${ord.id});" style="font-size: 0.74rem;">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Order
                                    </button>
                                </div>
                            </div>

                            <div class="panel-scroll-content custom-scroll" id="quickOrderItemsScroll">
                                ${itemsHtml}
                            </div>

                            <div class="panel-sticky-footer">
                                <div class="billing-summary-grid">
                                    <div class="summary-tile">
                                        <span class="summary-tile-label">Subtotal</span>
                                        <span class="summary-tile-value">৳ ${Math.round(ord.subtotal).toLocaleString()}</span>
                                    </div>
                                    <div class="summary-tile">
                                        <span class="summary-tile-label">Delivery Fee</span>
                                        <span class="summary-tile-value text-body">+৳ ${Math.round(ord.shipping_cost).toLocaleString()}</span>
                                    </div>
                                    <div class="summary-tile">
                                        <span class="summary-tile-label">Discount</span>
                                        <span class="summary-tile-value text-success">-৳ ${Math.round(ord.discount).toLocaleString()}</span>
                                    </div>
                                    <div class="summary-tile summary-tile-total">
                                        <span class="summary-tile-label">Total Payable</span>
                                        <span class="summary-tile-value">৳ ${Math.round(ord.total).toLocaleString()}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                setTimeout(() => {
                    const leftScroll = document.getElementById('quickOrderLeftScroll');
                    const itemsScroll = document.getElementById('quickOrderItemsScroll');
                    if (leftScroll) leftScroll.scrollTop = 0;
                    if (itemsScroll) itemsScroll.scrollTop = 0;
                }, 10);

                document.getElementById('quickOrderModalFooter').innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-success px-3.5 py-1.5 fw-bold" onclick="fastConfirmCall(${ord.id}); closeAdminModal('quickOrderModal');">
                            <i class="fa-solid fa-check me-1"></i> Confirm Call
                        </button>
                        <button type="button" class="btn btn-sm btn-primary px-3.5 py-1.5 fw-bold" onclick="fastCourierDispatch(${ord.id}); closeAdminModal('quickOrderModal');">
                            <i class="fa-solid fa-truck-fast me-1"></i> Dispatch Courier
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning px-3 py-1.5 fw-bold" onclick="closeAdminModal('quickOrderModal'); openEditOrderModal(${ord.id});">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" onclick="printPackingSlipDirect('${urls.packing_slip || ''}')" class="btn btn-sm btn-outline-secondary px-3 py-1.5 fw-semibold" data-bs-toggle="tooltip" data-bs-title="Print Shipping Label">
                            <i class="fa-solid fa-print me-1"></i> Shipping Label
                        </button>
                        <button type="button" onclick="printInvoiceDirect('${urls.invoice || ''}')" class="btn btn-sm btn-outline-secondary px-3 py-1.5 fw-semibold" data-bs-toggle="tooltip" data-bs-title="Print Customer Invoice">
                            <i class="fa-solid fa-file-invoice me-1"></i> Invoice
                        </button>
                        <a href="${urls.show || '#'}" class="btn btn-sm btn-outline-primary px-3 py-1.5 fw-semibold">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Details
                        </a>
                    </div>
                `;
                setTimeout(function () {
                    initTooltips(document.getElementById('quickOrderModal'));
                }, 50);
            })
            .catch(() => {
                document.getElementById('quickOrderModalBody').innerHTML = '<div class="alert alert-danger">Error loading order.</div>';
            });
    }

    function openEditOrderModal(orderId) {
        currentEditingOrderId = orderId;
        document.getElementById('editModalOrderNumber').innerText = 'Loading order...';
        document.getElementById('editModalHeaderTotal').innerText = '৳ 0';
        document.getElementById('editOrderModalBody').innerHTML = `
            <div class="admin-modal-loader">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        `;
        openAdminModal('editOrderModal');

        axios.get(`/admin/orders/${orderId}/ajax-details`)
            .then(res => {
                if (!res.data || !res.data.success) {
                    document.getElementById('editOrderModalBody').innerHTML = '<div class="alert alert-danger">Failed to load order.</div>';
                    return;
                }

                const ord = res.data.order;
                const courierProfile = res.data.courierProfile || null;
                currentEditItems = (res.data.items || []).map(it => ({
                    product_id: it.product_id,
                    product_title: it.product_title,
                    product_image: it.product_image,
                    unit_price: Number(it.unit_price) || 0,
                    quantity: Number(it.quantity) || 1
                }));

                document.getElementById('editModalOrderNumber').innerText = `Edit Order #${ord.order_number || ord.id}`;
                document.getElementById('editModalOrderDate').innerText = ord.created_at_formatted ? `Placed: ${ord.created_at_formatted}` : '';
                document.getElementById('editModalStatusBadge').innerText = (ord.call_status === 'confirmed' ? 'Confirmed' : (ord.call_status === 'no_answer' ? 'No Answer' : 'Pending Call'));

                const fraudBadge = document.getElementById('editModalFraudBadge');
                if (fraudBadge) {
                    const score = Number(ord.fraud_score) || 0;
                    const reports = (courierProfile && courierProfile.reports) ? courierProfile.reports : [];
                    if (ord.fraud_status === 'high_risk' || score >= 60 || reports.length > 0) {
                        fraudBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5 font-monospace';
                        fraudBadge.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i>High Risk (${score})`;
                    } else if (ord.fraud_status === 'suspicious' || score >= 30) {
                        fraudBadge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-0.5 font-monospace';
                        fraudBadge.innerHTML = `<i class="fa-solid fa-circle-exclamation me-1"></i>Review (${score})`;
                    } else {
                        fraudBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 font-monospace';
                        fraudBadge.innerHTML = `<i class="fa-solid fa-shield-halved me-1"></i>Safe Buyer`;
                    }
                }

                renderEditOrderForm(ord, courierProfile);
            })
            .catch(() => {
                document.getElementById('editOrderModalBody').innerHTML = '<div class="alert alert-danger">Server connection error.</div>';
            });
    }

    function renderEditOrderForm(ord, courierProfile) {
        const rawNote = ord.call_note || ord.notes || '';
        const notesList = ['⚡ Urgent', '🏢 Office Address', '📞 Call Before Delivery', '📦 Open & Check First'];

        let chipsHtml = '';
        notesList.forEach(noteTag => {
            const isSelected = rawNote.includes(noteTag);
            const btnClass = isSelected ? 'active' : '';
            chipsHtml += `
                <button type="button" class="instruction-tag ${btnClass}" data-tag="${noteTag}" onclick="toggleEditNote('${noteTag}')">
                    ${noteTag}
                </button>
            `;
        });

        document.getElementById('editOrderModalBody').innerHTML = `
            <div class="edit-modal-layout">
                <div class="panel-left-side">
                    <div class="panel-header">
                        <span class="small fw-bold text-uppercase text-muted d-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-user-gear text-primary"></i>
                            <span>Customer & Logistics Details</span>
                        </span>
                    </div>

                    <div class="panel-scroll-content custom-scroll" id="editCustomerScroll">
                        <div class="d-flex flex-column gap-2">
                            <div>
                                <label class="form-label">Customer Full Name</label>
                                <input type="text" id="editCustomerName" class="form-control" value="${ord.customer_name || ''}" placeholder="Customer full name">
                            </div>

                            <div>
                                <label class="form-label">Contact Phone</label>
                                <div class="input-group">
                                    <input type="text" id="editCustomerPhone" class="form-control font-monospace fw-semibold" value="${ord.customer_phone || ''}" placeholder="017XXXXXXXX">
                                    <a href="tel:${ord.customer_phone || ''}" class="btn btn-outline-success" data-bs-toggle="tooltip" data-bs-title="Dial Phone"><i class="fa-solid fa-phone"></i></a>
                                </div>
                            </div>

                            ${renderFraudCheckCard(ord, courierProfile)}

                            <div>
                                <label class="form-label">Delivery Address</label>
                                <textarea id="editCustomerAddress" class="form-control" rows="2" placeholder="House, Road, Area, Landmark...">${ord.customer_address || ''}</textarea>
                            </div>

                            <div class="pt-2 border-top border-secondary border-opacity-10">
                                <div class="small fw-bold text-uppercase text-muted mb-1.5 d-flex align-items-center gap-1.5" style="font-size: 0.7rem;">
                                    <i class="fa-solid fa-truck-fast text-primary"></i>
                                    <span>Delivery Logistics</span>
                                </div>
                                <div class="d-flex gap-1.5 mb-2">
                                    <button type="button" class="zone-preset-btn ${(!ord.district || ord.district.toLowerCase().includes('dhaka') || ord.district.includes('ঢাকা')) ? 'active' : ''}" onclick="setPresetArea('Dhaka', 60, this)">
                                        Dhaka (৳60)
                                    </button>
                                    <button type="button" class="zone-preset-btn" onclick="setPresetArea('Outside Dhaka', 120, this)">
                                        Outside (৳120)
                                    </button>
                                    <button type="button" class="zone-preset-btn" onclick="setPresetArea('Free Delivery', 0, this)">
                                        Free (৳0)
                                    </button>
                                </div>
                                <label class="form-label">Destination District</label>
                                <input type="text" id="editDistrict" class="form-control" value="${ord.district || 'Dhaka'}" placeholder="City / District">
                            </div>

                            <div class="pt-2 border-top border-secondary border-opacity-10">
                                <div class="small fw-bold text-uppercase text-muted mb-1.5 d-flex align-items-center gap-1.5" style="font-size: 0.7rem;">
                                    <i class="fa-solid fa-clipboard-list text-primary"></i>
                                    <span>Handling Instructions</span>
                                </div>
                                <div class="d-flex gap-1.5 flex-wrap mb-2" id="instructionChipsContainer">
                                    ${chipsHtml}
                                </div>
                                <textarea id="editCallNote" class="form-control" rows="2" placeholder="Special delivery instructions..." oninput="syncNoteChips()">${rawNote}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-right-side">
                    <div class="panel-header">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-uppercase text-muted d-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-cart-shopping text-primary"></i>
                                <span>Ordered Products (<span id="editItemCountBadge" class="text-body font-monospace">0</span>)</span>
                            </span>
                            <span class="small text-muted font-monospace" style="font-size: 0.7rem;">Live Quantity Stepper</span>
                        </div>

                        <div class="position-relative">
                            <div class="product-search-box">
                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                <input type="text" id="editProductSearchInput" class="form-control" placeholder="Search product name or SKU to add..." oninput="handleProductSearch(this.value)">
                            </div>
                            <div class="search-product-dropdown custom-scroll" id="editProductSearchDropdown"></div>
                        </div>
                    </div>

                    <div class="panel-scroll-content custom-scroll" id="editItemsList">
                    </div>

                    <div class="panel-sticky-footer">
                        <div class="billing-summary-grid">
                            <div class="summary-tile">
                                <span class="summary-tile-label">Subtotal</span>
                                <span class="summary-tile-value" id="editSummarySubtotal">৳ 0</span>
                            </div>
                            <div class="summary-tile summary-tile-editable">
                                <span class="summary-tile-label">Delivery Fee</span>
                                <div class="summary-tile-input-box">
                                    <span class="currency-sign">৳</span>
                                    <input type="number" id="editShippingCost" class="summary-inline-input" value="${Math.round(ord.shipping_cost || 0)}" min="0" oninput="recalcEditTotals()">
                                </div>
                            </div>
                            <div class="summary-tile summary-tile-editable">
                                <span class="summary-tile-label">Discount</span>
                                <div class="summary-tile-input-box text-success">
                                    <span class="currency-sign text-success">-৳</span>
                                    <input type="number" id="editDiscount" class="summary-inline-input text-success" value="${Math.round(ord.discount || 0)}" min="0" oninput="recalcEditTotals()">
                                </div>
                            </div>
                            <div class="summary-tile summary-tile-total">
                                <span class="summary-tile-label">Total Payable</span>
                                <span class="summary-tile-value" id="editCalculatedTotal">৳ 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        renderCurrentEditItems();
        recalcEditTotals();

        setTimeout(() => {
            const leftPane = document.getElementById('editCustomerScroll');
            const rightPane = document.getElementById('editItemsList');
            if (leftPane) leftPane.scrollTop = 0;
            if (rightPane) rightPane.scrollTop = 0;
        }, 10);
    }

    function renderCurrentEditItems() {
        const listEl = document.getElementById('editItemsList');
        const badge = document.getElementById('editItemCountBadge');
        if (!listEl) return;

        if (badge) badge.innerText = `${currentEditItems.length}`;

        if (currentEditItems.length === 0) {
            listEl.innerHTML = `<div class="text-center py-5 text-danger small"><i class="fa-solid fa-triangle-exclamation fa-2x mb-2"></i><br>No items in order. Search and add at least 1 product above.</div>`;
            return;
        }

        let html = '';
        currentEditItems.forEach((it, idx) => {
            const itemTotal = it.unit_price * it.quantity;
            html += `
                <div class="product-row-card">
                    <div class="d-flex align-items-center gap-3 overflow-hidden flex-grow-1" style="min-width: 0;">
                        <img src="${it.product_image || '/images/product-placeholder.svg'}" class="product-thumb-img" onerror="this.src='/images/product-placeholder.svg'">
                        <div class="overflow-hidden flex-grow-1" style="min-width: 0;">
                            <div class="fw-bold text-body text-truncate mb-1" style="font-size: 0.88rem;" title="${it.product_title}">${it.product_title}</div>
                            <div class="text-muted font-monospace small" style="font-size: 0.78rem;">৳ ${Math.round(it.unit_price).toLocaleString()} each</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2.5 flex-shrink-0">
                        <div class="qty-stepper">
                            <button type="button" onclick="changeItemQty(${idx}, -1)">−</button>
                            <input type="text" value="${it.quantity}" readonly>
                            <button type="button" onclick="changeItemQty(${idx}, 1)">+</button>
                        </div>
                        <div class="text-end font-monospace fw-bold text-body" style="min-width: 72px; font-size: 0.98rem;">
                            ৳ ${Math.round(itemTotal).toLocaleString()}
                        </div>
                        <button type="button" class="item-delete-btn flex-shrink-0" onclick="removeItemFromEdit(${idx})" data-bs-toggle="tooltip" data-bs-title="Remove item">
                            <i class="fa-solid fa-trash-can fa-xs"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        listEl.innerHTML = html;
        setTimeout(function () {
            initTooltips(listEl);
        }, 50);
    }

    function changeItemQty(idx, delta) {
        if (!currentEditItems[idx]) return;
        currentEditItems[idx].quantity = Math.max(1, currentEditItems[idx].quantity + delta);
        renderCurrentEditItems();
        recalcEditTotals();
    }

    function removeItemFromEdit(idx) {
        if (currentEditItems.length <= 1) {
            Swal.fire('Notice', 'An order must contain at least 1 product item.', 'warning');
            return;
        }
        currentEditItems.splice(idx, 1);
        renderCurrentEditItems();
        recalcEditTotals();
    }

    function handleProductSearch(query) {
        clearTimeout(productSearchTimer);
        const dropdown = document.getElementById('editProductSearchDropdown');
        if (!dropdown) return;

        if (!query || query.trim().length < 2) {
            dropdown.style.display = 'none';
            return;
        }

        productSearchTimer = setTimeout(() => {
            axios.get(`/admin/orders/search/products?q=${encodeURIComponent(query.trim())}`)
                .then(res => {
                    const prods = res.data || [];
                    if (prods.length === 0) {
                        dropdown.innerHTML = `<div class="p-3 text-muted small text-center font-monospace" style="background: var(--surface-2);">No products found for "${query}"</div>`;
                        dropdown.style.display = 'block';
                        return;
                    }

                    let html = '';
                    prods.forEach(p => {
                        const img = p.image || '/images/product-placeholder.svg';
                        const safeTitle = (p.text || '').replace(/'/g, "\\'");
                        html += `
                            <div class="search-product-item" onclick="addProductToEditOrder(${p.id}, '${safeTitle}', '${img}', ${p.price || 0})">
                                <img src="${img}" class="product-thumb-img" style="width: 38px; height: 38px;" onerror="this.src='/images/product-placeholder.svg'">
                                <div class="overflow-hidden flex-grow-1">
                                    <div class="text-body fw-bold small text-truncate">${p.text}</div>
                                    <span class="text-muted small font-monospace" style="font-size: 0.75rem;">৳ ${Math.round(p.price || 0)}</span>
                                </div>
                                <span class="badge bg-primary rounded-pill px-2 py-1 small fw-bold" style="font-size: 0.72rem;">+ Add</span>
                            </div>
                        `;
                    });

                    dropdown.innerHTML = html;
                    dropdown.style.display = 'block';
                })
                .catch(() => {
                    dropdown.style.display = 'none';
                });
        }, 250);
    }

    function addProductToEditOrder(id, title, img, price) {
        const dropdown = document.getElementById('editProductSearchDropdown');
        if (dropdown) dropdown.style.display = 'none';
        const searchInput = document.getElementById('editProductSearchInput');
        if (searchInput) searchInput.value = '';

        const existing = currentEditItems.find(it => it.product_id == id);
        if (existing) {
            existing.quantity += 1;
        } else {
            currentEditItems.push({
                product_id: id,
                product_title: title,
                product_image: img,
                unit_price: Number(price) || 0,
                quantity: 1
            });
        }

        renderCurrentEditItems();
        recalcEditTotals();
    }

    function setPresetArea(district, shippingCost, btnEl) {
        const distInput = document.getElementById('editDistrict');
        const shipInput = document.getElementById('editShippingCost');
        if (distInput) distInput.value = district;
        if (shipInput) shipInput.value = Math.round(shippingCost);

        document.querySelectorAll('.zone-preset-btn').forEach(b => {
            b.classList.remove('active');
        });
        if (btnEl) {
            btnEl.classList.add('active');
        }

        recalcEditTotals();
    }

    function toggleEditNote(noteText) {
        const noteInput = document.getElementById('editCallNote');
        if (!noteInput) return;
        let current = noteInput.value.trim();
        let parts = current ? current.split(',').map(s => s.trim()).filter(Boolean) : [];
        const index = parts.indexOf(noteText);
        if (index > -1) {
            parts.splice(index, 1);
        } else {
            parts.push(noteText);
        }
        noteInput.value = parts.join(', ');
        syncNoteChips();
    }

    function syncNoteChips() {
        const noteInput = document.getElementById('editCallNote');
        if (!noteInput) return;
        const current = noteInput.value;
        document.querySelectorAll('.instruction-tag').forEach(btn => {
            const tag = btn.getAttribute('data-tag');
            if (tag && current.includes(tag)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    function recalcEditTotals() {
        let subtotal = 0;
        currentEditItems.forEach(it => {
            subtotal += (it.unit_price * it.quantity);
        });

        const shippingEl = document.getElementById('editShippingCost');
        const discountEl = document.getElementById('editDiscount');
        const shipping = shippingEl ? (Number(shippingEl.value) || 0) : 0;
        const discount = discountEl ? (Number(discountEl.value) || 0) : 0;

        const total = Math.max(0, Math.round(subtotal + shipping - discount));

        const summarySubtotalEl = document.getElementById('editSummarySubtotal');
        const calcTotalEl = document.getElementById('editCalculatedTotal');
        const headerTotalEl = document.getElementById('editModalHeaderTotal');

        if (summarySubtotalEl) summarySubtotalEl.innerText = `৳ ${Math.round(subtotal).toLocaleString()}`;
        if (calcTotalEl) calcTotalEl.innerText = `৳ ${total.toLocaleString()}`;
        if (headerTotalEl) headerTotalEl.innerText = `৳ ${total.toLocaleString()}`;
    }

    function saveOrderEdits() {
        if (!currentEditingOrderId) return;
        if (!currentEditItems.length) {
            Swal.fire('Error', 'Please add at least one product item.', 'error');
            return;
        }

        const name = (document.getElementById('editCustomerName')?.value || '').trim();
        const phone = (document.getElementById('editCustomerPhone')?.value || '').trim();
        const addr = (document.getElementById('editCustomerAddress')?.value || '').trim();
        const dist = (document.getElementById('editDistrict')?.value || '').trim();
        const shipping = Math.round(Number(document.getElementById('editShippingCost')?.value) || 0);
        const discount = Math.round(Number(document.getElementById('editDiscount')?.value) || 0);
        const callNote = (document.getElementById('editCallNote')?.value || '').trim();

        if (!name || !phone || !addr) {
            Swal.fire('Incomplete Form', 'Please provide customer name, phone number, and address.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Updating Order...',
            text: 'Saving customer details and recalculating order totals...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        axios.post(`/admin/orders/${currentEditingOrderId}/ajax-update-full`, {
            customer_name: name,
            customer_phone: phone,
            customer_address: addr,
            district: dist,
            shipping_cost: shipping,
            discount: discount,
            call_note: callNote,
            items: currentEditItems
        }).then(res => {
            if (res.data && res.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Order Updated Successfully!',
                    text: 'Customer details, products and totals were saved.',
                    timer: 1600,
                    showConfirmButton: false
                });

                closeAdminModal('editOrderModal');

                if (ordersTable) ordersTable.ajax.reload(null, false);
            } else {
                Swal.fire('Error', res.data.message || 'Update failed', 'error');
            }
        }).catch(err => {
            const msg = err.response?.data?.message || 'Server error occurred while saving.';
            Swal.fire('Error', msg, 'error');
        });
    }

    function copyPhoneText(phone) {
        navigator.clipboard.writeText(phone).then(() => {
            showToast('success', 'Phone copied: ' + phone);
        });
    }

    function copyConsignmentText(text) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('success', 'Consignment ID copied: ' + text);
        });
    }

    function printDirect(url, docTitle) {
        if (!url || url === '#' || url === '') return;
        let frame = document.getElementById('printInvoiceFrame');
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'printInvoiceFrame';
            frame.style.position = 'fixed';
            frame.style.right = '0';
            frame.style.bottom = '0';
            frame.style.width = '0';
            frame.style.height = '0';
            frame.style.border = '0';
            frame.style.visibility = 'hidden';
            document.body.appendChild(frame);
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: docTitle || 'Opening print dialog...',
            showConfirmButton: false,
            timer: 1500
        });

        frame.src = url;
        frame.onload = function () {
            setTimeout(function () {
                try {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                } catch (e) {
                    window.open(url, '_blank');
                }
            }, 350);
        };
    }

    function printPackingSlipDirect(url) {
        printDirect(url, 'Opening shipping label print...');
    }

    function printInvoiceDirect(url) {
        printDirect(url, 'Opening invoice print...');
    }

    function initTooltips(container) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const root = container || document;
            root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                const existing = bootstrap.Tooltip.getInstance(el);
                if (existing) {
                    existing.dispose();
                }
                new bootstrap.Tooltip(el, {
                    boundary: 'window',
                    trigger: 'hover'
                });
            });
        }
    }

    function showToast(icon, title) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 1500
        });
    }

    function applyAdvancedFilters() {
        if (ordersTable) ordersTable.draw();
    }

    function setWorkflowStage(stage) {
        activeStage = stage;
        document.querySelectorAll('#stageTabsBar .pipeline-tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.stage === stage);
        });
        clearSelection();
        if (ordersTable) ordersTable.draw();
    }

    function handleSortChange(sortKey) {
        if (!ordersTable) return;
        if (sortKey === 'newest') {
            ordersTable.order([1, 'desc']).draw();
        } else if (sortKey === 'oldest') {
            ordersTable.order([1, 'asc']).draw();
        } else if (sortKey === 'amount_high') {
            ordersTable.order([4, 'desc']).draw();
        } else if (sortKey === 'amount_low') {
            ordersTable.order([4, 'asc']).draw();
        } else if (sortKey === 'customer_asc') {
            ordersTable.order([2, 'asc']).draw();
        }
    }

    function handlePageLengthChange(val) {
        const len = parseInt(val, 10) || 15;
        if (ordersTable) {
            ordersTable.page.len(len).draw();
        }
    }

    function resetAllFilters() {
        const dFrom = document.getElementById('filterDateFrom');
        if (dFrom) dFrom.value = '';
        const dTo = document.getElementById('filterDateTo');
        if (dTo) dTo.value = '';
        const sInp = document.getElementById('customSearchInput');
        if (sInp) sInp.value = '';
        const sortSel = document.getElementById('customSortOrder');
        if (sortSel) sortSel.value = 'newest';
        const pageSel = document.getElementById('customPageLength');
        if (pageSel) pageSel.value = '15';
        setWorkflowStage('all');
        if (ordersTable) {
            ordersTable.page.len(15);
            ordersTable.order([1, 'desc']);
            ordersTable.search('').draw();
        }
    }

    function refreshOrdersTable() {
        const icon = document.getElementById('refreshIcon');
        if (icon) icon.classList.add('fa-spin');
        fetchPipelineCounts();
        if (ordersTable) {
            ordersTable.ajax.reload(() => {
                if (icon) icon.classList.remove('fa-spin');
            }, false);
        } else {
            if (icon) icon.classList.remove('fa-spin');
        }
    }

    document.addEventListener('turbo:load', function () {
        initOrdersIndex();
        initTooltips();
    });
    document.addEventListener('DOMContentLoaded', function () {
        initOrdersIndex();
        initTooltips();
        setInterval(fetchPipelineCounts, 30000);
    });

    window.openAdminModal = openAdminModal;
    window.closeAdminModal = closeAdminModal;
    window.setWorkflowStage = setWorkflowStage;
    window.updateWorkflowBadges = updateWorkflowBadges;
    window.fetchPipelineCounts = fetchPipelineCounts;
    window.applyAdvancedFilters = applyAdvancedFilters;
    window.resetAllFilters = resetAllFilters;
    window.refreshOrdersTable = refreshOrdersTable;
    window.fastConfirmCall = fastConfirmCall;
    window.fastNoAnswer = fastNoAnswer;
    window.fastCancelCall = fastCancelCall;
    window.fastCourierDispatch = fastCourierDispatch;
    window.executeBulkCallStatus = executeBulkCallStatus;
    window.openBulkCourierModal = openBulkCourierModal;
    window.openQuickOrderModal = openQuickOrderModal;
    window.openEditOrderModal = openEditOrderModal;
    window.changeItemQty = changeItemQty;
    window.removeItemFromEdit = removeItemFromEdit;
    window.handleProductSearch = handleProductSearch;
    window.addProductToEditOrder = addProductToEditOrder;
    window.setPresetArea = setPresetArea;
    window.toggleEditNote = toggleEditNote;
    window.syncNoteChips = syncNoteChips;
    window.recalcEditTotals = recalcEditTotals;
    window.saveOrderEdits = saveOrderEdits;
    window.clearSelection = clearSelection;
    window.copyPhoneText = copyPhoneText;
    window.copyConsignmentText = copyConsignmentText;
    window.printPackingSlipDirect = printPackingSlipDirect;
    window.printInvoiceDirect = printInvoiceDirect;
    window.initTooltips = initTooltips;
    window.handleSortChange = handleSortChange;
    window.handlePageLengthChange = handlePageLengthChange;
})();
</script>
@endpush