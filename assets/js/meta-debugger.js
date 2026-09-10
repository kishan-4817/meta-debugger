/**
 * Meta Debugger - Core Frontend & Admin Controller
 *
 * Production-grade vanilla JavaScript module. Zero external runtime dependencies.
 *
 * @package MetaDebugger
 */

(function () {
    'use strict';

    const cfg = window.wpmdConfig;
    if (!cfg) {
        return;
    }

    const i18n = cfg.i18n || {};

    // ── State ──────────────────────────────────────────────────────────────────
    const state = {
        objectId:       0,
        metaStore:      {},
        searchTimer:    null,
        activeIdx:      -1,
        activeModalKey: '',
        activeModalVal: null,
    };

    // ── DOM References ────────────────────────────────────────────────────────
    const $ = id => document.getElementById(id);

    const $panel       = $('wpmd-panel');
    const $overlay     = $('wpmd-overlay');
    const $toggle      = $('wpmd-toggle');
    const $close       = $panel ? $panel.querySelector('.wpmd-close') : null;
    const $search      = $('wpmd-search');
    const $searchClear = $('wpmd-search-clear');
    const $results     = $('wpmd-search-results');
    const $editLink    = $('wpmd-edit-link');
    const $metaFilter  = $('wpmd-meta-filter');
    const $content     = $('wpmd-content');
    const $status      = $('wpmd-status');
    const $expandAll   = $('wpmd-expand-all');
    const $collapseAll = $('wpmd-collapse-all');

    // Modal elements
    const $modal      = $('wpmd-modal');
    const $modalClose = $('wpmd-modal-close');
    const $modalTitle = $('wpmd-modal-title');
    const $modalType  = $('wpmd-modal-type');
    const $modalValue = $('wpmd-modal-value');
    const $modalCopy  = $('wpmd-modal-copy');

    if (!$panel) {
        return;
    }

    // ── Global Panel Open / Close ─────────────────────────────────────────────
    function panelOpen() {
        $panel.classList.add('open');
        $panel.setAttribute('aria-hidden', 'false');
        if ($overlay) {
            $overlay.classList.add('active');
            $overlay.setAttribute('aria-hidden', 'false');
        }
        if ($search) {
            $search.focus();
        }

        if (!state.objectId && cfg.currentId) {
            selectItem(cfg.currentId);
        } else if (!state.objectId) {
            loadFirstItem();
        }
    }

    function panelClose() {
        $panel.classList.remove('open');
        $panel.setAttribute('aria-hidden', 'true');
        if ($overlay) {
            $overlay.classList.remove('active');
            $overlay.setAttribute('aria-hidden', 'true');
        }
        hideDropdown();
    }

    // Expose global toggler for Admin Bar
    window.wpmdTogglePanel = function () {
        if ($panel.classList.contains('open')) {
            panelClose();
        } else {
            panelOpen();
        }
    };

    if ($toggle) {
        $toggle.addEventListener('click', window.wpmdTogglePanel);
    }
    if ($close) {
        $close.addEventListener('click', panelClose);
    }
    if ($overlay) {
        $overlay.addEventListener('click', panelClose);
    }

    // Global keyboard listener (Ctrl+Shift+D or Cmd+Shift+D, and Escape)
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'D' || e.key === 'd')) {
            e.preventDefault();
            window.wpmdTogglePanel();
        }
        if (e.key === 'Escape') {
            if ($modal && $modal.classList.contains('open')) {
                modalClose();
                return;
            }
            if ($panel.classList.contains('open')) {
                panelClose();
            }
        }
    });

    // ── Search Handling ───────────────────────────────────────────────────────
    if ($search) {
        $search.addEventListener('input', function () {
            const q = this.value.trim();
            if ($searchClear) {
                $searchClear.hidden = !q;
            }
            clearTimeout(state.searchTimer);
            if (q.length < 1) {
                hideDropdown();
                return;
            }
            state.searchTimer = setTimeout(() => fetchSearchItems(q), 260);
        });

        $search.addEventListener('keydown', function (e) {
            const items = $results ? $results.querySelectorAll('.wpmd-result-item') : [];
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                state.activeIdx = Math.min(state.activeIdx + 1, items.length - 1);
                highlightResult(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                state.activeIdx = Math.max(state.activeIdx - 1, -1);
                highlightResult(items);
            } else if (e.key === 'Enter' && state.activeIdx >= 0) {
                e.preventDefault();
                items[state.activeIdx].click();
            }
        });
    }

    if ($searchClear) {
        $searchClear.addEventListener('click', function () {
            $search.value = '';
            $searchClear.hidden = true;
            hideDropdown();
            $search.focus();
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.wpmd-search-section')) {
            hideDropdown();
        }
    });

    function highlightResult(items) {
        items.forEach((el, i) => {
            el.classList.toggle('highlighted', i === state.activeIdx);
            el.setAttribute('aria-selected', i === state.activeIdx ? 'true' : 'false');
        });
    }

    async function fetchSearchItems(query) {
        const fd = new FormData();
        fd.append('action', 'wpmd_search');
        fd.append('nonce', cfg.nonce);
        fd.append('search', query);

        try {
            const res = await fetch(cfg.ajaxUrl, { method: 'POST', body: fd });
            const json = await res.json();
            renderDropdown(json.success ? json.data : []);
        } catch (err) {
            console.error('MetaDebugger search error:', err);
        }
    }

    function renderDropdown(items) {
        if (!$results) return;
        state.activeIdx = -1;

        if (!items || !items.length) {
            $results.innerHTML = `<div class="wpmd-no-results">${escapeHtml(i18n.noProductsFound || 'No items found')}</div>`;
            $results.hidden = false;
            $search.setAttribute('aria-expanded', 'true');
            return;
        }

        $results.innerHTML = items.map(item => `
            <div class="wpmd-result-item" data-id="${item.id}" role="option" aria-selected="false">
                <div class="wpmd-result-title">${escapeHtml(item.title)}</div>
                <div class="wpmd-result-meta">
                    <span class="wpmd-badge">${escapeHtml(item.type)}</span>
                    ${item.sku ? `<span class="wpmd-sku">SKU: ${escapeHtml(item.sku)}</span>` : ''}
                    <span class="wpmd-id">#${item.id}</span>
                </div>
            </div>
        `).join('');

        $results.hidden = false;
        $search.setAttribute('aria-expanded', 'true');

        $results.querySelectorAll('.wpmd-result-item').forEach(el => {
            el.addEventListener('click', function () {
                const id = parseInt(this.getAttribute('data-id'), 10);
                selectItem(id);
                hideDropdown();
                if ($search) $search.value = '';
                if ($searchClear) $searchClear.hidden = true;
            });
        });
    }

    function hideDropdown() {
        if ($results) {
            $results.hidden = true;
            $results.innerHTML = '';
        }
        if ($search) {
            $search.setAttribute('aria-expanded', 'false');
        }
        state.activeIdx = -1;
    }

    async function loadFirstItem() {
        fetchSearchItems('');
    }

    // ── Item Selection & Metadata Fetching ───────────────────────────────────
    async function selectItem(id) {
        state.objectId = id;
        showLoadingState();

        const fd = new FormData();
        fd.append('action', 'wpmd_fetch');
        fd.append('nonce', cfg.nonce);
        fd.append('object_id', id);

        try {
            const res = await fetch(cfg.ajaxUrl, { method: 'POST', body: fd });
            const json = await res.json();

            if (json.success && json.data) {
                state.metaStore = json.data.meta || {};
                // Update edit link in header
                if ($editLink && json.data.item) {
                    $editLink.href = json.data.item.edit_url || '#';
                    $editLink.hidden = !json.data.item.edit_url;
                }
                renderMetaTree(state.metaStore);
                updateStatus(`${json.data.total} meta keys loaded for #${id}`);
            } else {
                showErrorState(json.data || i18n.errorLoading);
            }
        } catch (err) {
            showErrorState(i18n.errorLoading || 'Error loading metadata.');
        }
    }

    // ── Meta Filter Search ────────────────────────────────────────────────────
    if ($metaFilter) {
        $metaFilter.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            filterMetaTree(query);
        });
    }

    function filterMetaTree(query) {
        if (!$content) return;
        const rows = $content.querySelectorAll('.wpmd-meta-row');
        if (!query) {
            rows.forEach(r => r.style.display = '');
            return;
        }

        rows.forEach(row => {
            const key = (row.getAttribute('data-key') || '').toLowerCase();
            const val = (row.getAttribute('data-value-preview') || '').toLowerCase();
            const match = key.includes(query) || val.includes(query);
            row.style.display = match ? '' : 'none';
        });
    }

    // ── Expand / Collapse Actions ─────────────────────────────────────────────
    if ($expandAll) {
        $expandAll.addEventListener('click', function () {
            if (!$content) return;
            $content.querySelectorAll('.wpmd-tree-body').forEach(el => el.classList.add('open'));
            $content.querySelectorAll('.wpmd-tree-toggle').forEach(el => el.classList.add('open'));
        });
    }

    if ($collapseAll) {
        $collapseAll.addEventListener('click', function () {
            if (!$content) return;
            $content.querySelectorAll('.wpmd-tree-body').forEach(el => el.classList.remove('open'));
            $content.querySelectorAll('.wpmd-tree-toggle').forEach(el => el.classList.remove('open'));
        });
    }

    // ── Metadata Tree Rendering ───────────────────────────────────────────────
    function renderMetaTree(metaData) {
        if (!$content) return;

        const keys = Object.keys(metaData);
        if (!keys.length) {
            $content.innerHTML = `<div class="wpmd-empty">${escapeHtml(i18n.noMetaFound || 'No metadata found for this item.')}</div>`;
            return;
        }

        const $wrap = document.createElement('div');
        $wrap.className = 'wpmd-category';

        keys.forEach(key => {
            const value = metaData[key];
            $wrap.appendChild(buildMetaRow(key, value));
        });

        $content.innerHTML = '';
        $content.appendChild($wrap);
    }

    function buildMetaRow(key, val) {
        const type = getValueType(val);
        const isExpandable = type === 'object' || type === 'array';
        const previewStr = getPreviewString(val);

        const $row = document.createElement('div');
        $row.className = 'wpmd-meta-row';
        $row.setAttribute('data-key', key);
        $row.setAttribute('data-value-preview', previewStr);

        // Key column
        const $keyCol = document.createElement('div');
        $keyCol.className = 'wpmd-meta-key-col';
        $keyCol.innerHTML = `
            <span class="wpmd-meta-key">${escapeHtml(key)}</span>
            <span class="wpmd-type-pill wpmd-type-${type}">${type}</span>
        `;

        // Value column
        const $valCol = document.createElement('div');
        $valCol.className = 'wpmd-meta-val-col';

        if (isExpandable) {
            // Collapsible tree toggle button
            const $toggle = document.createElement('button');
            $toggle.className = 'wpmd-tree-toggle';
            $toggle.innerHTML = `
                <svg class="wpmd-chevron" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                ${previewStr}
            `;

            const $treeBody = document.createElement('div');
            $treeBody.className = 'wpmd-tree-body';

            const $treeRoot = document.createElement('div');
            $treeRoot.className = 'wpmd-tree-root';

            Object.entries(val).forEach(([ckey, cval]) => {
                const $node = document.createElement('div');
                $node.className = 'wpmd-tree-node';
                const ctype = getValueType(cval);
                const cPreview = getPreviewString(cval);
                $node.innerHTML = `
                    <div class="wpmd-tree-row">
                        <span class="wpmd-tree-key">${escapeHtml(ckey)}</span>
                        <span class="wpmd-tree-colon">:</span>
                        <span class="wpmd-tree-val wpmd-type-${ctype}">${escapeHtml(cPreview)}</span>
                    </div>
                `;
                $treeRoot.appendChild($node);
            });

            $treeBody.appendChild($treeRoot);

            $toggle.addEventListener('click', () => {
                const isOpen = $treeBody.classList.toggle('open');
                $toggle.classList.toggle('open', isOpen);
            });

            $valCol.appendChild($toggle);
            $valCol.appendChild($treeBody);
        } else {
            const $preview = document.createElement('div');
            $preview.className = `wpmd-meta-val-preview wpmd-type-${type}`;
            $preview.textContent = previewStr;
            $preview.addEventListener('click', () => openValueModal(key, val, type));
            $valCol.appendChild($preview);
        }

        // Actions column
        const $actions = document.createElement('div');
        $actions.className = 'wpmd-meta-actions';
        $actions.innerHTML = `
            <button class="wpmd-inspect-btn" title="View Details">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
            <button class="wpmd-copy-btn" title="Copy Value">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            </button>
        `;

        $actions.querySelector('.wpmd-inspect-btn').addEventListener('click', e => {
            e.stopPropagation();
            openValueModal(key, val, type);
        });

        $actions.querySelector('.wpmd-copy-btn').addEventListener('click', e => {
            e.stopPropagation();
            const str = typeof val === 'object' ? JSON.stringify(val, null, 2) : String(val);
            copyToClipboard(str, $actions.querySelector('.wpmd-copy-btn'));
        });

        $row.appendChild($keyCol);
        $row.appendChild($valCol);
        $row.appendChild($actions);

        return $row;
    }

    // ── Value Modal Handling ──────────────────────────────────────────────────
    function openValueModal(key, val, type) {
        if (!$modal) return;

        state.activeModalKey = key;
        state.activeModalVal = val;

        if ($modalTitle) $modalTitle.textContent = key;
        if ($modalType) $modalType.textContent = type;

        const jsonStr = typeof val === 'object' ? JSON.stringify(val, null, 2) : String(val);
        if ($modalValue) $modalValue.textContent = jsonStr;

        $modal.classList.add('open');
        $modal.setAttribute('aria-hidden', 'false');
    }

    function modalClose() {
        if (!$modal) return;
        $modal.classList.remove('open');
        $modal.setAttribute('aria-hidden', 'true');
    }

    if ($modalClose) {
        $modalClose.addEventListener('click', modalClose);
    }
    if ($modal) {
        $modal.addEventListener('click', e => {
            if (e.target === $modal) modalClose();
        });
    }

    if ($modalCopy) {
        $modalCopy.addEventListener('click', function () {
            const val = state.activeModalVal;
            const str = typeof val === 'object' ? JSON.stringify(val, null, 2) : String(val);
            copyToClipboard(str, $modalCopy);
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function getValueType(val) {
        if (val === null) return 'null';
        if (Array.isArray(val)) return 'array';
        return typeof val;
    }

    function getPreviewString(val) {
        if (val === null) return 'null';
        if (typeof val === 'boolean') return val ? 'true' : 'false';
        if (typeof val === 'object') {
            const count = Object.keys(val).length;
            return Array.isArray(val) ? `Array(${count})` : `Object{${count}}`;
        }
        return String(val);
    }

    function copyToClipboard(text, $btn) {
        const onSuccess = () => {
            const orig = $btn.innerHTML;
            $btn.innerHTML = `<span style="font-size:11px;color:#10b981;">${i18n.copied || 'Copied!'}</span>`;
            setTimeout(() => $btn.innerHTML = orig, 1500);
        };

        const onError = (err) => {
            console.error('Clipboard copy failed:', err);
            fallbackCopy(text);
            onSuccess();
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(onSuccess).catch(onError);
        } else {
            fallbackCopy(text);
            onSuccess();
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.top = '-9999px';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand('copy');
        } catch (err) {
            console.error('Fallback copy failed:', err);
        }
        document.body.removeChild(textarea);
    }

    function showLoadingState() {
        if ($content) {
            $content.innerHTML = '<div class="wpmd-loading"><div class="wpmd-spinner"></div><span>Loading metadata…</span></div>';
        }
    }

    function showErrorState(msg) {
        if ($content) {
            $content.innerHTML = `<div class="wpmd-error">${escapeHtml(msg)}</div>`;
        }
    }

    function updateStatus(text) {
        if ($status) {
            $status.textContent = text;
        }
    }

    function escapeHtml(str) {
        if (typeof str !== 'string') return str;
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
})();
