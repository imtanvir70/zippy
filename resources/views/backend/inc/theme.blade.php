<div class="panel" id="panel">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-wand-magic-sparkles text-info fs-5"></i>
            <h5 class="fw-bold mb-0">Studio Customizer</h5>
        </div>
        <button type="button" class="topbar-action-btn" onclick="togglePanel(event)" title="Close Panel">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="panel-body p-3 p-md-4 flex-grow-1">
        <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-muted mb-2">Theme Mode</label>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light border flex-fill theme-btn active d-flex align-items-center justify-content-center gap-1.5 py-2 text-sm fw-bold" id="theme-light" onclick="setTheme('light')">
                    <i class="fa-solid fa-sun text-warning"></i> Light
                </button>
                <button type="button" class="btn btn-light border flex-fill theme-btn d-flex align-items-center justify-content-center gap-1.5 py-2 text-sm fw-bold" id="theme-dark" onclick="setTheme('dark')">
                    <i class="fa-solid fa-moon text-info"></i> Dark
                </button>
                <button type="button" class="btn btn-light border flex-fill theme-btn d-flex align-items-center justify-content-center gap-1.5 py-2 text-sm fw-bold" id="theme-auto" onclick="setTheme('auto')">
                    <i class="fa-solid fa-laptop text-secondary"></i> Auto
                </button>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-muted mb-2">Accent Color</label>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <div class="swatch active" data-color="#6366f1" style="background: #6366f1;" onclick="updateAccent('#6366f1')" title="Indigo"></div>
                <div class="swatch" data-color="#0ea5e9" style="background: #0ea5e9;" onclick="updateAccent('#0ea5e9')" title="Sky Blue"></div>
                <div class="swatch" data-color="#10b981" style="background: #10b981;" onclick="updateAccent('#10b981')" title="Emerald"></div>
                <div class="swatch" data-color="#f59e0b" style="background: #f59e0b;" onclick="updateAccent('#f59e0b')" title="Amber"></div>
                <div class="swatch" data-color="#ef4444" style="background: #ef4444;" onclick="updateAccent('#ef4444')" title="Rose"></div>
                <div class="swatch" data-color="#8b5cf6" style="background: #8b5cf6;" onclick="updateAccent('#8b5cf6')" title="Purple"></div>
                <div class="swatch" data-color="#ec4899" style="background: #ec4899;" onclick="updateAccent('#ec4899')" title="Pink"></div>
                <div class="swatch" data-color="#14b8a6" style="background: #14b8a6;" onclick="updateAccent('#14b8a6')" title="Teal"></div>
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-bold small text-uppercase text-muted m-0">Border Radius</label>
                <span class="badge bg-light text-dark border small" id="radiusVal">18px</span>
            </div>
            <input type="range" class="form-range" id="radiusSlider" min="0" max="32" value="18" oninput="updateRadius(this.value)">
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-bold small text-uppercase text-muted m-0">Base Font Size</label>
                <span class="badge bg-light text-dark border small" id="fontVal">16px</span>
            </div>
            <input type="range" class="form-range" id="fontSlider" min="13" max="20" value="16" oninput="updateFontSize(this.value)">
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-bold small text-uppercase text-muted m-0">Glassmorphism Blur</label>
                <span class="badge bg-light text-dark border small" id="blurVal">14px</span>
            </div>
            <input type="range" class="form-range" id="blurSlider" min="0" max="30" value="14" oninput="updateBlur(this.value)">
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-bold small text-uppercase text-muted m-0">Shadow Intensity</label>
                <span class="badge bg-light text-dark border small" id="shadowVal">8%</span>
            </div>
            <input type="range" class="form-range" id="shadowSlider" min="0" max="25" value="8" oninput="updateShadow(this.value)">
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-bold small text-uppercase text-muted m-0">Sidebar Width</label>
                <span class="badge bg-light text-dark border small" id="sidebarWidthVal">290px</span>
            </div>
            <input type="range" class="form-range" id="sidebarWidthSlider" min="240" max="340" value="290" oninput="updateSidebarWidth(this.value)">
        </div>

        <div class="pt-2">
            <button type="button" class="btn btn-outline-danger w-100 py-2 rounded-3 text-sm fw-bold" onclick="resetSettings()">
                <i class="fa-solid fa-arrow-rotate-left me-1"></i> Reset to Defaults
            </button>
        </div>
    </div>
</div>

