(function () {
    "use strict";

    function applyTheme(theme) {
        document.documentElement.setAttribute("data-theme", theme);
        document.documentElement.setAttribute("data-bs-theme", theme);
        localStorage.setItem("zippy_admin_theme", theme);
    }

    function initTheme() {
        var saved = localStorage.getItem("zippy_admin_theme");
        if (saved) {
            applyTheme(saved);
        } else {
            var prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
            applyTheme(prefersDark ? "dark" : "light");
        }
    }

    window.toggleThemeMode = function () {
        var current = document.documentElement.getAttribute("data-theme") || "light";
        var next = current === "dark" ? "light" : "dark";
        applyTheme(next);
    };

    window.toggleSidebar = function (e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var sidebar = document.getElementById("adminSidebar");
        var backdrop = document.getElementById("adminBackdrop");
        if (!sidebar) return;

        if (window.innerWidth < 992) {
            var isOpen = sidebar.classList.contains("mobile-open");
            if (isOpen) {
                sidebar.classList.remove("mobile-open");
                if (backdrop) backdrop.classList.remove("active");
                document.body.classList.remove("admin-no-scroll");
            } else {
                sidebar.classList.add("mobile-open");
                if (backdrop) backdrop.classList.add("active");
                document.body.classList.add("admin-no-scroll");
            }
        }
    };

    window.closeAdminSidebar = function () {
        var sidebar = document.getElementById("adminSidebar");
        var backdrop = document.getElementById("adminBackdrop");
        if (sidebar) sidebar.classList.remove("mobile-open");
        if (backdrop) backdrop.classList.remove("active");
        document.body.classList.remove("admin-no-scroll");
    };

    window.togglePanel = function (e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var panel = document.getElementById("panel");
        var userPanel = document.getElementById("userPanel");
        var overlay = document.getElementById("overlay");
        if (userPanel) userPanel.classList.remove("open");
        if (panel) {
            panel.classList.toggle("open");
            if (overlay) {
                overlay.classList.toggle("show", panel.classList.contains("open"));
            }
        }
    };

    window.toggleUserPanel = function (e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        var panel = document.getElementById("panel");
        var userPanel = document.getElementById("userPanel");
        var overlay = document.getElementById("overlay");
        if (panel) panel.classList.remove("open");
        if (userPanel) {
            userPanel.classList.toggle("open");
            if (overlay) {
                overlay.classList.toggle("show", userPanel.classList.contains("open"));
            }
        }
    };

    window.addEventListener("resize", function () {
        if (window.innerWidth >= 992) {
            window.closeAdminSidebar();
        }
    });

    window.showToast = function (message, type, duration) {
        type = type || "success";
        duration = duration || 3500;

        var container = document.getElementById("adminToastContainer");
        if (!container) {
            container = document.createElement("div");
            container.id = "adminToastContainer";
            container.className = "admin-toast-container";
            document.body.appendChild(container);
        }

        var iconMap = {
            success: "fa-circle-check",
            error: "fa-circle-exclamation",
            warning: "fa-triangle-exclamation",
            info: "fa-circle-info"
        };
        var icon = iconMap[type] || iconMap.info;

        var toast = document.createElement("div");
        toast.className = "admin-toast admin-toast-" + type;

        toast.innerHTML =
            '<div class="admin-toast-icon"><i class="fa-solid ' + icon + '"></i></div>' +
            '<div class="flex-grow-1">' + message + '</div>' +
            '<button type="button" class="admin-toast-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>';

        container.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.add("show");
        });

        function removeToast() {
            toast.classList.remove("show");
            setTimeout(function () {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 250);
        }

        var closeBtn = toast.querySelector(".admin-toast-close");
        if (closeBtn) {
            closeBtn.addEventListener("click", removeToast);
        }

        setTimeout(removeToast, duration);
    };

    function setupAxios() {
        if (typeof window.axios === "undefined") return;

        window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            window.axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfMeta.getAttribute("content");
        }

        window.axios.interceptors.request.use(function (config) {
            var currentCsrf = document.querySelector('meta[name="csrf-token"]');
            if (currentCsrf) {
                config.headers["X-CSRF-TOKEN"] = currentCsrf.getAttribute("content");
            }
            return config;
        }, function (error) {
            return Promise.reject(error);
        });

        window.axios.interceptors.response.use(function (response) {
            var method = (response.config.method || "").toUpperCase();
            if (["POST", "PUT", "PATCH", "DELETE"].indexOf(method) !== -1) {
                if (response.data && response.data.message && response.config.autoToast) {
                    window.showToast(response.data.message, "success");
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
                            errorList.push(data.errors[key].join(", "));
                        }
                    }
                    window.showToast(errorList.join("<br>"), "error", 5000);
                } else if (data.message) {
                    window.showToast(data.message, "error");
                }
            } else if (status === 419) {
                window.showToast("Session expired. Please refresh the page.", "warning");
            } else if (status === 401 || status === 403) {
                window.showToast((data && data.message) ? data.message : "Access denied.", "error");
            } else if (status >= 500) {
                window.showToast("Internal server error. Please try again.", "error");
            } else if (error.message) {
                window.showToast(error.message, "error");
            }

            return Promise.reject(error);
        });
    }

    window.openGlobalModal = function (options) {
        options = options || {};
        var modalWrapper = document.getElementById("adminGlobalModal");
        var modalDialog = document.getElementById("adminGlobalModalDialog");
        var modalTitle = document.getElementById("adminGlobalModalTitle");
        var modalIcon = document.getElementById("adminGlobalModalIcon");
        var modalBody = document.getElementById("adminGlobalModalBody");
        var modalFooter = document.getElementById("adminGlobalModalFooter");

        if (!modalWrapper) return;

        modalDialog.className = "admin-modal-dialog " + (options.size ? "modal-" + options.size : "modal-md");
        modalTitle.textContent = options.title || "Quick Action";
        modalIcon.innerHTML = '<i class="fa-solid ' + (options.icon || "fa-cube") + '"></i>';

        if (options.footer) {
            modalFooter.innerHTML = options.footer;
            modalFooter.style.display = "flex";
        } else {
            modalFooter.innerHTML = "";
            modalFooter.style.display = "none";
        }

        if (options.url) {
            modalBody.innerHTML = '<div class="admin-modal-loader"><div class="spinner-border text-primary" role="status"></div></div>';
            modalWrapper.classList.add("active");
            document.body.classList.add("admin-no-scroll");

            window.axios.get(options.url, { skipToast: true }).then(function (res) {
                modalBody.innerHTML = res.data;
                bindModalForms(modalBody, options);
                if (typeof options.onLoaded === "function") {
                    options.onLoaded(modalBody);
                }
            }).catch(function () {
                modalBody.innerHTML = '<div class="alert alert-danger mb-0">Failed to load content. Please try again.</div>';
            });
        } else {
            modalBody.innerHTML = options.html || "";
            bindModalForms(modalBody, options);
            modalWrapper.classList.add("active");
            document.body.classList.add("admin-no-scroll");
            if (typeof options.onLoaded === "function") {
                options.onLoaded(modalBody);
            }
        }
    };

    window.closeGlobalModal = function () {
        var modalWrapper = document.getElementById("adminGlobalModal");
        if (modalWrapper) {
            modalWrapper.classList.remove("active");
            document.body.classList.remove("admin-no-scroll");
        }
    };

    function bindModalForms(container, modalOptions) {
        var forms = container.querySelectorAll("form");
        forms.forEach(function (form) {
            if (form.getAttribute("data-bound") === "true") return;
            form.setAttribute("data-bound", "true");

            form.addEventListener("submit", function (e) {
                e.preventDefault();
                window.submitModalForm(form, modalOptions);
            });
        });
    }

    window.submitModalForm = function (form, options) {
        options = options || {};
        var submitBtn = form.querySelector('button[type="submit"]');
        var originalBtnHtml = submitBtn ? submitBtn.innerHTML : "";

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Processing...';
        }

        var formData = new FormData(form);
        var method = (form.getAttribute("method") || "POST").toUpperCase();
        var action = form.getAttribute("action");

        var spoofedMethod = formData.get("_method");
        if (spoofedMethod) {
            method = spoofedMethod.toUpperCase();
        }

        window.axios({
            method: method,
            url: action,
            data: formData
        }).then(function (res) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }

            if (options.closeOnSuccess !== false) {
                window.closeGlobalModal();
            }

            var refreshTarget = form.getAttribute("data-refresh-target") || options.refreshTarget;
            var refreshUrl = form.getAttribute("data-refresh-url") || options.refreshUrl;

            if (refreshTarget && refreshUrl) {
                window.axios.get(refreshUrl, { skipToast: true }).then(function (targetRes) {
                    var el = document.querySelector(refreshTarget);
                    if (el) el.innerHTML = targetRes.data;
                });
            }

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
                var dt = window.jQuery(".dataTable").DataTable();
                if (dt && typeof dt.ajax !== "undefined" && typeof dt.ajax.reload === "function") {
                    dt.ajax.reload(null, false);
                }
            }

            document.dispatchEvent(new CustomEvent("admin:form-submitted", {
                detail: { form: form, response: res }
            }));

            if (typeof options.onSuccess === "function") {
                options.onSuccess(res);
            }
        }).catch(function () {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        });
    };

    window.showAutoSaveBadge = function (text, isError) {
        var el = document.getElementById("adminAutoSaveIndicator");
        if (!el) {
            el = document.createElement("div");
            el.id = "adminAutoSaveIndicator";
            el.style.position = "fixed";
            el.style.bottom = "24px";
            el.style.right = "24px";
            el.style.zIndex = "999999";
            el.style.display = "flex";
            el.style.alignItems = "center";
            el.style.gap = "8px";
            el.style.padding = "8px 18px";
            el.style.borderRadius = "9999px";
            el.style.fontSize = "13px";
            el.style.fontWeight = "600";
            el.style.boxShadow = "0 8px 24px rgba(0,0,0,0.18)";
            el.style.transition = "opacity 0.25s ease, transform 0.25s ease";
            el.style.pointerEvents = "none";
            document.body.appendChild(el);
        }

        if (isError) {
            el.style.background = "#ef4444";
            el.style.color = "#ffffff";
            el.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + (text || "Failed to save");
        } else {
            el.style.background = "linear-gradient(135deg, #10b981 0%, #059669 100%)";
            el.style.color = "#ffffff";
            el.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + (text || "Auto-saved");
        }

        el.style.opacity = "1";
        el.style.transform = "translateY(0)";

        if (window._autoSaveTimer) {
            clearTimeout(window._autoSaveTimer);
        }
        window._autoSaveTimer = setTimeout(function () {
            el.style.opacity = "0";
            el.style.transform = "translateY(8px)";
        }, 1800);
    };

    window.autoSaveSetting = function (key, value, url) {
        if (!window.axios) return Promise.reject(new Error("Axios not loaded"));
        url = url || "/admin/settings/ajax-update";
        var payload = { _single_key: key };
        payload[key] = value;
        return window.axios.post(url, payload, { skipToast: true }).then(function (res) {
            window.showAutoSaveBadge("Saved: " + key);
            return res;
        }).catch(function (err) {
            window.showAutoSaveBadge("Save failed", true);
            return Promise.reject(err);
        });
    };

    function initAutoSave() {
        if (window.__adminAutoSaveInitialized) return;
        window.__adminAutoSaveInitialized = true;

        document.addEventListener("change", function (e) {
            var target = e.target;
            if (!target || !target.matches) return;

            if (target.matches('input[type="checkbox"].form-check-input, input[type="checkbox"][role="switch"]')) {
                var form = target.closest("form#settingsForm, form#enterpriseSettingsForm, form[data-autosave='true'], .auto-save-zone");
                var autoSaveExplicit = target.getAttribute("data-autosave") === "true";

                if (form || autoSaveExplicit) {
                    var key = target.name;
                    if (!key) return;
                    var val = target.checked ? "1" : "0";
                    var url = "/admin/settings/ajax-update";

                    if (form && form.id === "enterpriseSettingsForm") {
                        url = form.getAttribute("action") || "/enterprise-settings/update";
                        var payload = {};
                        payload[key] = val;
                        var tabInput = form.querySelector('input[name="_active_tab"]');
                        if (tabInput) payload["_active_tab"] = tabInput.value;
                        window.axios.post(url, payload, { skipToast: true }).then(function () {
                            window.showAutoSaveBadge("Saved");
                        }).catch(function () {
                            target.checked = !target.checked;
                            window.showAutoSaveBadge("Failed to save", true);
                        });
                        return;
                    }

                    if (form && form.getAttribute("data-autosave-url")) {
                        url = form.getAttribute("data-autosave-url");
                    }

                    var data = { _single_key: key };
                    data[key] = val;

                    window.axios.post(url, data, { skipToast: true }).then(function () {
                        window.showAutoSaveBadge("Saved");
                    }).catch(function () {
                        target.checked = !target.checked;
                        window.showAutoSaveBadge("Failed to save", true);
                    });
                }
            }
        });

        var debounceMap = {};
        document.addEventListener("input", function (e) {
            var target = e.target;
            if (!target || !target.matches) return;

            if (target.matches("form#settingsForm input[type='text'], form#settingsForm input[type='number'], form#settingsForm textarea, form[data-autosave='true'] input:not([type='password']):not([type='file']), form[data-autosave='true'] textarea")) {
                var key = target.name;
                if (!key) return;

                if (debounceMap[key]) {
                    clearTimeout(debounceMap[key]);
                }

                debounceMap[key] = setTimeout(function () {
                    var val = target.value;
                    var data = { _single_key: key };
                    data[key] = val;
                    window.axios.post("/admin/settings/ajax-update", data, { skipToast: true }).then(function () {
                        window.showAutoSaveBadge("Saved");
                    }).catch(function () {
                        window.showAutoSaveBadge("Failed to save", true);
                    });
                }, 650);
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        initTheme();
        setupAxios();
        initAutoSave();

        document.addEventListener("click", function (e) {
            var trigger = e.target.closest("[data-modal-open='true'], [data-admin-modal='true']");
            if (trigger) {
                e.preventDefault();
                window.openGlobalModal({
                    title: trigger.getAttribute("data-modal-title") || trigger.getAttribute("title") || "Action",
                    icon: trigger.getAttribute("data-modal-icon") || "fa-cube",
                    size: trigger.getAttribute("data-modal-size") || "md",
                    url: trigger.getAttribute("data-modal-url") || trigger.getAttribute("href"),
                    refreshTarget: trigger.getAttribute("data-refresh-target"),
                    refreshUrl: trigger.getAttribute("data-refresh-url")
                });
            }
        });

        document.addEventListener("submit", function (e) {
            var form = e.target.closest("form[data-async-form='true']");
            if (form && !form.closest("#adminGlobalModalBody")) {
                e.preventDefault();
                window.submitModalForm(form);
            }
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                window.closeGlobalModal();
                window.closeAdminSidebar();
            }
        });
    });

    document.addEventListener("turbo:load", function () {
        initTheme();
        setupAxios();
        initAutoSave();
    });
})();

