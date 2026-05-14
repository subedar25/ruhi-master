/**
 * Searchable Select2 dropdowns for Ruhi Master Livewire pages.
 *
 * Markup (pattern):
 *   <div id="your-anchor" class="d-none" data-s2-value="{{ $boundValue }}"></div>
 *   <input type="hidden" wire:model.live="propName" id="your-hidden">
 *   <div wire:ignore class="d-inline-block">
 *     <select id="your-select" class="... js-ruhi-master-select2"
 *       data-s2-hidden="#your-hidden"
 *       data-s2-anchor="#your-anchor"
 *       data-s2-placeholder="Optional placeholder"
 *       data-s2-dropdown-parent="modal"
 *       data-s2-dropdown-class="optional-css-class"
 *       data-s2-allow-clear="true">
 *       ...
 *     </select>
 *   </div>
 *
 * Server anchor data-s2-value is authoritative after each Livewire round-trip (submit, filter, etc.).
 */
(function () {
    'use strict';
    var ruhiGlobalOpenFocusBound = false;

    /**
     * Match options by label substring (case-insensitive). If the user types only digits,
     * also match when those digits appear in the label's numeric parts (e.g. "48" → "GS-48").
     */
    function ruhiSelect2Matcher(params, data) {
        if (!data) {
            return null;
        }
        if (data.children && data.children.length > 0) {
            var clone = $.extend(true, {}, data);
            var matchedChildren = [];
            for (var i = 0; i < data.children.length; i++) {
                var childMatch = ruhiSelect2Matcher(params, data.children[i]);
                if (childMatch != null) {
                    matchedChildren.push(childMatch);
                }
            }
            if (matchedChildren.length) {
                clone.children = matchedChildren;
                return clone;
            }
            return null;
        }

        var term = (params.term || '').trim();
        if (term === '') {
            return data;
        }

        var text = data.text == null ? '' : String(data.text);
        var upperText = text.toUpperCase();
        var upperTerm = term.toUpperCase();
        if (upperText.indexOf(upperTerm) >= 0) {
            return data;
        }

        if (/^\d+$/.test(term)) {
            var digitsOnly = text.replace(/\D/g, '');
            if (digitsOnly.indexOf(term) >= 0) {
                return data;
            }
        }

        return null;
    }

    function ruhiMasterSelect2Bind($select) {
        if (!window.jQuery || !window.jQuery.fn.select2) return;
        var $ = window.jQuery;
        if (!$select.length) return;

        var hiddenSel = $select.attr('data-s2-hidden');
        var anchorSel = $select.attr('data-s2-anchor');
        if (!hiddenSel) return;

        var $hidden = $(hiddenSel);
        if (!$hidden.length) return;

        var $anchor = anchorSel ? $(anchorSel) : $();

        function readAnchorOrHidden() {
            if ($anchor.length && $anchor.attr('data-s2-value') !== undefined && $anchor.attr('data-s2-value') !== null) {
                return String($anchor.attr('data-s2-value'));
            }
            return String($hidden.val() || '');
        }

        var placeholderStr = $select.attr('data-s2-placeholder') || 'Select';

        var ddParentAttr = ($select.attr('data-s2-dropdown-parent') || '').toLowerCase();
        var $dropdownParent = $(document.body);
        if (ddParentAttr === 'modal') {
            var $modal = $select.closest('.modal');
            if ($modal.length) {
                $dropdownParent = $modal;
            }
        }

        var allowClearAttr = ($select.attr('data-s2-allow-clear') || '').toLowerCase();
        var allowClear = allowClearAttr === '1' || allowClearAttr === 'true';

        var dropdownCssClass = $select.attr('data-s2-dropdown-class') || '';

        if ($select.data('ruhiMs2Init')) {
            $select.off('change.ruhiMs2');
            try {
                $select.select2('destroy');
            } catch (ignore) {}
            $select.removeData('ruhiMs2Init');
        }

        if (!$select.data('ruhiMs2Init')) {
            var s2opts = {
                width: '100%',
                minimumResultsForSearch: 0,
                allowClear: allowClear,
                dropdownParent: $dropdownParent,
                matcher: ruhiSelect2Matcher,
            };
            if (allowClear && ! $select.prop('multiple')) {
                s2opts.placeholder = {
                    id: '',
                    text: placeholderStr,
                };
            } else {
                s2opts.placeholder = placeholderStr;
            }
            if (dropdownCssClass) {
                s2opts.dropdownCssClass = dropdownCssClass;
            }
            var closeOnSelectAttr = ($select.attr('data-s2-close-on-select') || '').toLowerCase();
            if (closeOnSelectAttr === '0' || closeOnSelectAttr === 'false') {
                s2opts.closeOnSelect = false;
            }
            $select.select2(s2opts);
            $select.data('ruhiMs2Init', true);
            $select.on('change.ruhiMs2', function () {
                if ($select.data('ruhiMs2Programmatic')) return;
                var v = $(this).val();
                if ($select.prop('multiple')) {
                    if (v == null) v = [];
                    v = Array.isArray(v) ? v.filter(function (x) { return x !== null && x !== undefined && x !== ''; }).join(',') : String(v);
                } else {
                    if (v === null || v === undefined) v = '';
                    v = String(v);
                }
                $hidden.val(v);
                var hel = $hidden.get(0);
                hel.dispatchEvent(new Event('input', { bubbles: true }));
                hel.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        var hv = readAnchorOrHidden();

        // Align DOM hidden input with server-rendered anchor only — do NOT dispatch
        // input/change here. wire:model.live on the hidden would treat those as a new
        // user edit and fire another Livewire request (global loader stuck / endless loop).
        if (String($hidden.val() || '') !== hv) {
            $hidden.val(hv);
        }

        var currentVal;
        if ($select.prop('multiple')) {
            var cur = $select.val();
            var curStr = Array.isArray(cur) ? cur.join(',') : (cur == null || cur === '' ? '' : String(cur));
            currentVal = curStr;
        } else {
            currentVal = String($select.val() || '');
        }
        if (currentVal !== String(hv)) {
            $select.data('ruhiMs2Programmatic', true);
            if ($select.prop('multiple')) {
                var arr = hv.length ? String(hv).split(',').map(function (x) { return x.trim(); }).filter(function (x) { return x.length; }) : [];
                $select.val(arr).trigger('change.select2');
            } else {
                $select.val(hv.length ? hv : null).trigger('change.select2');
            }
            $select.data('ruhiMs2Programmatic', false);
        }
    }

    function ruhiMasterSelect2InitAll() {
        if (!window.jQuery) return;
        window.jQuery('.js-ruhi-master-select2').each(function () {
            ruhiMasterSelect2Bind(window.jQuery(this));
        });
    }

    function ruhiMasterSelect2Schedule() {
        ruhiBindGlobalOpenFocus();
        window.setTimeout(ruhiMasterSelect2InitAll, 0);
        window.setTimeout(ruhiMasterSelect2InitAll, 50);
        window.setTimeout(ruhiMasterSelect2InitAll, 150);
    }

    function ruhiBindGlobalOpenFocus() {
        if (ruhiGlobalOpenFocusBound || !window.jQuery) return;
        ruhiGlobalOpenFocusBound = true;
        window.jQuery(document).on('select2:open.ruhiMs2Global', function () {
            // Global listener: applies to all Select2 dropdowns in the app.
            window.setTimeout(function () {
                var $search = window.jQuery('.select2-container--open .select2-search__field').last();
                if ($search.length) {
                    $search.trigger('focus');
                    $search.get(0).focus();
                }
            }, 0);
            window.setTimeout(function () {
                var $search = window.jQuery('.select2-container--open .select2-search__field').last();
                if ($search.length) {
                    $search.trigger('focus');
                    $search.get(0).focus();
                }
            }, 60);
        });
    }

    document.addEventListener('livewire:load', ruhiMasterSelect2Schedule);
    document.addEventListener('livewire:navigated', ruhiMasterSelect2Schedule);
    document.addEventListener('livewire:init', function () {
        if (typeof Livewire === 'undefined' || !Livewire.hook) return;
        Livewire.hook('commit', function (ref) {
            var succeed = ref.succeed;
            succeed(ruhiMasterSelect2Schedule);
        });
        Livewire.hook('message.processed', ruhiMasterSelect2Schedule);
    });
    document.addEventListener('DOMContentLoaded', function () {
        ruhiBindGlobalOpenFocus();
        window.setTimeout(ruhiMasterSelect2InitAll, 50);
    });
    document.addEventListener('shown.bs.modal', ruhiMasterSelect2Schedule);
})();
