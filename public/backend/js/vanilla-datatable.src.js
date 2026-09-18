/**
 * VanillaDataTable - High Performance Pure Vanilla JavaScript Replacement for jQuery DataTables
 * Specifically tailored for Laravel Yajra Server-Side DataTables and Bootstrap 5
 * No jQuery required. Zero external dependencies.
 */
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.VanillaDataTable = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {
    'use strict';

    var instances = {};

    class VanillaDataTable {
        constructor(target, options) {
            options = options || {};
            this.table = typeof target === 'string' ? document.querySelector(target) : target;
            if (!this.table) {
                console.warn('VanillaDataTable: Table target not found', target);
                return;
            }

            this.selector = typeof target === 'string' ? target : (this.table.id ? '#' + this.table.id : null);
            if (this.selector && instances[this.selector]) {
                instances[this.selector].destroy();
            }

            this.options = Object.assign({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'desc']],
                columns: [],
                ajax: null,
                drawCallback: null,
                language: {
                    zeroRecords: 'No matching records found',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total entries)',
                    search: 'Search:',
                    searchPlaceholder: '',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: '<i class="fa-solid fa-chevron-right fa-xs"></i>',
                        previous: '<i class="fa-solid fa-chevron-left fa-xs"></i>'
                    }
                }
            }, options);

            this.pageIndex = 0;
            this.pageLength = parseInt(this.options.pageLength, 10) || 10;
            this.searchValue = '';
            this.currentOrder = (this.options.order && this.options.order.length) 
                ? [this.options.order[0][0], this.options.order[0][1]] 
                : [0, 'desc'];
            this.drawCounter = 0;
            this.recordsTotal = 0;
            this.recordsFiltered = 0;
            this.data = [];
            this.lastJson = null;
            this.isProcessing = false;
            this.eventListeners = {};

            // Page API
            var self = this;
            this.page = {
                len: function(newLen) {
                    if (newLen !== undefined) {
                        self.pageLength = parseInt(newLen, 10) || 10;
                        self.pageIndex = 0;
                        return self;
                    }
                    return self.pageLength;
                },
                info: function() {
                    var total = self.recordsFiltered;
                    var start = total === 0 ? 0 : self.pageIndex * self.pageLength + 1;
                    var end = Math.min((self.pageIndex + 1) * self.pageLength, total);
                    return {
                        page: self.pageIndex,
                        pages: Math.ceil(total / self.pageLength) || 1,
                        start: start,
                        end: end,
                        length: self.pageLength,
                        recordsTotal: self.recordsTotal,
                        recordsDisplay: self.recordsFiltered
                    };
                }
            };

            // Ajax API
            this.ajax = {
                reload: function(callback, resetPaging) {
                    if (resetPaging) self.pageIndex = 0;
                    self.fetchData(callback);
                }
            };

            this.initDOM();
            this.bindEvents();

            if (this.selector) {
                instances[this.selector] = this;
            }
            this.table._vanillaDataTable = this;

            this.fetchData();
        }

        initDOM() {
            var parent = this.table.parentNode;
            if (!this.wrapper) {
                this.wrapper = document.createElement('div');
                this.wrapper.className = 'dataTables_wrapper dt-bootstrap5 no-footer';
                parent.insertBefore(this.wrapper, this.table);
            }

            var hasCustomDom = typeof this.options.dom === 'string';

            // Top bar (Length + Search) if default dom
            if (!hasCustomDom) {
                this.topRow = document.createElement('div');
                this.topRow.className = 'row mb-3 align-items-center';
                this.topRow.innerHTML = '<div class="col-sm-12 col-md-6 d-flex align-items-center">' +
                    '<div class="dataTables_length">' +
                        '<label class="d-inline-flex align-items-center gap-1.5 small text-muted">' +
                            '<span>Show</span>' +
                            '<select class="form-select form-select-sm d-inline-block w-auto mx-1" data-dt-length>' +
                                this.buildLengthOptions() +
                            '</select>' +
                            '<span>entries</span>' +
                        '</label>' +
                    '</div>' +
                '</div>' +
                '<div class="col-sm-12 col-md-6 d-flex justify-content-md-end">' +
                    '<div class="dataTables_filter">' +
                        '<label class="d-inline-flex align-items-center gap-1.5 small text-muted">' +
                            '<span>Search:</span>' +
                            '<input type="search" class="form-control form-control-sm d-inline-block w-auto ms-1" placeholder="' + (this.options.language.searchPlaceholder || '') + '" data-dt-search>' +
                        '</label>' +
                    '</div>' +
                '</div>';
                this.wrapper.appendChild(this.topRow);
            }

            // Processing overlay
            this.processingEl = document.createElement('div');
            this.processingEl.className = 'dataTables_processing card shadow-sm';
            this.processingEl.style.display = 'none';
            this.processingEl.innerHTML = this.options.language.processing || '<div>Loading...</div>';
            this.wrapper.appendChild(this.processingEl);

            // Move table inside wrapper
            this.tableContainer = document.createElement('div');
            this.tableContainer.className = 'table-responsive';
            this.tableContainer.appendChild(this.table);
            this.wrapper.appendChild(this.tableContainer);

            // Table styling
            this.table.classList.add('dataTable', 'no-footer');

            // Setup sortable headers
            this.setupHeaders();

            // Bottom bar (Info + Pagination)
            this.bottomRow = document.createElement('div');
            if (hasCustomDom) {
                this.bottomRow.className = 'd-flex align-items-center justify-content-between p-3 border-top border-secondary border-opacity-25 flex-wrap gap-3';
                this.bottomRow.innerHTML = '<div class="d-flex align-items-center gap-3">' +
                    '<div class="small text-muted font-monospace" data-dt-info></div>' +
                    '<div class="bottom-length-select d-flex align-items-center gap-1.5 small text-muted font-monospace">' +
                        '<label class="d-inline-flex align-items-center gap-1 text-nowrap">' +
                            '<span>Show</span>' +
                            '<select class="form-select form-select-sm d-inline-block w-auto mx-1" data-dt-length>' +
                                this.buildLengthOptions() +
                            '</select>' +
                            '<span>/ page</span>' +
                        '</label>' +
                    '</div>' +
                '</div>' +
                '<div class="pagination-erp" data-dt-pagination></div>';
            } else {
                this.bottomRow.className = 'row mt-3 align-items-center';
                this.bottomRow.innerHTML = '<div class="col-sm-12 col-md-5">' +
                    '<div class="dataTables_info small text-muted" data-dt-info></div>' +
                '</div>' +
                '<div class="col-sm-12 col-md-7 d-flex justify-content-md-end">' +
                    '<div class="dataTables_paginate paging_simple_numbers" data-dt-pagination></div>' +
                '</div>';
            }
            this.wrapper.appendChild(this.bottomRow);

            this.infoEl = this.bottomRow.querySelector('[data-dt-info]');
            this.paginationEl = this.bottomRow.querySelector('[data-dt-pagination]');
            this.lengthSelects = this.wrapper.querySelectorAll('[data-dt-length]');
            this.searchInput = this.wrapper.querySelector('[data-dt-search]');
        }

        buildLengthOptions() {
            var menu = this.options.lengthMenu;
            var values = (menu && menu[0]) ? menu[0] : [10, 25, 50, 100];
            var labels = (menu && menu[1]) ? menu[1] : values;
            var html = '';
            for (var i = 0; i < values.length; i++) {
                var val = values[i];
                var text = labels[i];
                var sel = (val == this.pageLength) ? ' selected' : '';
                html += '<option value="' + val + '"' + sel + '>' + text + '</option>';
            }
            return html;
        }

        setupHeaders() {
            var thead = this.table.querySelector('thead');
            if (!thead) return;
            var ths = thead.querySelectorAll('th');
            var cols = this.options.columns || [];

            ths.forEach((th, idx) => {
                var col = cols[idx] || {};
                var isOrderable = col.orderable !== false;
                if (isOrderable) {
                    th.classList.add('sorting');
                    th.style.cursor = 'pointer';
                    th.addEventListener('click', () => {
                        this.handleSortClick(idx);
                    });
                }
            });
            this.updateHeaderSortClasses();
        }

        handleSortClick(colIdx) {
            var currentCol = this.currentOrder[0];
            var currentDir = this.currentOrder[1];
            var newDir = (currentCol === colIdx && currentDir === 'asc') ? 'desc' : 'asc';
            this.currentOrder = [colIdx, newDir];
            this.updateHeaderSortClasses();
            this.pageIndex = 0;
            this.trigger('order.dt', this);
            this.fetchData();
        }

        updateHeaderSortClasses() {
            var thead = this.table.querySelector('thead');
            if (!thead) return;
            var ths = thead.querySelectorAll('th');
            ths.forEach((th, idx) => {
                th.classList.remove('sorting_asc', 'sorting_desc');
                if (idx === this.currentOrder[0]) {
                    th.classList.add(this.currentOrder[1] === 'asc' ? 'sorting_asc' : 'sorting_desc');
                }
            });
        }

        bindEvents() {
            var self = this;
            this.lengthSelects.forEach(select => {
                select.addEventListener('change', function() {
                    self.pageLength = parseInt(this.value, 10) || 10;
                    self.pageIndex = 0;
                    self.lengthSelects.forEach(s => { if (s !== this) s.value = self.pageLength; });
                    self.fetchData();
                });
            });

            if (this.searchInput) {
                var timer = null;
                this.searchInput.addEventListener('input', function() {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        self.searchValue = this.value;
                        self.pageIndex = 0;
                        self.fetchData();
                    }, 300);
                });
            }
        }

        buildAjaxParams() {
            var cols = this.options.columns || [];
            var orderCol = this.currentOrder[0];
            var orderDir = this.currentOrder[1];

            var d = {
                draw: ++this.drawCounter,
                start: this.pageIndex * this.pageLength,
                length: this.pageLength,
                search: {
                    value: this.searchValue || '',
                    regex: false
                },
                order: [
                    {
                        column: orderCol,
                        dir: orderDir
                    }
                ],
                columns: cols.map((c, i) => ({
                    data: c.data || '',
                    name: c.name || c.data || '',
                    searchable: c.searchable !== false,
                    orderable: c.orderable !== false,
                    search: { value: '', regex: false }
                }))
            };

            // Custom user filter data hook
            if (this.options.ajax && typeof this.options.ajax.data === 'function') {
                this.options.ajax.data(d);
            }

            return d;
        }

        setProcessing(state) {
            this.isProcessing = !!state;
            if (this.processingEl) {
                this.processingEl.style.display = this.isProcessing ? 'block' : 'none';
            }
            if (this.wrapper) {
                if (this.isProcessing) {
                    this.wrapper.classList.add('dt-is-processing');
                    this.table.classList.add('dt-table-processing');
                } else {
                    this.wrapper.classList.remove('dt-is-processing');
                    this.table.classList.remove('dt-table-processing');
                }
            }
            // Trigger processing.dt event for document listeners
            var evt = new CustomEvent('processing.dt', {
                bubbles: true,
                detail: { settings: { nTable: this.table, nTableWrapper: this.wrapper }, processing: this.isProcessing }
            });
            this.table.dispatchEvent(evt);
            document.dispatchEvent(evt);
        }

        fetchData(callback) {
            var url = '';
            if (typeof this.options.ajax === 'string') {
                url = this.options.ajax;
            } else if (this.options.ajax && this.options.ajax.url) {
                url = this.options.ajax.url;
            }

            if (!url) {
                console.warn('VanillaDataTable: No AJAX URL specified');
                return;
            }

            this.setProcessing(true);
            var params = this.buildAjaxParams();

            if (typeof axios !== 'undefined') {
                axios.get(url, {
                    params: params,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    skipToast: true
                }).then(res => {
                    this.handleResponse(res.data, callback);
                }).catch(err => {
                    console.error('VanillaDataTable fetch error:', err);
                    this.setProcessing(false);
                });
            } else {
                var queryStr = this.serializeParams(params);
                var fullUrl = url + (url.indexOf('?') === -1 ? '?' : '&') + queryStr;
                fetch(fullUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(res => res.json())
                  .then(data => this.handleResponse(data, callback))
                  .catch(err => {
                      console.error('VanillaDataTable fetch error:', err);
                      this.setProcessing(false);
                  });
            }
        }

        serializeParams(obj, prefix) {
            var str = [];
            for (var p in obj) {
                if (obj.hasOwnProperty(p)) {
                    var k = prefix ? prefix + '[' + p + ']' : p;
                    var v = obj[p];
                    if (v !== null && typeof v === 'object') {
                        str.push(this.serializeParams(v, k));
                    } else {
                        str.push(encodeURIComponent(k) + '=' + encodeURIComponent(v !== undefined && v !== null ? v : ''));
                    }
                }
            }
            return str.filter(Boolean).join('&');
        }

        handleResponse(json, callback) {
            this.setProcessing(false);
            if (!json) return;

            this.lastJson = json;
            this.recordsTotal = parseInt(json.recordsTotal, 10) || 0;
            this.recordsFiltered = parseInt(json.recordsFiltered, 10) || 0;
            this.data = json.data || [];

            this.trigger('xhr.dt', { settings: { json: json }, json: json });

            this.renderTable();
            this.renderInfo();
            this.renderPagination();

            var settings = { json: json, nTable: this.table, nTableWrapper: this.wrapper };
            if (typeof this.options.drawCallback === 'function') {
                try {
                    this.options.drawCallback.call(this, settings);
                } catch(e) {
                    console.error('Error in drawCallback:', e);
                }
            }

            this.trigger('draw.dt', settings);
            if (typeof callback === 'function') {
                callback(json);
            }
        }

        renderTable() {
            var tbody = this.table.querySelector('tbody');
            if (!tbody) {
                tbody = document.createElement('tbody');
                this.table.appendChild(tbody);
            }
            tbody.innerHTML = '';

            var cols = this.options.columns || [];
            if (this.data.length === 0) {
                var colSpan = cols.length || 1;
                var zeroMsg = this.options.language.zeroRecords || 'No records found';
                tbody.innerHTML = '<tr><td colspan="' + colSpan + '" class="text-center py-4 text-muted">' + zeroMsg + '</td></tr>';
                return;
            }

            this.data.forEach((row, rowIdx) => {
                var tr = document.createElement('tr');
                tr.setAttribute('data-row-index', rowIdx);

                cols.forEach(col => {
                    var td = document.createElement('td');
                    if (col.className) td.className = col.className;
                    if (col.width) td.style.width = col.width;

                    var val = row[col.data];
                    if (typeof col.render === 'function') {
                        td.innerHTML = col.render(val, 'display', row);
                    } else if (val !== undefined && val !== null) {
                        td.innerHTML = String(val);
                    } else {
                        td.innerHTML = '';
                    }
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }

        renderInfo() {
            if (!this.infoEl) return;
            var total = this.recordsFiltered;
            var start = total === 0 ? 0 : this.pageIndex * this.pageLength + 1;
            var end = Math.min((this.pageIndex + 1) * this.pageLength, total);

            if (total === 0) {
                this.infoEl.textContent = this.options.language.infoEmpty || 'Showing 0 to 0 of 0 entries';
            } else {
                var template = this.options.language.info || 'Showing _START_ to _END_ of _TOTAL_ entries';
                var text = template
                    .replace('_START_', start.toLocaleString('en-US'))
                    .replace('_END_', end.toLocaleString('en-US'))
                    .replace('_TOTAL_', total.toLocaleString('en-US'));
                this.infoEl.textContent = text;
            }
        }

        renderPagination() {
            if (!this.paginationEl) return;
            var totalPages = Math.ceil(this.recordsFiltered / this.pageLength) || 1;
            var currentPage = this.pageIndex;

            var ul = document.createElement('ul');
            ul.className = 'pagination pagination-sm m-0 gap-1';

            // Previous button
            var prevLi = document.createElement('li');
            prevLi.className = 'page-item ' + (currentPage === 0 ? 'disabled' : '');
            prevLi.innerHTML = '<a class="page-link" href="javascript:void(0)" aria-label="Previous">' + this.options.language.paginate.previous + '</a>';
            if (currentPage > 0) {
                prevLi.querySelector('a').addEventListener('click', (e) => {
                    e.preventDefault();
                    this.pageIndex--;
                    this.fetchData();
                });
            }
            ul.appendChild(prevLi);

            // Page numbers
            var pages = this.getVisiblePages(currentPage, totalPages);
            pages.forEach(p => {
                var li = document.createElement('li');
                if (p === '...') {
                    li.className = 'page-item disabled';
                    li.innerHTML = '<span class="page-link border-0">...</span>';
                } else {
                    var pageNum = p - 1;
                    var isActive = pageNum === currentPage;
                    li.className = 'page-item ' + (isActive ? 'active' : '');
                    li.innerHTML = '<a class="page-link" href="javascript:void(0)">' + p + '</a>';
                    if (!isActive) {
                        li.querySelector('a').addEventListener('click', (e) => {
                            e.preventDefault();
                            this.pageIndex = pageNum;
                            this.fetchData();
                        });
                    }
                }
                ul.appendChild(li);
            });

            // Next button
            var nextLi = document.createElement('li');
            nextLi.className = 'page-item ' + (currentPage >= totalPages - 1 ? 'disabled' : '');
            nextLi.innerHTML = '<a class="page-link" href="javascript:void(0)" aria-label="Next">' + this.options.language.paginate.next + '</a>';
            if (currentPage < totalPages - 1) {
                nextLi.querySelector('a').addEventListener('click', (e) => {
                    e.preventDefault();
                    this.pageIndex++;
                    this.fetchData();
                });
            }
            ul.appendChild(nextLi);

            this.paginationEl.innerHTML = '';
            this.paginationEl.appendChild(ul);
        }

        getVisiblePages(current, total) {
            var cur = current + 1;
            if (total <= 7) {
                var res = [];
                for (var i = 1; i <= total; i++) res.push(i);
                return res;
            }
            if (cur <= 4) {
                return [1, 2, 3, 4, 5, '...', total];
            }
            if (cur >= total - 3) {
                return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
            }
            return [1, '...', cur - 1, cur, cur + 1, '...', total];
        }

        draw(resetPaging) {
            if (resetPaging) this.pageIndex = 0;
            this.fetchData();
            return this;
        }

        search(val) {
            this.searchValue = val || '';
            this.pageIndex = 0;
            if (this.searchInput && this.searchInput.value !== this.searchValue) {
                this.searchInput.value = this.searchValue;
            }
            return this;
        }

        order(newOrder) {
            if (newOrder !== undefined) {
                this.currentOrder = [newOrder[0][0], newOrder[0][1]];
                this.updateHeaderSortClasses();
                return this;
            }
            return [this.currentOrder];
        }

        clear() {
            this.data = [];
            var tbody = this.table.querySelector('tbody');
            if (tbody) tbody.innerHTML = '';
            return this;
        }

        destroy() {
            if (this.wrapper && this.table) {
                var parent = this.wrapper.parentNode;
                if (parent) {
                    parent.insertBefore(this.table, this.wrapper);
                    parent.removeChild(this.wrapper);
                }
            }
            this.table.classList.remove('dataTable', 'no-footer');
            delete this.table._vanillaDataTable;
            if (this.selector && instances[this.selector]) {
                delete instances[this.selector];
            }
        }

        on(event, handler) {
            if (!this.eventListeners[event]) this.eventListeners[event] = [];
            this.eventListeners[event].push(handler);
            return this;
        }

        off(event) {
            if (event) {
                delete this.eventListeners[event];
            } else {
                this.eventListeners = {};
            }
            return this;
        }

        trigger(event, data) {
            if (this.eventListeners[event]) {
                this.eventListeners[event].forEach(fn => {
                    try { fn.call(this, { type: event }, data, (data && data.json) ? data.json : null); } catch(e) { console.error(e); }
                });
            }
            var domEvent = new CustomEvent(event, { bubbles: true, detail: data });
            this.table.dispatchEvent(domEvent);
        }

        static isDataTable(target) {
            var el = typeof target === 'string' ? document.querySelector(target) : target;
            return !!(el && el._vanillaDataTable);
        }

        static getInstance(target) {
            var el = typeof target === 'string' ? document.querySelector(target) : target;
            return el ? el._vanillaDataTable : null;
        }
    }

    VanillaDataTable.instances = instances;

    return VanillaDataTable;
}));
