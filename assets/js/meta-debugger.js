/**
 * Meta Debugger - Core Frontend & Admin Controller
 *
 * Production-grade vanilla JavaScript module. Zero external runtime dependencies.
 *
 * @package MetaDebugger
 */

(function () {
    'use strict';

    const cfg = window.metadebugConfig || window.wpmdConfig;
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
    const $productCard = $('wpmd-product-card');
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
    window.metadebugTogglePanel = panelOpen;
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
                renderProductCard(json.data.item);
                renderMetaTree(state.metaStore);
                updateStatus(`${json.data.total} meta keys loaded for #${id}`);
            } else {
                showErrorState(json.data || i18n.errorLoading);
            }
        } catch (err) {
            showErrorState(i18n.errorLoading || 'Error loading metadata.');
        }
    }

    function renderProductCard(item) {
        if (!$productCard || !item) return;

        if ($editLink) {
            $editLink.href = item.edit_url || '#';
            $editLink.hidden = !item.edit_url;
        }

        $productCard.innerHTML = `
            <div class="wpmd-card-inner">
                ${item.thumb ? `<img class="wpmd-thumb" src="${escapeHtml(item.thumb)}" alt="${escapeHtml(item.name)}">` : '<div class="wpmd-thumb-placeholder"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>'}
                <div class="wpmd-card-details">
                    <h3 class="wpmd-card-title">${escapeHtml(item.name)}</h3>
                    <div class="wpmd-card-meta-line">
                        <span class="wpmd-badge">${escapeHtml(item.type)}</span>
                        <span class="wpmd-badge wpmd-status-${escapeHtml(item.status)}">${escapeHtml(item.status)}</span>
                        ${item.sku ? `<span class="wpmd-card-sku">SKU: ${escapeHtml(item.sku)}</span>` : ''}
                        <span class="wpmd-card-id">ID: #${item.id}</span>
                    </div>
                </div>
            </div>
        `;
        $productCard.hidden = false;
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
        const rows = $content.querySelectorAll('.wpmd-tree-row');
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
            $content.querySelectorAll('.wpmd-node-children').forEach(el => el.hidden = false);
            $content.querySelectorAll('.wpmd-caret').forEach(el => el.classList.add('open'));
        });
    }

    if ($collapseAll) {
        $collapseAll.addEventListener('click', function () {
            if (!$content) return;
            $content.querySelectorAll('.wpmd-node-children').forEach(el => el.hidden = true);
            $content.querySelectorAll('.wpmd-caret').forEach(el => el.classList.remove('open'));
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

        const fragment = document.createDocumentFragment();
        const $tree = document.createElement('div');
        $tree.className = 'wpmd-tree';

        keys.forEach(key => {
            const value = metaData[key];
            const $row = buildTreeRow(key, value, 0);
            $tree.appendChild($row);
        });

        fragment.appendChild($tree);
        $content.innerHTML = '';
        $content.appendChild(fragment);
    }

    function buildTreeRow(key, val, depth) {
        const type = getValueType(val);
        const $row = document.createElement('div');
        $row.className = `wpmd-tree-row wpmd-type-${type}`;
        $row.setAttribute('data-key', key);

        const previewStr = getPreviewString(val);
        $row.setAttribute('data-value-preview', previewStr);

        const isExpandable = type === 'object' || type === 'array';

        $row.innerHTML = `
            <div class="wpmd-row-header" style="padding-left: ${depth * 14 + 10}px">
                ${isExpandable ? `<span class="wpmd-caret">▶</span>` : `<span class="wpmd-caret-spacer"></span>`}
                <span class="wpmd-key">${escapeHtml(key)}</span>
                <span class="wpmd-type-tag">${type}</span>
                <span class="wpmd-val-preview">${escapeHtml(previewStr)}</span>
                <div class="wpmd-row-actions">
                    <button class="wpmd-act-btn wpmd-view-btn" title="View Details">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button class="wpmd-act-btn wpmd-copy-btn" title="Copy Value">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </div>
            </div>
        `;

        if (isExpandable) {
            const $children = document.createElement('div');
            $children.className = 'wpmd-node-children';
            $children.hidden = true;

            const childKeys = Object.keys(val);
            childKeys.forEach(ckey => {
                $children.appendChild(buildTreeRow(ckey, val[ckey], depth + 1));
            });

            $row.appendChild($children);

            const $rowHeader = $row.querySelector('.wpmd-row-header');
            const $caret = $row.querySelector('.wpmd-caret');

            $rowHeader.addEventListener('click', function (e) {
                if (e.target.closest('.wpmd-row-actions')) return;
                const isHidden = $children.hidden;
                $children.hidden = !isHidden;
                if ($caret) $caret.classList.toggle('open', isHidden);
            });
        }

        // Action Handlers
        const $viewBtn = $row.querySelector('.wpmd-view-btn');
        const $copyBtn = $row.querySelector('.wpmd-copy-btn');

        if ($viewBtn) {
            $viewBtn.addEventListener('click', e => {
                e.stopPropagation();
                openValueModal(key, val, type);
            });
        }

        if ($copyBtn) {
            $copyBtn.addEventListener('click', e => {
                e.stopPropagation();
                copyToClipboard(typeof val === 'object' ? JSON.stringify(val, null, 2) : String(val), $copyBtn);
            });
        }

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
        navigator.clipboard.writeText(text).then(() => {
            const orig = $btn.innerHTML;
            $btn.innerHTML = `<span style="font-size:11px;color:#10b981;">${i18n.copied || 'Copied!'}</span>`;
            setTimeout(() => $btn.innerHTML = orig, 1500);
        }).catch(err => {
            console.error('Clipboard copy failed:', err);
        });
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
