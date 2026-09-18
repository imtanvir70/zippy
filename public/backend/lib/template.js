(() => {
    function isMobile() {
        return window.innerWidth < 992;
    }
    function save(e, t) {
        try { localStorage.setItem(e, t); } catch (err) {}
    }
    function getState() {
        return {
            theme: localStorage.getItem("theme") || "auto",
            accent: localStorage.getItem("accent") || "#6366f1",
            radius: localStorage.getItem("radius") || "18",
            font: localStorage.getItem("font-size") || "16",
            blur: localStorage.getItem("glass-blur") || "14",
            shadow: localStorage.getItem("shadow-int") || "8",
            sidebarState: localStorage.getItem("sidebar-state") || "open",
            sidebarWidth: localStorage.getItem("sidebar-width") || "290"
        };
    }
    function anyOpen() {
        var sidebar = document.getElementById("sidebar");
        var studioPanel = document.getElementById("panel");
        var userPanel = document.getElementById("userPanel");
        return (sidebar && sidebar.classList.contains("mobile-open")) ||
               (studioPanel && studioPanel.classList.contains("open")) ||
               (userPanel && userPanel.classList.contains("open"));
    }
    function syncOverlay() {
        var overlay = document.getElementById("overlay");
        var isOpen = anyOpen();
        if (overlay) overlay.classList.toggle("show", isOpen);
        document.body.classList.toggle("no-scroll", isOpen);
    }
    function closeAllPanels() {
        var sidebar = document.getElementById("sidebar");
        var studioPanel = document.getElementById("panel");
        var userPanel = document.getElementById("userPanel");
        if (sidebar) sidebar.classList.remove("mobile-open");
        if (studioPanel) studioPanel.classList.remove("open");
        if (userPanel) userPanel.classList.remove("open");
        syncOverlay();
    }
    function setTheme(e, t = !0) {
        if (t) save("theme", e);
        var a = "auto" === e ? (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light") : e;
        document.documentElement.setAttribute("data-bs-theme", a);
        document.querySelectorAll(".theme-btn").forEach(function(el) { el.classList.remove("active"); });
        var targetBtn = document.getElementById("theme-" + e);
        if (targetBtn) targetBtn.classList.add("active");
        document.querySelectorAll(".theme-opt").forEach(function(el) {
            el.classList.toggle("active", el.getAttribute("data-theme") === e);
        });
        var icon = document.getElementById("themeIcon");
        if (icon) {
            icon.className = "light" === e ? "fa-sun-bright" : "dark" === e ? "fa-moon" : "fa-laptop";
        }
        if (typeof window.updateHeaderThemeIcon === 'function') {
            window.updateHeaderThemeIcon(a);
        }
    }
    function setRadius(e, t = !0) {
        if (t) save("radius", e);
        document.documentElement.style.setProperty("--radius", e + "px");
        var valEl = document.getElementById("radiusVal");
        if (valEl) valEl.textContent = e + "px";
        var slider = document.getElementById("radiusSlider");
        if (slider && slider.value !== String(e)) slider.value = e;
    }
    function setFontSize(e, t = !0) {
        if (t) save("font-size", e);
        document.documentElement.style.setProperty("--bs-body-font-size", e + "px");
        var valEl = document.getElementById("fontVal");
        if (valEl) valEl.textContent = e + "px";
        var slider = document.getElementById("fontSlider");
        if (slider && slider.value !== String(e)) slider.value = e;
    }
    function setBlur(e, t = !0) {
        if (t) save("glass-blur", e);
        document.documentElement.style.setProperty("--blur", e + "px");
        var valEl = document.getElementById("blurVal");
        if (valEl) valEl.textContent = e + "px";
        var slider = document.getElementById("blurSlider");
        if (slider && slider.value !== String(e)) slider.value = e;
    }
    function setShadow(e, t = !0) {
        if (t) save("shadow-int", e);
        document.documentElement.style.setProperty("--shadow-int", (e / 100).toFixed(3));
        var valEl = document.getElementById("shadowVal");
        if (valEl) valEl.textContent = e + "%";
        var slider = document.getElementById("shadowSlider");
        if (slider && slider.value !== String(e)) slider.value = e;
    }
    function setSidebarWidth(e, t = !0) {
        if (t) save("sidebar-width", e);
        document.documentElement.style.setProperty("--sidebar-w", e + "px");
        var valEl = document.getElementById("sidebarWidthVal");
        if (valEl) valEl.textContent = e + "px";
        var slider = document.getElementById("sidebarWidthSlider");
        if (slider && slider.value !== String(e)) slider.value = e;
    }
    function toggleSidebar(e) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();
        var sidebar = document.getElementById("sidebar");
        var appShell = document.getElementById("appShell");
        if (isMobile()) {
            closeAllPanels();
            if (sidebar) sidebar.classList.toggle("mobile-open");
        } else {
            if (appShell) {
                appShell.classList.toggle("sidebar-collapsed");
                save("sidebar-state", appShell.classList.contains("sidebar-collapsed") ? "collapsed" : "open");
            }
        }
        syncOverlay();
    }
    function togglePanel(e) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();
        var userPanel = document.getElementById("userPanel");
        var sidebar = document.getElementById("sidebar");
        var studioPanel = document.getElementById("panel");
        if (userPanel) userPanel.classList.remove("open");
        if (sidebar) sidebar.classList.remove("mobile-open");
        if (studioPanel) studioPanel.classList.toggle("open");
        syncOverlay();
    }
    function toggleUserPanel(e) {
        if (e && typeof e.preventDefault === 'function') e.preventDefault();
        var studioPanel = document.getElementById("panel");
        var sidebar = document.getElementById("sidebar");
        var userPanel = document.getElementById("userPanel");
        if (studioPanel) studioPanel.classList.remove("open");
        if (sidebar) sidebar.classList.remove("mobile-open");
        if (userPanel) userPanel.classList.toggle("open");
        syncOverlay();
    }
    function resetSettings() {
        localStorage.clear();
        location.reload();
    }
    function setAccent(e, t = !0) {
        if (t) save("accent", e);
        var match = e.replace("#", "").match(/.{2}/g);
        var a = match ? match.map(x => parseInt(x, 16)).join(", ") : "99, 102, 241";
        document.documentElement.style.setProperty("--accent", e);
        document.documentElement.style.setProperty("--accent-rgb", a);
        document.querySelectorAll(".swatch, .custom-color-picker").forEach(function(el) {
            el.classList.remove("active");
        });
        var targetSwatch = document.querySelector(`.swatch[data-color="${e}"]`);
        var preview = document.getElementById("customColorPreview");
        var picker = document.getElementById("customAccentPicker");
        if (targetSwatch) {
            targetSwatch.classList.add("active");
            if (preview) preview.innerHTML = '<i class="bi bi-palette-fill"></i>';
        } else {
            var customPicker = document.querySelector(".custom-color-picker");
            if (customPicker) customPicker.classList.add("active");
            if (picker) picker.value = e;
            if (preview) {
                preview.style.background = e;
                preview.innerHTML = '<i class="fa-light fa-paintbrush fa-shake" style="opacity:1;transform:scale(1);"></i>';
            }
        }
    }
    function refreshTooltips(container = document) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            container.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                var inst = bootstrap.Tooltip.getInstance(el);
                if (inst) inst.dispose();
                new bootstrap.Tooltip(el, { trigger: "hover focus", boundary: "window", container: "body" });
            });
        }
    }

    document.addEventListener("click", function(e) {
        if (e.target && e.target.closest("#overlay")) {
            closeAllPanels();
        }
    });

    window.addEventListener("resize", function() {
        if (!isMobile()) {
            var sidebar = document.getElementById("sidebar");
            if (sidebar) sidebar.classList.remove("mobile-open");
        }
        syncOverlay();
    });

    function syncStateToDom() {
        var state = getState();
        setAccent(state.accent, false);
        setRadius(state.radius, false);
        setFontSize(state.font, false);
        setBlur(state.blur, false);
        setShadow(state.shadow, false);
        setSidebarWidth(state.sidebarWidth, false);
        setTheme(state.theme, false);

        var sliderBlur = document.getElementById("blurSlider");
        if (sliderBlur) sliderBlur.value = state.blur;
        var sliderFont = document.getElementById("fontSlider");
        if (sliderFont) sliderFont.value = state.font;
        var sliderRadius = document.getElementById("radiusSlider");
        if (sliderRadius) sliderRadius.value = state.radius;
        var sliderShadow = document.getElementById("shadowSlider");
        if (sliderShadow) sliderShadow.value = state.shadow;
        var sliderWidth = document.getElementById("sidebarWidthSlider");
        if (sliderWidth) sliderWidth.value = state.sidebarWidth;

        var appShell = document.getElementById("appShell");
        if ("collapsed" === state.sidebarState && !isMobile() && appShell) {
            appShell.classList.add("sidebar-collapsed");
        }
        syncOverlay();
    }

    document.addEventListener("turbo:load", syncStateToDom);
    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", syncStateToDom);
    } else {
        syncStateToDom();
    }

    window.updateAccent = e => setAccent(e, true);
    window.updateRadius = e => setRadius(e, true);
    window.updateFontSize = e => setFontSize(e, true);
    window.updateBlur = e => setBlur(e, true);
    window.updateShadow = e => setShadow(e, true);
    window.updateSidebarWidth = e => setSidebarWidth(e, true);
    window.togglePanel = togglePanel;
    window.toggleSidebar = toggleSidebar;
    window.toggleUserPanel = toggleUserPanel;
    window.closeAllPanels = closeAllPanels;
    window.syncOverlay = syncOverlay;
    window.resetSettings = resetSettings;
    window.setTheme = setTheme;
    window.setAccent = setAccent;
    window.setRadius = setRadius;
    window.setFontSize = setFontSize;
    window.setBlur = setBlur;
    window.setShadow = setShadow;
    window.setSidebarWidth = setSidebarWidth;
    window.refreshTooltips = refreshTooltips;
    window.$doc = window.jQuery ? window.jQuery(document) : null;
})();
