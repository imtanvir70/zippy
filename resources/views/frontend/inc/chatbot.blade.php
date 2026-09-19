<style>
.zippy-chatbot-container {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 1060;
    font-family: var(--font-custom, 'Outfit', sans-serif);
}

.zippy-chat-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 1059;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.28s ease;
}

.zippy-chat-drag-handle {
    display: none;
}

.zippy-chat-window {
    background: #ffffff;
    overflow: hidden;
}

.zippy-chat-trigger {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #090d16 0%, #0f172a 60%, #1e293b 100%);
    color: #ffffff;
    border: 1.5px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 10px 25px -3px rgba(15, 23, 42, 0.6), 0 0 20px rgba(59, 130, 246, 0.35);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
    animation: zippyThemeRadar 3.2s infinite cubic-bezier(0.4, 0, 0.2, 1);
}

.zippy-chat-trigger:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 16px 35px -4px rgba(15, 23, 42, 0.75), 0 0 25px rgba(59, 130, 246, 0.55);
    border-color: rgba(59, 130, 246, 0.6);
}

.zippy-chat-trigger:active {
    transform: scale(0.95);
}

.zippy-chat-trigger .trigger-icon {
    font-size: 25px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.4));
    transition: transform 0.3s ease, opacity 0.3s ease;
    color: #ffffff !important;
}

.zippy-chat-trigger .close-icon {
    font-size: 22px;
    display: none;
    transition: transform 0.3s ease, opacity 0.3s ease;
    color: #ffffff !important;
}

.zippy-chatbot-container.active .zippy-chat-trigger .trigger-icon {
    display: none;
}

.zippy-chatbot-container.active .zippy-chat-trigger .close-icon {
    display: block;
}

.zippy-chat-pulse {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 14px;
    height: 14px;
    background-color: #10b981;
    border: 2.5px solid #0f172a;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.zippy-chat-pulse::after {
    content: '';
    position: absolute;
    top: -2.5px;
    left: -2.5px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background-color: rgba(16, 185, 129, 0.7);
    animation: zippyPulse 2s infinite ease-out;
}

@keyframes zippyPulse {
    0% { transform: scale(1); opacity: 0.8; }
    100% { transform: scale(2.2); opacity: 0; }
}

@keyframes zippyThemeRadar {
    0% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5), 0 10px 25px -3px rgba(15, 23, 42, 0.6);
    }
    50% {
        box-shadow: 0 0 0 12px rgba(59, 130, 246, 0), 0 12px 30px -3px rgba(15, 23, 42, 0.7);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0), 0 10px 25px -3px rgba(15, 23, 42, 0.6);
    }
}

.zippy-chat-tooltip {
    position: absolute;
    bottom: 74px;
    right: 0;
    width: 275px;
    background: #ffffff;
    border-radius: 18px;
    padding: 12px 14px;
    box-shadow: 0 12px 35px -5px rgba(15, 23, 42, 0.2), 0 4px 12px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    z-index: 140;
    cursor: pointer;
    transform-origin: bottom right;
    animation: zippyTooltipEntrance 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.zippy-chat-tooltip:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 40px -5px rgba(37, 99, 235, 0.25), 0 6px 16px rgba(0, 0, 0, 0.1);
}
.zippy-chat-tooltip::after {
    content: '';
    position: absolute;
    bottom: -7px;
    right: 22px;
    width: 14px;
    height: 14px;
    background: #ffffff;
    transform: rotate(45deg);
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}
.zippy-tooltip-close {
    position: absolute;
    top: 6px;
    right: 8px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #f1f5f9;
    border: none;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    cursor: pointer;
    transition: all 0.15s ease;
    z-index: 2;
}
.zippy-tooltip-close:hover {
    background: #0f172a;
    color: #ffffff;
}
.zippy-tooltip-content {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.zippy-tooltip-avatar {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14.5px;
    flex-shrink: 0;
    margin-top: 2px;
    position: relative;
}
.zippy-tooltip-avatar i {
    animation: zippySupportBounce 2.4s ease-in-out infinite;
    transform-origin: bottom center;
}
@keyframes zippySupportBounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0) scale(1);
    }
    40% {
        transform: translateY(-4px) scale(1.1);
    }
    60% {
        transform: translateY(-2px) scale(1.05);
    }
}
.zippy-avatar-online {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 8px;
    height: 8px;
    background-color: #10b981;
    border: 1.5px solid #ffffff;
    border-radius: 50%;
}
.zippy-tooltip-text {
    flex-grow: 1;
    padding-right: 12px;
}
.zippy-tooltip-title {
    display: block;
    font-size: 0.85rem;
    color: #0f172a;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 3px;
}
.zippy-tooltip-desc {
    font-size: 0.76rem;
    color: #475569;
    line-height: 1.35;
    margin: 0;
}
@keyframes zippyTooltipEntrance {
    0% {
        opacity: 0;
        transform: scale(0.85) translateY(10px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@media (max-width: 991.98px) {
    .zippy-chatbot-container {
        bottom: calc(78px + env(safe-area-inset-bottom, 0px)) !important;
        right: 16px !important;
        z-index: 1045;
    }
    .zippy-chat-trigger {
        position: relative !important;
        bottom: auto !important;
        right: auto !important;
        width: 52px !important;
        height: 52px !important;
        border: 2px solid rgba(255, 255, 255, 0.4) !important;
        box-shadow: 0 8px 22px -2px rgba(37, 99, 235, 0.55), 0 3px 10px rgba(0, 0, 0, 0.15) !important;
    }
    .zippy-chat-trigger .trigger-icon {
        font-size: 24px !important;
    }
    .zippy-chat-tooltip {
        bottom: 56px !important;
        right: 0 !important;
        width: min(260px, calc(100vw - 32px)) !important;
        max-width: calc(100vw - 32px) !important;
        padding: 9px 12px !important;
        border-radius: 14px !important;
    }
    .zippy-chat-tooltip::after {
        right: 16px !important;
    }
    .zippy-tooltip-title {
        font-size: 0.82rem !important;
    }
    .zippy-tooltip-desc {
        font-size: 0.74rem !important;
    }

    body:has(.mobile-floating-action-sheet) .zippy-chatbot-container,
    .has-floating-action-sheet .zippy-chatbot-container {
        bottom: calc(84px + env(safe-area-inset-bottom, 0px)) !important;
        right: 16px !important;
    }
    body:has(.mobile-floating-action-sheet) .zippy-chat-trigger,
    .has-floating-action-sheet .zippy-chat-trigger {
        width: 52px !important;
        height: 52px !important;
        border: 2px solid rgba(255, 255, 255, 0.4) !important;
    }
    body:has(.mobile-floating-action-sheet) .zippy-chat-trigger .trigger-icon,
    .has-floating-action-sheet .zippy-chat-trigger .trigger-icon {
        font-size: 24px !important;
    }

    body:has(.zk-mobile-bottom-bar) .zippy-chatbot-container,
    .zk-checkout-wrapper .zippy-chatbot-container {
        bottom: calc(84px + env(safe-area-inset-bottom, 0px)) !important;
        right: 16px !important;
    }
    body:has(.zk-mobile-bottom-bar) .zippy-chat-trigger,
    .zk-checkout-wrapper .zippy-chat-trigger {
        width: 52px !important;
        height: 52px !important;
        border: 2px solid rgba(255, 255, 255, 0.4) !important;
    }
    body:has(.zk-mobile-bottom-bar) .zippy-chat-trigger .trigger-icon,
    .zk-checkout-wrapper .zippy-chat-trigger .trigger-icon {
        font-size: 24px !important;
    }
    .zippy-chatbot-container.active {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100%;
        z-index: 1060;
        pointer-events: none;
    }
    .zippy-chat-backdrop {
        display: block;
    }
    .zippy-chatbot-container.active .zippy-chat-backdrop {
        opacity: 1;
        pointer-events: auto;
    }
    .zippy-chat-trigger {
        width: 52px;
        height: 52px;
    }
    .zippy-chat-trigger .trigger-icon {
        font-size: 25px !important;
    }
    .zippy-chatbot-container.active .zippy-chat-trigger {
        display: none !important;
    }
    .zippy-chat-window {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100vw;
        max-width: 100vw;
        height: 88vh;
        height: 88dvh;
        max-height: calc(100dvh - 36px);
        margin: 0;
        border-radius: 22px 22px 0 0;
        border-bottom: none;
        box-shadow: 0 -12px 36px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.08);
        z-index: 1062;
        display: flex;
        flex-direction: column;
        transform: translateY(100%);
        transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
        opacity: 0;
        pointer-events: none;
    }
    .zippy-chatbot-container.active .zippy-chat-window {
        transform: translateY(0);
        opacity: 1;
        pointer-events: auto;
    }
    .zippy-chat-drag-handle {
        display: block;
        width: 38px;
        height: 4px;
        background: rgba(255, 255, 255, 0.35);
        border-radius: 999px;
        margin: 4px auto 8px auto;
        flex-shrink: 0;
    }
    .zippy-chat-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 70%, #2563eb 100%);
        color: #ffffff;
        padding: 6px 16px 12px 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex-shrink: 0;
        border-radius: 22px 22px 0 0;
    }
    .zippy-chat-body {
        padding: 12px;
        -webkit-overflow-scrolling: touch;
    }
    .zippy-chat-footer {
        padding: 8px 12px calc(12px + env(safe-area-inset-bottom, 0px)) 12px;
    }
    .zippy-checkout-drawer {
        border-radius: 22px 22px 0 0;
    }
    .zippy-checkout-header {
        border-radius: 22px 22px 0 0;
    }
    .zippy-co-footer {
        padding: 10px 14px calc(14px + env(safe-area-inset-bottom, 0px)) 14px;
    }
}

@media (min-width: 768px) {
    .zippy-chat-window {
        position: absolute;
        bottom: 74px;
        right: 0;
        width: 395px;
        max-width: calc(100vw - 32px);
        height: min(570px, calc(100vh - 110px));
        max-height: calc(100vh - 110px);
        border-radius: 20px;
        box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.08);
        display: none;
        flex-direction: column;
        transform: translateY(15px) scale(0.96);
        opacity: 0;
        pointer-events: none;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .zippy-chatbot-container.active .zippy-chat-window {
        display: flex;
        transform: translateY(0) scale(1);
        opacity: 1;
        pointer-events: auto;
    }
    .zippy-chat-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 70%, #2563eb 100%);
        color: #ffffff;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
}

.zippy-chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    border: 1.5px solid rgba(255, 255, 255, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #38bdf8;
    flex-shrink: 0;
}

.zippy-chat-header-info {
    margin-left: 12px;
    flex-grow: 1;
}

.zippy-chat-title {
    font-size: 15px;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
    letter-spacing: 0.3px;
}

.zippy-chat-status {
    font-size: 11px;
    color: #94a3b8;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 5px;
}

.zippy-chat-status .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background-color: #22c55e;
}

.zippy-chat-header-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.zippy-lang-toggle {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 999px;
    padding: 2px;
    gap: 2px;
}

.zippy-lang-btn {
    border: none;
    background: transparent;
    color: rgba(255, 255, 255, 0.78);
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 999px;
    cursor: pointer;
    transition: all 0.2s ease;
    line-height: 1.35;
}

.zippy-lang-btn:hover {
    color: #ffffff;
}

.zippy-lang-btn.active {
    background: #ffffff;
    color: #0f172a;
    font-weight: 700;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.zippy-chat-btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.zippy-chat-btn-icon:hover {
    background: rgba(255, 255, 255, 0.22);
}

.zippy-chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.zippy-chat-message {
    display: flex;
    flex-direction: column;
    max-width: 90%;
    animation: zippyFadeIn 0.2s ease;
}

@keyframes zippyFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.zippy-chat-message.bot {
    align-self: flex-start;
}

.zippy-chat-message.user {
    align-self: flex-end;
}

.zippy-msg-bubble {
    padding: 11px 15px;
    border-radius: 16px;
    font-size: 13.5px;
    line-height: 1.5;
    word-break: break-word;
}

.zippy-chat-message.bot .zippy-msg-bubble {
    background: #ffffff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.zippy-chat-message.user .zippy-msg-bubble {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border-bottom-right-radius: 4px;
    box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
}

.zippy-msg-time {
    font-size: 10px;
    color: #94a3b8;
    margin-top: 3px;
    padding: 0 4px;
}

.zippy-chat-message.user .zippy-msg-time {
    align-self: flex-end;
}

.zippy-msg-bubble p:last-child {
    margin-bottom: 0;
}

.zippy-msg-bubble ul, .zippy-msg-bubble ol {
    margin-bottom: 0.5rem;
    padding-left: 1.2rem;
}

.zippy-msg-bubble a {
    color: #2563eb;
    font-weight: 600;
    text-decoration: underline;
}

.zippy-chat-message.user .zippy-msg-bubble a {
    color: #bae6fd;
}

.zippy-quick-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 4px;
}

.zippy-chip {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-size: 11.5px;
    font-weight: 600;
    padding: 6px 11px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.zippy-chip:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #1d4ed8;
    transform: translateY(-1px);
}

.zippy-chip.confirm-chip {
    background: #22c55e;
    color: #ffffff;
    border-color: #16a34a;
}

.zippy-chip.confirm-chip:hover {
    background: #16a34a;
    color: #ffffff;
}

.zippy-chip.cancel-chip {
    background: #ef4444;
    color: #ffffff;
    border-color: #dc2626;
}

.zippy-chip.cancel-chip:hover {
    background: #dc2626;
    color: #ffffff;
}

.zippy-product-card-compact {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 9px;
    margin-top: 8px;
    display: flex;
    gap: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: border-color 0.2s, box-shadow 0.2s;
}

.zippy-product-card-compact:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.12);
}

.zippy-product-compact-img {
    width: 58px;
    height: 58px;
    border-radius: 8px;
    object-fit: cover;
    background: #f1f5f9;
    flex-shrink: 0;
}

.zippy-product-compact-body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.zippy-product-compact-title {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.zippy-product-compact-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 5px;
}

.zippy-product-compact-price {
    font-size: 12.5px;
    font-weight: 800;
    color: #2563eb;
}

.zippy-product-compact-stock {
    font-size: 9.5px;
    font-weight: 700;
    padding: 1.5px 6px;
    border-radius: 10px;
}

.zippy-product-compact-stock.in-stock {
    background: #dcfce7;
    color: #15803d;
}

.zippy-product-compact-stock.out-stock {
    background: #fee2e2;
    color: #b91c1c;
}

.zippy-product-compact-actions {
    display: flex;
    align-items: center;
    gap: 5px;
}

.zippy-btn-view-details {
    flex: 1;
    padding: 4px 7px;
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    text-align: center;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: all 0.2s;
}

.zippy-btn-view-details:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}

.zippy-btn-quick-buy {
    flex: 1.25;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 700;
    color: #ffffff;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    transition: all 0.2s;
}

.zippy-btn-quick-buy:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
}

.zippy-btn-quick-buy:active {
    transform: scale(0.97);
}

.zippy-btn-quick-buy:disabled {
    background: #cbd5e1;
    color: #94a3b8;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}

.zippy-checkout-drawer {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #ffffff;
    z-index: 25;
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}

.zippy-checkout-drawer.active {
    transform: translateX(0);
}

.zippy-checkout-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 70%, #2563eb 100%);
    color: #ffffff;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}

.zippy-checkout-header-title {
    font-size: 14px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.zippy-checkout-header-sub {
    font-size: 10.5px;
    color: #94a3b8;
    margin: 0;
}

.zippy-checkout-body {
    flex: 1;
    overflow-y: auto;
    padding: 14px 16px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.zippy-co-product-strip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

.zippy-co-product-img {
    width: 44px;
    height: 44px;
    border-radius: 6px;
    object-fit: cover;
    background: #e2e8f0;
    flex-shrink: 0;
}

.zippy-co-product-info {
    flex: 1;
    min-width: 0;
}

.zippy-co-product-title {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
    margin-bottom: 2px;
}

.zippy-co-product-price {
    font-size: 12px;
    font-weight: 800;
    color: #2563eb;
}

.zippy-co-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.zippy-co-label {
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    margin: 0;
}

.zippy-co-input, .zippy-co-textarea {
    width: 100%;
    padding: 7px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 12.5px;
    color: #0f172a;
    outline: none;
    font-family: inherit;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: #ffffff;
}

.zippy-co-input:focus, .zippy-co-textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.zippy-phone-wrapper {
    display: flex;
    align-items: center;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    overflow: hidden;
    background: #ffffff;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.zippy-phone-wrapper:focus-within {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.zippy-phone-code {
    padding: 7px 9px;
    background: #f1f5f9;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    border-right: 1px solid #cbd5e1;
    user-select: none;
}

.zippy-phone-input {
    border: none;
    outline: none;
    flex: 1;
    padding: 7px 10px;
    font-size: 12.5px;
    color: #0f172a;
    font-family: inherit;
}

.zippy-variant-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.zippy-variant-pill {
    padding: 5px 10px;
    font-size: 11px;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    border-radius: 16px;
    background: #ffffff;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s ease;
}

.zippy-variant-pill:hover {
    border-color: #3b82f6;
    color: #1d4ed8;
}

.zippy-variant-pill.active {
    background: #eff6ff;
    border-color: #2563eb;
    color: #1d4ed8;
    font-weight: 700;
}

.zippy-co-stepper {
    display: inline-flex;
    align-items: center;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    overflow: hidden;
    width: fit-content;
    background: #ffffff;
}

.zippy-stepper-btn {
    width: 32px;
    height: 30px;
    background: #f8fafc;
    border: none;
    color: #334155;
    font-size: 11px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}

.zippy-stepper-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.zippy-stepper-val {
    width: 38px;
    text-align: center;
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    user-select: none;
}

.zippy-area-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.zippy-area-card {
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #ffffff;
    display: flex;
    flex-direction: column;
}

.zippy-area-card:hover {
    border-color: #94a3b8;
}

.zippy-area-card.active {
    border-color: #2563eb;
    background: #eff6ff;
}

.area-card-title {
    font-size: 11.5px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 2px;
}

.area-card-fee {
    font-size: 10px;
    font-weight: 600;
    color: #2563eb;
}

.zippy-co-pricing-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    font-size: 11.5px;
}

.zippy-pricing-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #64748b;
}

.zippy-pricing-row.total-row {
    padding-top: 6px;
    border-top: 1px dashed #cbd5e1;
    margin-top: 2px;
    font-size: 12.5px;
    font-weight: 800;
    color: #0f172a;
}

.zippy-pricing-row.total-row .total-amount {
    color: #2563eb;
    font-size: 14px;
}

.zippy-co-error-alert {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 600;
    display: none;
}

.zippy-co-footer {
    padding: 12px 16px;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
    flex-shrink: 0;
}

.zippy-co-confirm-btn {
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    transition: all 0.2s ease;
}

.zippy-co-confirm-btn:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
    transform: translateY(-1px);
}

.zippy-co-confirm-btn:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.zippy-order-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px;
    margin-top: 8px;
    font-size: 12px;
    box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05);
}

.zippy-order-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 12px;
}

.zippy-order-card-title {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: 0.2px;
}

.zippy-order-card-date {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 1px;
}

.zippy-status-pill {
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: capitalize;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-transit { background: #e0e7ff; color: #3730a3; }
.status-delivered { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

.zippy-order-timeline {
    position: relative;
    padding: 6px 4px 14px 4px;
    margin-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
}

.zippy-timeline-line-bg {
    position: absolute;
    top: 20px;
    left: 20px;
    right: 20px;
    height: 3px;
    background: #e2e8f0;
    z-index: 1;
}

.zippy-timeline-line-fill {
    position: absolute;
    top: 20px;
    left: 20px;
    height: 3px;
    background: linear-gradient(90deg, #2563eb, #22c55e);
    z-index: 2;
    transition: width 0.4s ease;
}

.zippy-timeline-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 3;
}

.zippy-timeline-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    width: 60px;
}

.zippy-step-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transition: all 0.3s ease;
}

.zippy-timeline-step.active .zippy-step-icon {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

.zippy-timeline-step.completed .zippy-step-icon {
    background: #22c55e;
    border-color: #22c55e;
    color: #ffffff;
}

.zippy-step-label {
    font-size: 9.5px;
    font-weight: 600;
    color: #94a3b8;
    margin-top: 5px;
    line-height: 1.1;
}

.zippy-timeline-step.active .zippy-step-label,
.zippy-timeline-step.completed .zippy-step-label {
    color: #0f172a;
    font-weight: 700;
}

.zippy-order-courier-box {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 10px;
    padding: 8px 12px;
    margin-bottom: 10px;
}

.zippy-order-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.zippy-order-meta-row:last-child {
    margin-bottom: 0;
}

.zippy-order-meta-label {
    color: #64748b;
    font-size: 11px;
    font-weight: 500;
}

.zippy-order-meta-val {
    color: #0f172a;
    font-weight: 700;
    font-size: 11.5px;
}

.zippy-tracking-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.zippy-tracking-link:hover {
    text-decoration: underline;
}

.zippy-order-items-box {
    margin-top: 8px;
}

.zippy-order-item-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 0;
    border-bottom: 1px dashed #f1f5f9;
}

.zippy-order-item-row:last-child {
    border-bottom: none;
}

.zippy-order-item-img {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    object-fit: cover;
    background: #f1f5f9;
    flex-shrink: 0;
}

.zippy-order-item-info {
    flex-grow: 1;
    overflow: hidden;
}

.zippy-order-item-name {
    font-size: 11.5px;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.zippy-order-item-qty {
    font-size: 10.5px;
    color: #64748b;
}

.zippy-order-item-price {
    font-size: 11.5px;
    font-weight: 700;
    color: #0f172a;
    flex-shrink: 0;
}

.zippy-order-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 8px;
    border-top: 1px solid #e2e8f0;
    margin-top: 6px;
    font-size: 12px;
}

.zippy-order-success-card {
    background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
    border: 1.5px solid #22c55e;
    border-radius: 16px;
    padding: 14px;
    margin-top: 8px;
    font-size: 12px;
    box-shadow: 0 4px 15px rgba(34, 197, 94, 0.12);
}

.zippy-typing-indicator {
    align-self: flex-start;
    display: none;
    align-items: center;
    gap: 5px;
    padding: 10px 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.zippy-typing-dot {
    width: 6px;
    height: 6px;
    background-color: #94a3b8;
    border-radius: 50%;
    animation: zippyBounce 1.4s infinite ease-in-out both;
}

.zippy-typing-dot:nth-child(1) { animation-delay: -0.32s; }
.zippy-typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes zippyBounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

.zippy-chat-footer {
    padding: 10px 14px;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
    flex-shrink: 0;
}

.zippy-chat-input-box {
    display: flex;
    align-items: flex-end;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 20px;
    padding: 4px 6px 4px 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
    min-height: 44px;
    box-sizing: border-box;
}

.zippy-chat-input-box:focus-within {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    background: #ffffff;
}

.zippy-chat-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 13px;
    line-height: 1.45;
    color: #0f172a;
    outline: none;
    padding: 8px 4px 8px 0;
    resize: none;
    overflow-y: auto;
    max-height: 120px;
    height: auto;
    font-family: inherit;
    box-sizing: border-box;
}

.zippy-chat-input::placeholder {
    color: #94a3b8;
}

.zippy-chat-input::-webkit-scrollbar {
    width: 4px;
}

.zippy-chat-input::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 4px;
}

.zippy-chat-input::-webkit-scrollbar-track {
    background: transparent;
}

.zippy-chat-send-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #2563eb;
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s;
    flex-shrink: 0;
    margin-bottom: 2px;
}

.zippy-chat-send-btn:hover {
    background: #1d4ed8;
    transform: scale(1.05);
}

.zippy-chat-send-btn:active {
    transform: scale(0.95);
}

.zippy-chat-send-btn:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
    transform: none;
}

.zippy-chat-subnote {
    text-align: center;
    font-size: 10px;
    color: #94a3b8;
    margin-top: 6px;
    margin-bottom: 0;
}
</style>

<div class="zippy-chatbot-container" id="zippyChatbotContainer">
    <div class="zippy-chat-backdrop" id="zippyChatBackdrop"></div>
    <div class="zippy-chat-window" id="zippyChatWindow">
        <div class="zippy-chat-header">
            <div class="zippy-chat-drag-handle"></div>
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center">
                    <div class="zippy-chat-avatar">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div class="zippy-chat-header-info">
                        <h6 class="zippy-chat-title">Zippy AI Assistant</h6>
                        <p class="zippy-chat-status">
                            <span class="status-dot"></span> Online • Official Support
                        </p>
                    </div>
                </div>
                <div class="zippy-chat-header-actions">
                    <div class="zippy-lang-toggle" id="zippyLangToggle" role="group" aria-label="Language selector">
                        <button type="button" class="zippy-lang-btn active" data-lang="bn">বাংলা</button>
                        <button type="button" class="zippy-lang-btn" data-lang="en">EN</button>
                    </div>
                    <button type="button" class="zippy-chat-btn-icon" id="zippyChatClearBtn" title="Reset Chat">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                    <button type="button" class="zippy-chat-btn-icon" id="zippyChatCloseBtn" title="Minimize">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="zippy-chat-body" id="zippyChatBody">
            <div class="zippy-typing-indicator" id="zippyTypingIndicator">
                <span class="zippy-typing-dot"></span>
                <span class="zippy-typing-dot"></span>
                <span class="zippy-typing-dot"></span>
                <span style="font-size: 11px; color: #64748b; margin-left: 4px;">Assisting...</span>
            </div>

            <div class="zippy-quick-chips" id="zippyQuickChips" style="display: none;"></div>
        </div>

        <div class="zippy-chat-footer">
            <div class="zippy-chat-input-box">
                <textarea id="zippyChatInput" class="zippy-chat-input" rows="1" placeholder="প্রোডাক্ট খুঁজুন, ডেলিভারি বা অর্ডার ট্র্যাক করুন..." aria-label="Type your message"></textarea>
                <button type="button" id="zippyChatSendBtn" class="zippy-chat-send-btn" aria-label="Send message">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
            <p class="zippy-chat-subnote">Instant Store Support • 24/7 Assistance</p>
        </div>

        <div class="zippy-checkout-drawer" id="zippyCheckoutDrawer">
            <div class="zippy-checkout-header">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="zippy-chat-btn-icon" id="zippyCheckoutBackBtn" title="Back to Chat">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <div>
                        <h6 class="zippy-checkout-header-title"><i class="fa-solid fa-bolt text-warning"></i> Express Checkout</h6>
                        <p class="zippy-checkout-header-sub">Cash on Delivery • Direct Order</p>
                    </div>
                </div>
                <button type="button" class="zippy-chat-btn-icon" id="zippyCheckoutCloseBtn" title="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="zippy-checkout-body">
                <div class="zippy-co-product-strip">
                    <img id="coProductImg" src="{{ asset('images/product-placeholder.svg') }}" class="zippy-co-product-img" alt="Selected Product" width="60" height="60" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                    <div class="zippy-co-product-info">
                        <div id="coProductTitle" class="zippy-co-product-title"></div>
                        <div class="zippy-co-product-price">৳<span id="coProductPrice">0</span></div>
                    </div>
                </div>

                <div class="zippy-co-field" id="coVariantField" style="display: none;">
                    <label class="zippy-co-label">Select Option / Variant:</label>
                    <div class="zippy-variant-pills" id="coVariantPills"></div>
                </div>

                <div class="zippy-co-field">
                    <label class="zippy-co-label">Quantity:</label>
                    <div class="zippy-co-stepper">
                        <button type="button" class="zippy-stepper-btn" id="coQtyDec" aria-label="পরিমাণ কমান"><i class="fa-solid fa-minus"></i></button>
                        <span class="zippy-stepper-val" id="coQtyDisplay">1</span>
                        <button type="button" class="zippy-stepper-btn" id="coQtyInc" aria-label="পরিমাণ বাড়ান"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>

                <div class="zippy-co-field">
                    <label class="zippy-co-label" for="coCustName">Customer Full Name <span class="text-danger">*</span></label>
                    <input type="text" id="coCustName" class="zippy-co-input" placeholder="e.g. Tanvir Ahmed" required>
                </div>

                <div class="zippy-co-field">
                    <label class="zippy-co-label" for="coCustPhone">Mobile Number (11 Digits) <span class="text-danger">*</span></label>
                    <div class="zippy-phone-wrapper">
                        <span class="zippy-phone-code">+88</span>
                        <input type="tel" id="coCustPhone" class="zippy-phone-input" placeholder="017XXXXXXXX" maxlength="11" required>
                    </div>
                </div>

                <div class="zippy-co-field">
                    <label class="zippy-co-label">Delivery Destination <span class="text-danger">*</span></label>
                    <div class="zippy-area-grid">
                        <div class="zippy-area-card active" id="coAreaDhakaCard">
                            <div class="area-card-title"><i class="fa-solid fa-city me-1"></i> Inside Dhaka</div>
                            <div class="area-card-fee">৳60 (24-48 Hours)</div>
                        </div>
                        <div class="zippy-area-card" id="coAreaOutsideCard">
                            <div class="area-card-title"><i class="fa-solid fa-truck-fast me-1"></i> Outside Dhaka</div>
                            <div class="area-card-fee">৳120 (2-4 Days)</div>
                        </div>
                    </div>
                </div>

                <div class="zippy-co-field">
                    <label class="zippy-co-label" for="coCustAddress">Full Delivery Address <span class="text-danger">*</span></label>
                    <textarea id="coCustAddress" class="zippy-co-textarea" rows="2" placeholder="House/Flat #, Road, Area, City" required></textarea>
                </div>

                <div class="zippy-co-pricing-box">
                    <div class="zippy-pricing-row">
                        <span>Item Subtotal (<span id="coQtyCount">1</span>x):</span>
                        <span>৳<span id="coSubtotalVal">0</span></span>
                    </div>
                    <div class="zippy-pricing-row">
                        <span>Delivery Fee:</span>
                        <span>৳<span id="coShippingVal">60</span></span>
                    </div>
                    <div class="zippy-pricing-row total-row">
                        <span>Total Payable (COD):</span>
                        <span class="total-amount">৳<span id="coTotalVal">0</span></span>
                    </div>
                </div>

                <div class="zippy-co-error-alert" id="coErrorAlert"></div>
            </div>

            <div class="zippy-co-footer">
                <button type="button" class="zippy-co-confirm-btn" id="coConfirmBtn">
                    <span id="coBtnReady"><i class="fa-solid fa-circle-check me-1"></i> Confirm Order (Cash on Delivery)</span>
                    <span id="coBtnSpinner" style="display: none;"><i class="fa-solid fa-spinner fa-spin me-1"></i> Processing Order...</span>
                </button>
            </div>
        </div>
    </div>

    <div class="zippy-chat-tooltip" id="zippyChatTooltip" role="tooltip" aria-hidden="true" style="display: none;">
        <button type="button" class="zippy-tooltip-close" id="zippyTooltipCloseBtn" aria-label="Close message">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="zippy-tooltip-content" id="zippyTooltipContent">
            <div class="zippy-tooltip-avatar">
                <i class="fa-solid fa-headset fa-bounce"></i>
                <span class="zippy-avatar-online"></span>
            </div>
            <div class="zippy-tooltip-text">
                <strong class="zippy-tooltip-title">👋 কী খুঁজছেন?</strong>
                <p class="zippy-tooltip-desc">যেকোনো গ্যাজেট খুঁজে পেতে বা সরাসরি অর্ডার করতে আমাকে জানান!</p>
            </div>
        </div>
    </div>

    <button type="button" class="zippy-chat-trigger" id="zippyChatTrigger" aria-label="Customer Support Chat">
        <i class="fa-duotone fa-solid fa-headset fa-float trigger-icon"></i>
        <i class="fa-solid fa-xmark close-icon"></i>
        <span class="zippy-chat-pulse"></span>
    </button>
</div>

<script data-turbo-eval="false">
(function() {
    if (window.__zippyChatbotInit) return;
    const container = document.getElementById('zippyChatbotContainer');
    const trigger = document.getElementById('zippyChatTrigger');
    const closeBtn = document.getElementById('zippyChatCloseBtn');
    const backdrop = document.getElementById('zippyChatBackdrop');
    if (!container || !trigger || !closeBtn) return;
    window.__zippyChatbotInit = true;
    const clearBtn = document.getElementById('zippyChatClearBtn');
    const chatBody = document.getElementById('zippyChatBody');
    const chatInput = document.getElementById('zippyChatInput');
    const sendBtn = document.getElementById('zippyChatSendBtn');
    const typingIndicator = document.getElementById('zippyTypingIndicator');

    const drawer = document.getElementById('zippyCheckoutDrawer');
    const drawerBackBtn = document.getElementById('zippyCheckoutBackBtn');
    const drawerCloseBtn = document.getElementById('zippyCheckoutCloseBtn');
    const coProductImg = document.getElementById('coProductImg');
    const coProductTitle = document.getElementById('coProductTitle');
    const coProductPrice = document.getElementById('coProductPrice');
    const coVariantField = document.getElementById('coVariantField');
    const coVariantPills = document.getElementById('coVariantPills');
    const coQtyDisplay = document.getElementById('coQtyDisplay');
    const coQtyDec = document.getElementById('coQtyDec');
    const coQtyInc = document.getElementById('coQtyInc');
    const coCustName = document.getElementById('coCustName');
    const coCustPhone = document.getElementById('coCustPhone');
    const coCustAddress = document.getElementById('coCustAddress');
    const coAreaDhakaCard = document.getElementById('coAreaDhakaCard');
    const coAreaOutsideCard = document.getElementById('coAreaOutsideCard');
    const coQtyCount = document.getElementById('coQtyCount');
    const coSubtotalVal = document.getElementById('coSubtotalVal');
    const coShippingVal = document.getElementById('coShippingVal');
    const coTotalVal = document.getElementById('coTotalVal');
    const coErrorAlert = document.getElementById('coErrorAlert');
    const coConfirmBtn = document.getElementById('coConfirmBtn');
    const coBtnReady = document.getElementById('coBtnReady');
    const coBtnSpinner = document.getElementById('coBtnSpinner');

    const messageRoute = "{{ route('api.chatbot.message') }}";
    const historyRoute = "{{ route('api.chatbot.history') }}";
    const clearRoute = "{{ route('api.chatbot.clear') }}";
    const quickOrderRoute = "{{ route('api.chatbot.quick_order') }}";

    let currentOrderState = {
        productId: null,
        productTitle: '',
        unitPrice: 0,
        quantity: 1,
        maxStock: 50,
        variant: null,
        district: 'ঢাকা',
        shippingCost: 60,
    };

    let currentChatLang = localStorage.getItem('zippy_chat_lang') || 'bn';

    function updateLangUi() {
        const toggle = document.getElementById('zippyLangToggle');
        if (toggle) {
            toggle.querySelectorAll('.zippy-lang-btn').forEach(btn => {
                if (btn.getAttribute('data-lang') === currentChatLang) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }
        const inp = document.getElementById('zippyChatInput') || chatInput;
        if (inp) {
            inp.placeholder = currentChatLang === 'en'
                ? "Search products, delivery info, or track orders..."
                : "প্রোডাক্ট খুঁজুন, ডেলিভারি বা অর্ডার ট্র্যাক করুন...";
        }
    }
    updateLangUi();

    function hideTooltip() {
        const tip = document.getElementById('zippyChatTooltip');
        if (tip) {
            tip.style.display = 'none';
            tip.setAttribute('aria-hidden', 'true');
        }
    }

    function showTooltipIfEligible() {
        const c = document.getElementById('zippyChatbotContainer') || container;
        if (c && c.classList.contains('active')) return;
        if (sessionStorage.getItem('zippy_tooltip_dismissed') === '1') return;
        const tip = document.getElementById('zippyChatTooltip');
        if (!tip) return;
        tip.style.display = 'block';
        tip.setAttribute('aria-hidden', 'false');
    }

    function toggleChat() {
        hideTooltip();
        const c = document.getElementById('zippyChatbotContainer') || container;
        if (!c) return;
        const isActive = c.classList.toggle('active');
        if (isActive) {
            if (!window.__chatbotHistoryLoaded) {
                window.__chatbotHistoryLoaded = true;
                loadHistory();
            }
            if (window.innerWidth <= 768) {
                if (typeof window.lockPageScroll === 'function') {
                    window.lockPageScroll();
                } else {
                    document.body.style.overflow = 'hidden';
                }
            }
            scrollToBottom();
            setTimeout(() => {
                const inp = document.getElementById('zippyChatInput') || chatInput;
                if (inp && window.innerWidth > 768) {
                    inp.focus();
                }
            }, 250);
        } else {
            if (typeof window.unlockPageScroll === 'function') {
                window.unlockPageScroll();
            } else {
                document.body.style.overflow = '';
            }
            const windowEl = document.getElementById('zippyChatWindow');
            if (windowEl) windowEl.style.transform = '';
            const drw = document.getElementById('zippyCheckoutDrawer') || drawer;
            if (drw && drw.classList.contains('active')) {
                drw.classList.remove('active');
            }
        }
    }
    window.zippyToggleChat = toggleChat;
    window.zippyShowTooltipIfEligible = showTooltipIfEligible;
    window.zippyHideTooltip = hideTooltip;
    window.openChatbot = function() {
        const c = document.getElementById('zippyChatbotContainer');
        if (c && !c.classList.contains('active')) {
            toggleChat();
        }
    };
    window.closeChatbot = function() {
        const c = document.getElementById('zippyChatbotContainer');
        if (c && c.classList.contains('active')) {
            toggleChat();
        }
    };

    setTimeout(showTooltipIfEligible, 1200);

    if (!window.__zippyTooltipTurboBound) {
        window.__zippyTooltipTurboBound = true;
        document.addEventListener('turbo:load', () => {
            setTimeout(showTooltipIfEligible, 1200);
        });
    }

    if (!window.__zippyChatClickDelegated) {
        window.__zippyChatClickDelegated = true;
        document.addEventListener('click', function(e) {
            const langBtn = e.target.closest('.zippy-lang-btn');
            if (langBtn) {
                e.preventDefault();
                const selectedLang = langBtn.getAttribute('data-lang');
                if (selectedLang && selectedLang !== currentChatLang) {
                    currentChatLang = selectedLang;
                    localStorage.setItem('zippy_chat_lang', currentChatLang);
                    updateLangUi();
                    const body = document.getElementById('zippyChatBody') || chatBody;
                    const msgs = body ? body.querySelectorAll('.zippy-chat-message') : [];
                    if (msgs.length <= 1) {
                        if (body) {
                            msgs.forEach(m => m.remove());
                        }
                        const chips = document.getElementById('zippyQuickChips');
                        if (chips) chips.style.display = 'none';
                        loadHistory();
                    }
                }
                return;
            }

            /* Trigger, close, backdrop, and tooltip clicks are already
               handled by the global delegation in frontend.src.js.
               Do NOT add handlers for them here or toggleChat() fires twice. */
        });

        let touchStartY = 0;
        let touchDiffY = 0;

        document.addEventListener('touchstart', (e) => {
            if (window.innerWidth > 768) return;
            if (e.target.closest('.zippy-chat-header')) {
                touchStartY = e.touches[0].clientY;
                touchDiffY = 0;
            }
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (window.innerWidth > 768 || touchStartY === 0) return;
            if (!e.target.closest('.zippy-chat-header')) return;
            touchDiffY = e.touches[0].clientY - touchStartY;
            if (touchDiffY > 0) {
                const windowEl = document.getElementById('zippyChatWindow');
                if (windowEl) {
                    windowEl.style.transform = 'translateY(' + touchDiffY + 'px)';
                    windowEl.style.transition = 'none';
                }
            }
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            if (window.innerWidth > 768 || touchStartY === 0) return;
            if (!e.target.closest('.zippy-chat-header')) return;
            const windowEl = document.getElementById('zippyChatWindow');
            if (windowEl) {
                windowEl.style.transition = '';
                if (touchDiffY > 80) {
                    windowEl.style.transform = '';
                    toggleChat();
                } else {
                    windowEl.style.transform = '';
                }
            }
            touchStartY = 0;
            touchDiffY = 0;
        }, { passive: true });
    }

    document.addEventListener('turbo:before-cache', () => {
        const c = document.getElementById('zippyChatbotContainer') || container;
        if (c && c.classList.contains('active')) {
            c.classList.remove('active');
        }
        if (typeof window.unlockPageScroll === 'function') {
            window.unlockPageScroll(true);
        } else {
            document.body.style.overflow = '';
        }
    });

    function scrollToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function parseSimpleMarkdown(text) {
        if (!text) return '';
        let escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
        escaped = escaped.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
        escaped = escaped.replace(/\n\n/g, '<br><br>');
        escaped = escaped.replace(/\n/g, '<br>');
        return escaped;
    }

    function renderOrderTrackingCard(order) {
        if (!order || !order.order_number) return '';

        const step = order.timeline_step || 1;
        const fillPercent = step === 4 ? 100 : (step === 3 ? 75 : (step === 2 ? 45 : 15));

        let statusClass = 'status-pending';
        if (step === 2) statusClass = 'status-processing';
        else if (step === 3) statusClass = 'status-transit';
        else if (step === 4) statusClass = 'status-delivered';
        if (order.order_status === 'cancelled') statusClass = 'status-cancelled';

        let itemsHtml = '';
        if (order.items && order.items.length > 0) {
            itemsHtml = '<div class="zippy-order-items-box">';
            order.items.forEach(it => {
                const title = it.title || it;
                const qty = it.qty ? `x${it.qty}` : '';
                const price = it.price ? `৳${Number(it.price).toLocaleString()}` : '';
                const img = it.image || '/favicon.ico';
                itemsHtml += `
                    <div class="zippy-order-item-row">
                        <img src="${img}" class="zippy-order-item-img" alt="" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                        <div class="zippy-order-item-info">
                            <div class="zippy-order-item-name">${title}</div>
                            ${qty ? `<div class="zippy-order-item-qty">Quantity: ${qty}</div>` : ''}
                        </div>
                        ${price ? `<div class="zippy-order-item-price">${price}</div>` : ''}
                    </div>
                `;
            });
            itemsHtml += '</div>';
        }

        return `
            <div class="zippy-order-card">
                <div class="zippy-order-card-header">
                    <div>
                        <div class="zippy-order-card-title">Order #${order.order_number}</div>
                        <div class="zippy-order-card-date">${order.order_date || 'Recent Order'}</div>
                    </div>
                    <span class="zippy-status-pill ${statusClass}">${order.order_status_label || order.order_status}</span>
                </div>

                <div class="zippy-order-timeline">
                    <div class="zippy-timeline-line-bg"></div>
                    <div class="zippy-timeline-line-fill" style="width: ${fillPercent}%;"></div>
                    <div class="zippy-timeline-steps">
                        <div class="zippy-timeline-step ${step >= 1 ? (step === 1 ? 'active' : 'completed') : ''}">
                            <div class="zippy-step-icon"><i class="fa-solid fa-receipt"></i></div>
                            <span class="zippy-step-label">Placed</span>
                        </div>
                        <div class="zippy-timeline-step ${step >= 2 ? (step === 2 ? 'active' : 'completed') : ''}">
                            <div class="zippy-step-icon"><i class="fa-solid fa-box-open"></i></div>
                            <span class="zippy-step-label">Packing</span>
                        </div>
                        <div class="zippy-timeline-step ${step >= 3 ? (step === 3 ? 'active' : 'completed') : ''}">
                            <div class="zippy-step-icon"><i class="fa-solid fa-truck-fast"></i></div>
                            <span class="zippy-step-label">In Transit</span>
                        </div>
                        <div class="zippy-timeline-step ${step >= 4 ? 'completed' : ''}">
                            <div class="zippy-step-icon"><i class="fa-solid fa-house-circle-check"></i></div>
                            <span class="zippy-step-label">Delivered</span>
                        </div>
                    </div>
                </div>

                <div class="zippy-order-courier-box">
                    ${order.customer_phone_masked ? `
                    <div class="zippy-order-meta-row">
                        <span class="zippy-order-meta-label"><i class="fa-solid fa-phone me-1"></i> Customer:</span>
                        <span class="zippy-order-meta-val">${order.customer_phone_masked}</span>
                    </div>` : ''}
                    <div class="zippy-order-meta-row">
                        <span class="zippy-order-meta-label"><i class="fa-solid fa-truck-ramp-box me-1"></i> Courier:</span>
                        <span class="zippy-order-meta-val">${order.courier_provider || 'Standard Express'}</span>
                    </div>
                    <div class="zippy-order-meta-row">
                        <span class="zippy-order-meta-label"><i class="fa-solid fa-barcode me-1"></i> Tracking:</span>
                        <span class="zippy-order-meta-val">
                            ${order.courier_tracking_url ? `
                                <a href="${order.courier_tracking_url}" target="_blank" class="zippy-tracking-link">
                                    ${order.courier_tracking_code || 'Track'} <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px;"></i>
                                </a>
                            ` : (order.courier_tracking_code || 'Pending')}
                        </span>
                    </div>
                    <div class="zippy-order-meta-row">
                        <span class="zippy-order-meta-label"><i class="fa-solid fa-credit-card me-1"></i> Payment:</span>
                        <span class="zippy-order-meta-val">${order.payment_method || 'COD'} (${order.payment_status || 'Pending'})</span>
                    </div>
                </div>

                ${itemsHtml}

                ${order.total ? `
                <div class="zippy-order-total-row">
                    <span class="fw-bold text-secondary">Total Amount:</span>
                    <span class="fw-bold text-primary" style="font-size: 13px;">৳${Number(order.total).toLocaleString()}</span>
                </div>` : ''}
            </div>
        `;
    }

    function appendUserMessage(text, timeStr) {
        const time = timeStr || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const msgDiv = document.createElement('div');
        msgDiv.className = 'zippy-chat-message user';
        msgDiv.innerHTML = `
            <div class="zippy-msg-bubble">${parseSimpleMarkdown(text)}</div>
            <span class="zippy-msg-time">${time}</span>
        `;
        const body = document.getElementById('zippyChatBody') || chatBody;
        const ind = document.getElementById('zippyTypingIndicator') || typingIndicator;
        if (body) {
            if (ind) {
                body.insertBefore(msgDiv, ind);
            } else {
                body.appendChild(msgDiv);
            }
        }
        scrollToBottom();
    }

    function appendBotMessage(text, timeStr, products, orderStatus, ordersList, createdOrder, orderingState) {
        const time = timeStr || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const msgDiv = document.createElement('div');
        msgDiv.className = 'zippy-chat-message bot';

        let innerHtml = `<div class="zippy-msg-bubble">${parseSimpleMarkdown(text)}</div>`;

        if (createdOrder && createdOrder.order_number) {
            innerHtml += `
                <div class="zippy-order-success-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-success"><i class="fa-solid fa-circle-check"></i> Order Placed Successfully!</span>
                        <span class="badge bg-success">${createdOrder.order_number}</span>
                    </div>
                    <div class="zippy-order-meta-row">
                        <span class="text-secondary">Product:</span>
                        <strong>${createdOrder.product_title || ''} ${createdOrder.quantity ? `(x${createdOrder.quantity})` : ''}</strong>
                    </div>
                    <div class="zippy-order-meta-row">
                        <span class="text-secondary">Payable (COD):</span>
                        <strong class="text-primary" style="font-size: 13px;">৳${Number(createdOrder.total).toLocaleString()}</strong>
                    </div>
                    <div class="zippy-order-meta-row">
                        <span class="text-secondary">Delivery Address:</span>
                        <span>${createdOrder.delivery_address || ''}</span>
                    </div>
                    <div class="zippy-order-meta-row">
                        <span class="text-secondary">Estimated Delivery:</span>
                        <span class="fw-bold text-dark"><i class="fa-solid fa-clock me-1 text-primary"></i> ${createdOrder.estimated_delivery || '24-48 Hours'}</span>
                    </div>
                    <div class="mt-2 pt-2 border-top border-success border-opacity-25 text-success" style="font-size: 11px;">
                        <i class="fa-solid fa-phone me-1"></i> Our team will call you shortly for dispatch confirmation.
                    </div>
                </div>
            `;
        }

        if (orderStatus && orderStatus.order_number) {
            innerHtml += renderOrderTrackingCard(orderStatus);
        }

        if (ordersList && ordersList.length > 0) {
            ordersList.forEach(ord => {
                innerHtml += renderOrderTrackingCard(ord);
            });
        }

        if (orderingState && orderingState.step === 'confirming' && (!createdOrder || !createdOrder.order_number)) {
            innerHtml += `
                <div class="zippy-quick-chips mt-2">
                    <button type="button" class="zippy-chip confirm-chip" onclick="zippySendQuickPrompt('${currentChatLang === 'en' ? 'Confirm Order' : 'কনফার্ম'}')">
                        <i class="fa-solid fa-check"></i> ${currentChatLang === 'en' ? 'Confirm Order' : 'কনফার্ম করুন'}
                    </button>
                    <button type="button" class="zippy-chip cancel-chip" onclick="zippySendQuickPrompt('${currentChatLang === 'en' ? 'Cancel' : 'বাতিল'}')">
                        <i class="fa-solid fa-xmark"></i> ${currentChatLang === 'en' ? 'Cancel' : 'বাতিল'}
                    </button>
                </div>
            `;
        }

        if (products && products.length > 0) {
            products.forEach(p => {
                const img = p.main_image || '/favicon.ico';
                const safePayload = encodeURIComponent(JSON.stringify(p));
                innerHtml += `
                    <div class="zippy-product-card-compact">
                        <img src="${img}" class="zippy-product-compact-img" alt="${p.title}" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';">
                        <div class="zippy-product-compact-body">
                            <div class="zippy-product-compact-title" title="${p.title}">${p.title}</div>
                            <div class="zippy-product-compact-meta">
                                <span class="zippy-product-compact-price">৳${Number(p.price).toLocaleString()}</span>
                                <span class="zippy-product-compact-stock ${p.in_stock ? 'in-stock' : 'out-stock'}">
                                    <i class="fa-solid ${p.in_stock ? 'fa-check' : 'fa-xmark'}"></i> ${p.in_stock ? 'In Stock' : 'Out of Stock'}
                                </span>
                            </div>
                            <div class="zippy-product-compact-actions">
                                <a href="${p.url}" class="zippy-btn-view-details" target="_blank">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Details
                                </a>
                                ${p.in_stock ? `
                                    <button type="button" class="zippy-btn-quick-buy" data-product="${safePayload}" onclick="zippyOpenQuickCheckout(JSON.parse(decodeURIComponent(this.getAttribute('data-product'))))">
                                        <i class="fa-solid fa-bolt"></i> Quick Buy
                                    </button>
                                ` : `
                                    <button type="button" class="zippy-btn-quick-buy" disabled>
                                        <i class="fa-solid fa-ban"></i> Unavailable
                                    </button>
                                `}
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        innerHtml += `<span class="zippy-msg-time">${time}</span>`;
        msgDiv.innerHTML = innerHtml;
        const body = document.getElementById('zippyChatBody') || chatBody;
        const ind = document.getElementById('zippyTypingIndicator') || typingIndicator;
        if (body) {
            if (ind) {
                body.insertBefore(msgDiv, ind);
            } else {
                body.appendChild(msgDiv);
            }
        }
        scrollToBottom();
    }

    function showTyping() {
        const ind = document.getElementById('zippyTypingIndicator') || typingIndicator;
        const btn = document.getElementById('zippyChatSendBtn') || sendBtn;
        if (ind) ind.style.display = 'flex';
        if (btn) btn.disabled = true;
        scrollToBottom();
    }

    function hideTyping() {
        const ind = document.getElementById('zippyTypingIndicator') || typingIndicator;
        const btn = document.getElementById('zippyChatSendBtn') || sendBtn;
        if (ind) ind.style.display = 'none';
        if (btn) btn.disabled = false;
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
            || (('ontouchstart' in window || navigator.maxTouchPoints > 0) && window.innerWidth <= 1024)
            || window.innerWidth <= 768;
    }

    function autoResizeInput() {
        const inp = document.getElementById('zippyChatInput') || chatInput;
        if (!inp) return;
        inp.style.height = 'auto';
        const scrollH = inp.scrollHeight;
        const newH = Math.min(scrollH, 120);
        inp.style.height = (newH > 0 ? newH : 34) + 'px';
    }

    function resetInputHeight() {
        const inp = document.getElementById('zippyChatInput') || chatInput;
        if (inp) {
            inp.style.height = '';
        }
    }

    chatInput.addEventListener('input', autoResizeInput);

    function collectUserActivity() {
        const url = window.location.href;
        const pathname = window.location.pathname;
        let pageType = 'home';
        let currentProduct = null;

        if (pathname.includes('/product/')) {
            pageType = 'product';
            const titleEl = document.querySelector('h1') || document.querySelector('.product-title');
            const priceEl = document.querySelector('.text-danger.font-heading') || document.querySelector('.product-price');
            const metaImg = document.querySelector('meta[property="og:image"]');
            const mainImg = document.querySelector('.main-slider-img') || document.querySelector('.productMainSwiper img');

            let slug = '';
            const match = pathname.match(/\/product\/([^\/?#]+)/);
            if (match && match[1]) {
                slug = decodeURIComponent(match[1]);
            }

            currentProduct = {
                title: titleEl ? titleEl.innerText.trim() : (document.title.split('-')[0].trim() || ''),
                slug: slug,
                price: priceEl ? priceEl.innerText.replace(/[^0-9.]/g, '') : null,
                image: metaImg ? metaImg.getAttribute('content') : (mainImg ? mainImg.src : null)
            };

            if (slug) {
                try {
                    let recents = JSON.parse(localStorage.getItem('zippy_recent_views') || '[]');
                    recents = recents.filter(s => s !== slug);
                    recents.unshift(slug);
                    if (recents.length > 5) recents = recents.slice(0, 5);
                    localStorage.setItem('zippy_recent_views', JSON.stringify(recents));
                } catch (e) {}
            }
        } else if (pathname.includes('/checkout') || pathname.includes('/cart')) {
            pageType = 'checkout';
        } else if (pathname.includes('/order/track') || pathname.includes('/order/success') || pathname.includes('/order/history')) {
            pageType = 'tracking';
        } else if (pathname.includes('/products') || pathname.includes('/category/') || pathname.includes('/new-collection') || pathname.includes('/best-sale') || pathname.includes('/flash-deals') || pathname.includes('/search')) {
            pageType = 'catalog';
        }

        let recentlyViewed = [];
        try {
            recentlyViewed = JSON.parse(localStorage.getItem('zippy_recent_views') || '[]');
        } catch (e) {}

        const cartBadge = document.querySelector('.cart-count, #cartCount, [data-cart-count], .cart-badge');
        const cartCount = cartBadge ? parseInt(cartBadge.innerText.replace(/[^0-9]/g, ''), 10) || 0 : 0;

        return {
            current_url: url,
            page_type: pageType,
            page_title: document.title,
            current_product: currentProduct,
            recently_viewed: recentlyViewed,
            cart_count: cartCount
        };
    }

    function renderChips(chips) {
        let chipsContainer = document.getElementById('zippyQuickChips');
        if (!chipsContainer) {
            chipsContainer = document.createElement('div');
            chipsContainer.className = 'zippy-quick-chips';
            chipsContainer.id = 'zippyQuickChips';
        }
        chatBody.insertBefore(chipsContainer, typingIndicator);

        chipsContainer.innerHTML = '';
        if (!chips || !Array.isArray(chips) || chips.length === 0) {
            chipsContainer.style.display = 'none';
            return;
        }

        chipsContainer.style.display = 'flex';
        chips.forEach(chip => {
            const label = (typeof chip === 'object' && chip.label) ? chip.label : (typeof chip === 'string' ? chip : '');
            const prompt = (typeof chip === 'object' && chip.prompt) ? chip.prompt : label;
            if (!label) return;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'zippy-chip';
            btn.innerHTML = label;
            btn.addEventListener('click', () => {
                chipsContainer.style.display = 'none';
                window.zippySendQuickPrompt(prompt);
            });
            chipsContainer.appendChild(btn);
        });
        scrollToBottom();
    }

    function sendMessage(text) {
        const inp = document.getElementById('zippyChatInput') || chatInput;
        const query = (text || (inp ? inp.value : '') || '').trim();
        if (!query) return;

        if (inp) {
            inp.value = '';
            resetInputHeight();
            if (window.innerWidth > 768) {
                inp.focus();
            }
        }
        appendUserMessage(query);
        showTyping();

        const chips = document.getElementById('zippyQuickChips');
        if (chips) {
            chips.style.display = 'none';
        }

        axios.post(messageRoute, {
            message: query,
            activity: collectUserActivity(),
            language: currentChatLang
        }, {
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        }).then(res => {
            hideTyping();
            if (res.data && res.data.success) {
                const activeOrderCard = res.data.order_card !== undefined ? res.data.order_card : (res.data.order_status || null);
                appendBotMessage(
                    res.data.reply,
                    null,
                    res.data.products,
                    activeOrderCard,
                    res.data.orders_list,
                    res.data.created_order,
                    res.data.ordering_state
                );
                if (res.data.chips && Array.isArray(res.data.chips) && res.data.chips.length > 0) {
                    renderChips(res.data.chips);
                }
            } else {
                appendBotMessage(res.data.message || 'Sorry, something went wrong. Please try again.');
            }
        }).catch(err => {
            hideTyping();
            let errText = 'Unable to connect to assistant. Please check your internet connection.';
            if (err.response && err.response.data && err.response.data.message) {
                errText = err.response.data.message;
            }
            appendBotMessage(errText);
        });
    }

    window.zippySendQuickPrompt = function(promptText) {
        const c = document.getElementById('zippyChatbotContainer') || container;
        if (c && !c.classList.contains('active')) {
            c.classList.add('active');
        }
        sendMessage(promptText);
    };

    sendBtn.addEventListener('click', () => sendMessage());

    chatInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.keyCode === 13) {
            if (isMobileDevice()) {
                setTimeout(autoResizeInput, 0);
                return;
            }
            if (e.shiftKey) {
                setTimeout(autoResizeInput, 0);
            } else {
                e.preventDefault();
                sendMessage();
            }
        }
    });

    clearBtn.addEventListener('click', () => {
        if (confirm('Clear chat conversation?')) {
            axios.post(clearRoute, {}, {
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            }).then(() => {
                const body = document.getElementById('zippyChatBody') || chatBody;
                if (body) {
                    body.querySelectorAll('.zippy-chat-message').forEach(el => el.remove());
                }
                const chips = document.getElementById('zippyQuickChips');
                if (chips) chips.style.display = 'none';
                loadHistory();
            }).catch(() => {
                location.reload();
            });
        }
    });

    function updateQuickCheckoutTotals() {
        const subtotal = currentOrderState.unitPrice * currentOrderState.quantity;
        const total = subtotal + currentOrderState.shippingCost;

        coQtyDisplay.textContent = currentOrderState.quantity;
        coQtyCount.textContent = currentOrderState.quantity;
        coProductPrice.textContent = Number(currentOrderState.unitPrice).toLocaleString();
        coSubtotalVal.textContent = Number(subtotal).toLocaleString();
        coShippingVal.textContent = Number(currentOrderState.shippingCost).toLocaleString();
        coTotalVal.textContent = Number(total).toLocaleString();
    }

    window.zippyOpenQuickCheckout = function(product) {
        if (!product || !product.id) return;

        currentOrderState.productId = product.id;
        currentOrderState.productTitle = product.title;
        currentOrderState.unitPrice = Number(product.price) || 0;
        currentOrderState.quantity = 1;
        currentOrderState.maxStock = product.stock_qty || 50;
        currentOrderState.variant = null;
        currentOrderState.district = 'ঢাকা';
        currentOrderState.shippingCost = 60;

        coProductTitle.textContent = product.title;
        coProductImg.src = product.main_image || '/favicon.ico';
        coProductPrice.textContent = Number(currentOrderState.unitPrice).toLocaleString();

        coErrorAlert.style.display = 'none';
        coErrorAlert.textContent = '';

        coAreaDhakaCard.classList.add('active');
        coAreaOutsideCard.classList.remove('active');

        coVariantPills.innerHTML = '';
        if (product.variants && Array.isArray(product.variants) && product.variants.length > 0) {
            coVariantField.style.display = 'flex';
            product.variants.forEach((v, idx) => {
                const pill = document.createElement('button');
                pill.type = 'button';
                pill.className = 'zippy-variant-pill' + (idx === 0 ? ' active' : '');

                const vPrice = v.price ? Number(v.price) : currentOrderState.unitPrice;
                const priceDiff = vPrice - Number(product.price);
                const diffText = priceDiff > 0 ? ` (+৳${priceDiff})` : (priceDiff < 0 ? ` (-৳${Math.abs(priceDiff)})` : '');

                pill.textContent = (v.name || v) + diffText;
                if (idx === 0) {
                    currentOrderState.variant = v.name || v;
                    currentOrderState.unitPrice = vPrice;
                }

                pill.addEventListener('click', () => {
                    coVariantPills.querySelectorAll('.zippy-variant-pill').forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    currentOrderState.variant = v.name || v;
                    currentOrderState.unitPrice = vPrice;
                    updateQuickCheckoutTotals();
                });

                coVariantPills.appendChild(pill);
            });
        } else {
            coVariantField.style.display = 'none';
        }

        const savedName = localStorage.getItem('zippy_co_name');
        const savedPhone = localStorage.getItem('zippy_co_phone');
        const savedAddress = localStorage.getItem('zippy_co_address');
        if (savedName && !coCustName.value) coCustName.value = savedName;
        if (savedPhone && !coCustPhone.value) coCustPhone.value = savedPhone;
        if (savedAddress && !coCustAddress.value) coCustAddress.value = savedAddress;

        updateQuickCheckoutTotals();
        drawer.classList.add('active');
    };

    function closeQuickCheckout() {
        drawer.classList.remove('active');
        coErrorAlert.style.display = 'none';
    }

    drawerBackBtn.addEventListener('click', closeQuickCheckout);
    drawerCloseBtn.addEventListener('click', closeQuickCheckout);

    coQtyDec.addEventListener('click', () => {
        if (currentOrderState.quantity > 1) {
            currentOrderState.quantity--;
            updateQuickCheckoutTotals();
        }
    });

    coQtyInc.addEventListener('click', () => {
        if (currentOrderState.quantity < currentOrderState.maxStock && currentOrderState.quantity < 50) {
            currentOrderState.quantity++;
            updateQuickCheckoutTotals();
        }
    });

    coAreaDhakaCard.addEventListener('click', () => {
        coAreaDhakaCard.classList.add('active');
        coAreaOutsideCard.classList.remove('active');
        currentOrderState.district = 'ঢাকা';
        currentOrderState.shippingCost = 60;
        updateQuickCheckoutTotals();
    });

    coAreaOutsideCard.addEventListener('click', () => {
        coAreaOutsideCard.classList.add('active');
        coAreaDhakaCard.classList.remove('active');
        currentOrderState.district = 'ঢাকার বাইরে';
        currentOrderState.shippingCost = 120;
        updateQuickCheckoutTotals();
    });

    coConfirmBtn.addEventListener('click', () => {
        const name = coCustName.value.trim();
        const rawPhone = coCustPhone.value.trim();
        const address = coCustAddress.value.trim();

        if (!name) {
            showCheckoutError('Please enter your full name.');
            coCustName.focus();
            return;
        }

        const digits = rawPhone.replace(/[^0-9]/g, '');
        const cleanPhone = digits.startsWith('8801') ? digits.slice(2) : digits;
        if (!/^01[3-9]\d{8}$/.test(cleanPhone)) {
            showCheckoutError('Please enter a valid 11-digit mobile number (e.g. 017XXXXXXXX).');
            coCustPhone.focus();
            return;
        }

        if (!address) {
            showCheckoutError('Please enter your full delivery address.');
            coCustAddress.focus();
            return;
        }

        coErrorAlert.style.display = 'none';
        coConfirmBtn.disabled = true;
        coBtnReady.style.display = 'none';
        coBtnSpinner.style.display = 'inline-flex';

        axios.post(quickOrderRoute, {
            product_id: currentOrderState.productId,
            customer_name: name,
            customer_phone: cleanPhone,
            customer_address: address,
            district: currentOrderState.district,
            quantity: currentOrderState.quantity,
            variant: currentOrderState.variant,
        }, {
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        }).then(res => {
            coConfirmBtn.disabled = false;
            coBtnReady.style.display = 'inline-flex';
            coBtnSpinner.style.display = 'none';

            if (res.data && res.data.success) {
                localStorage.setItem('zippy_co_name', name);
                localStorage.setItem('zippy_co_phone', cleanPhone);
                localStorage.setItem('zippy_co_address', address);

                closeQuickCheckout();

                appendBotMessage(
                    res.data.reply,
                    null,
                    null,
                    res.data.order_card || res.data.order_status,
                    null,
                    res.data.created_order,
                    null
                );

                if (res.data.chips && Array.isArray(res.data.chips) && res.data.chips.length > 0) {
                    renderChips(res.data.chips);
                }
            } else {
                showCheckoutError(res.data.message || 'Unable to place order. Please try again.');
            }
        }).catch(err => {
            coConfirmBtn.disabled = false;
            coBtnReady.style.display = 'inline-flex';
            coBtnSpinner.style.display = 'none';

            let errMsg = 'Connection error. Please try again.';
            if (err.response && err.response.data && err.response.data.message) {
                errMsg = err.response.data.message;
            }
            showCheckoutError(errMsg);
        });
    });

    function showCheckoutError(msg) {
        coErrorAlert.textContent = msg;
        coErrorAlert.style.display = 'block';
    }

    function loadHistory() {
        updateLangUi();
        axios.post(historyRoute, {
            activity: collectUserActivity(),
            language: currentChatLang
        }, {
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        }).then(res => {
            if (res.data && res.data.success) {
                if (res.data.history && res.data.history.length > 0) {
                    res.data.history.forEach(item => {
                        if (item.role === 'user') {
                            appendUserMessage(item.content, item.time);
                        } else if (item.role === 'assistant') {
                            appendBotMessage(item.content, item.time);
                        }
                    });
                    if (res.data.chips && Array.isArray(res.data.chips) && res.data.chips.length > 0) {
                        renderChips(res.data.chips);
                    }
                } else if (res.data.greeting) {
                    appendBotMessage(res.data.greeting, 'Just now');
                    if (res.data.chips && Array.isArray(res.data.chips) && res.data.chips.length > 0) {
                        renderChips(res.data.chips);
                    }
                }
            }
        }).catch(() => {
            axios.get(historyRoute + '?language=' + encodeURIComponent(currentChatLang), {
                headers: { 'Accept': 'application/json' }
            }).then(res => {
                if (res.data && res.data.success) {
                    if (res.data.history && res.data.history.length > 0) {
                        res.data.history.forEach(item => {
                            if (item.role === 'user') {
                                appendUserMessage(item.content, item.time);
                            } else if (item.role === 'assistant') {
                                appendBotMessage(item.content, item.time);
                            }
                        });
                        if (res.data.chips && Array.isArray(res.data.chips) && res.data.chips.length > 0) {
                            renderChips(res.data.chips);
                        }
                    } else if (res.data.greeting) {
                        appendBotMessage(res.data.greeting, 'Just now');
                        if (res.data.chips && Array.isArray(res.data.chips) && res.data.chips.length > 0) {
                            renderChips(res.data.chips);
                        }
                    }
                }
            }).catch(() => {});
        });
    }


})();
</script>
