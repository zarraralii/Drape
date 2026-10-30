/* Drape storefront — vanilla JS, no dependencies. Every feature degrades to
   plain links/forms if this file fails to load. */
(function () {
    'use strict';

    var D = window.Drape || { routes: {} };
    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
    var desktop = window.matchMedia('(min-width: 1024px)');

    /* ---------- helpers ---------- */

    function request(url, body, method) {
        return fetch(url, {
            method: method || 'POST',
            headers: {
                'X-CSRF-TOKEN': D.csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body,
            credentials: 'same-origin',
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) {
                if (!res.ok) {
                    var message = data.message || (data.errors && Object.values(data.errors)[0][0]) || 'Something went wrong. Please try again.';
                    throw Object.assign(new Error(message), { data: data });
                }
                return data;
            });
        });
    }

    function toast(message, type) {
        var stack = $('[data-toasts]');
        if (!stack || !message) return;
        var el = document.createElement('div');
        el.className = 'toast' + (type === 'error' ? ' toast--error' : '');
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');
        el.textContent = message;
        stack.appendChild(el);
        setTimeout(function () {
            el.classList.add('is-leaving');
            setTimeout(function () { el.remove(); }, 300);
        }, 3200);
    }

    function setCount(selector, count) {
        $$(selector).forEach(function (el) {
            el.textContent = count;
            el.hidden = !count;
        });
    }

    function busy(btn, on) {
        if (!btn) return;
        btn.classList.toggle('is-loading', on);
        btn.disabled = on;
    }

    /* ---------- drawers ---------- */

    var lastFocus = null;

    function openDrawer(name) {
        var drawer = $('[data-drawer="' + name + '"]');
        if (!drawer) return;
        lastFocus = document.activeElement;
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('is-locked');
        var focusable = $('.drawer__panel button, .drawer__panel a, .drawer__panel input', drawer);
        if (focusable) setTimeout(function () { focusable.focus({ preventScroll: true }); }, 50);
        if (name === 'cart') loadCart();
    }

    function closeDrawers() {
        $$('.drawer.is-open').forEach(function (drawer) {
            drawer.classList.remove('is-open');
            drawer.setAttribute('aria-hidden', 'true');
        });
        document.body.classList.remove('is-locked');
        if (lastFocus) lastFocus.focus({ preventScroll: true });
    }

    document.addEventListener('click', function (e) {
        var opener = e.target.closest('[data-drawer-open]');
        if (opener) {
            var name = opener.getAttribute('data-drawer-open');
            // Bag icon on the cart page itself just links there.
            if (name === 'cart' && document.body.classList.contains('page-cart')) return;
            if (name === 'filters' && desktop.matches) return;
            e.preventDefault();
            openDrawer(name);
            return;
        }
        if (e.target.closest('[data-drawer-close]')) {
            e.preventDefault();
            closeDrawers();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDrawers();
            closeSearch();
        }
    });

    /* ---------- cart ---------- */

    var cartPanel = $('[data-cart-drawer]');
    var cartFresh = false;

    function renderCart(data) {
        if (cartPanel && data.html) {
            cartPanel.innerHTML = data.html;
            cartFresh = true;
        }
        if (typeof data.count !== 'undefined') setCount('[data-cart-count]', data.count);
    }

    function loadCart() {
        if (!cartPanel || cartFresh) return;
        fetch(cartPanel.getAttribute('data-url'), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (res) { return res.json(); })
            .then(renderCart)
            .catch(function () { window.location.href = $('[data-drawer-open="cart"]').href; });
    }

    // Add to bag (product page form and "Quick add" on cards).
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('[data-add-to-cart]');
        if (!form) return;
        e.preventDefault();

        var variantInput = $('[data-variant-input]', form);
        if (variantInput && !variantInput.value) {
            toast('Please choose a ' + (form.getAttribute('data-missing') || 'size') + '.', 'error');
            return;
        }

        var btn = $('[type="submit"]', form);
        busy(btn, true);
        request(D.routes.cartAdd, new FormData(form))
            .then(function (data) {
                renderCart(data);
                openDrawer('cart');
            })
            .catch(function (err) {
                if (err.data && err.data.html) renderCart(err.data);
                toast(err.message, 'error');
            })
            .finally(function () { busy(btn, false); });
    });

    // Quantity / remove inside the drawer — the cart page uses plain posts.
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('[data-cart-line-form]');
        if (!form || !form.closest('[data-cart-drawer]')) return;
        e.preventDefault();

        var body = new FormData(form);
        if (e.submitter && e.submitter.name) body.append(e.submitter.name, e.submitter.value);
        var line = form.closest('[data-line]');
        if (line) line.classList.add('is-updating');

        request(form.action, body)
            .then(renderCart)
            .catch(function (err) {
                if (err.data && err.data.html) renderCart(err.data);
                toast(err.message, 'error');
            });
    });

    /* ---------- wishlist ---------- */

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-wishlist]');
        if (!btn) return;
        e.preventDefault();

        var id = btn.getAttribute('data-wishlist');
        var body = new FormData();
        body.append('product_id', id);

        request(D.routes.wishlistToggle, body)
            .then(function (data) {
                $$('[data-wishlist="' + id + '"]').forEach(function (el) {
                    el.classList.toggle('is-active', data.added);
                    el.setAttribute('aria-pressed', data.added ? 'true' : 'false');
                });
                setCount('[data-wishlist-count]', data.count);
                toast(data.message);
            })
            .catch(function (err) { toast(err.message, 'error'); });
    });

    /* ---------- header + search ---------- */

    var header = $('[data-header]');
    if (header) {
        var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 4); };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    var searchPanel = $('[data-search-panel]');
    var searchInput = $('[data-search-input]');
    var searchResults = $('[data-search-results]');
    var searchTimer = null;
    var searchSeq = 0;

    function closeSearch() {
        if (searchPanel && !searchPanel.hidden) searchPanel.hidden = true;
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-search-open]') && searchPanel) {
            searchPanel.hidden = !searchPanel.hidden;
            if (!searchPanel.hidden) searchInput.focus();
        } else if (e.target.closest('[data-search-close]')) {
            closeSearch();
        } else if (searchPanel && !searchPanel.hidden && !e.target.closest('[data-search-panel]')) {
            closeSearch();
        }
    });

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            var q = searchInput.value.trim();
            if (q.length < 2) { searchResults.innerHTML = ''; return; }

            searchTimer = setTimeout(function () {
                var seq = ++searchSeq;
                fetch(searchInput.getAttribute('data-suggest-url') + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (seq !== searchSeq) return;
                        if (!data.products || !data.products.length) {
                            searchResults.innerHTML = '<p class="suggest__empty">No products found for “' + escapeHtml(q) + '”.</p>';
                            return;
                        }
                        searchResults.innerHTML = '<div class="suggest">' + data.products.map(function (p) {
                            return '<a class="suggest__item" href="' + escapeHtml(p.url) + '">'
                                + (p.image ? '<img src="' + escapeHtml(p.image) + '" alt="">' : '')
                                + '<div><strong>' + escapeHtml(p.name) + '</strong><span>' + escapeHtml(p.price) + '</span></div></a>';
                        }).join('') + '</div><a class="link-arrow suggest__all" href="' + escapeHtml(data.all_url) + '">See all results</a>';
                    })
                    .catch(function () {});
            }, 220);
        });
    }

    /* ---------- hero slider ---------- */

    $$('[data-slider]').forEach(function (slider) {
        var slides = $$('[data-slide]', slider);
        var dots = $$('[data-slide-to]', slider);
        if (slides.length < 2) return;
        var index = 0;
        var timer = null;
        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function go(i) {
            index = (i + slides.length) % slides.length;
            slides.forEach(function (s, n) { s.classList.toggle('is-active', n === index); });
            dots.forEach(function (d, n) { d.classList.toggle('is-active', n === index); });
        }
        function play() {
            if (reduced) return;
            clearInterval(timer);
            timer = setInterval(function () { if (!document.hidden) go(index + 1); }, 6000);
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () { go(+dot.getAttribute('data-slide-to')); play(); });
        });

        var startX = null;
        slider.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
        slider.addEventListener('touchend', function (e) {
            if (startX === null) return;
            var dx = e.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 40) { go(index + (dx < 0 ? 1 : -1)); play(); }
            startX = null;
        });

        play();
    });

    /* ---------- product page ---------- */

    var pdp = $('[data-product]');
    if (pdp) {
        var data = JSON.parse(pdp.getAttribute('data-product'));
        var form = $('[data-pdp-form]', pdp);
        var variantInput = $('[data-variant-input]', form);
        var addBtn = $('[data-add-button]', form);
        var stockEl = $('[data-stock]', form);
        var priceEl = $('[data-price]', pdp);
        var qtyInput = $('input[name="qty"]', form);
        var mainImage = $('[data-gallery-main]', pdp);
        var originalImage = mainImage ? mainImage.getAttribute('src') : null;
        var fieldsets = $$('[data-option]', form);

        if (fieldsets.length) form.setAttribute('data-missing', fieldsets.map(function (f) { return f.getAttribute('data-option').toLowerCase(); }).join(' and '));

        function selection() {
            var picked = {};
            fieldsets.forEach(function (fs) {
                var checked = $('input:checked', fs);
                if (checked) picked[fs.getAttribute('data-option')] = checked.value;
            });
            return picked;
        }

        function matches(variant, picked, ignore) {
            return Object.keys(picked).every(function (name) {
                return name === ignore || variant.options[name] === picked[name];
            });
        }

        function update(fromUser) {
            var picked = selection();

            // Mark values that can't be bought with the rest of the selection.
            fieldsets.forEach(function (fs) {
                var name = fs.getAttribute('data-option');
                var label = $('[data-option-label]', fs);
                if (label) label.textContent = picked[name] || '';
                $$('input', fs).forEach(function (input) {
                    var probe = Object.assign({}, picked);
                    probe[name] = input.value;
                    var available = data.variants.some(function (v) { return matches(v, probe) && v.stock > 0; });
                    input.closest('label').classList.toggle('is-unavailable', !available);
                });
            });

            var complete = Object.keys(picked).length === data.options.length;
            var variant = complete ? data.variants.find(function (v) { return matches(v, picked); }) : null;

            variantInput.value = variant ? variant.id : '';

            if (!variant) {
                addBtn.disabled = true;
                addBtn.textContent = complete ? 'Unavailable' : 'Select ' + form.getAttribute('data-missing');
                stockEl.className = 'pdp__stock is-out';
                stockEl.textContent = complete ? 'This combination isn’t available' : '';
                return;
            }

            priceEl.innerHTML = '<span class="price' + (variant.compare_at ? ' price--sale' : '') + '"><span class="price__current">' + escapeHtml(variant.price) + '</span>'
                + (variant.compare_at ? '<s class="price__compare">' + escapeHtml(variant.compare_at) + '</s>' : '') + '</span>';

            var out = variant.stock <= 0;
            addBtn.disabled = out;
            addBtn.textContent = out ? 'Sold out' : 'Add to bag';
            stockEl.className = 'pdp__stock' + (out ? ' is-out' : variant.stock <= 5 ? ' is-low' : '');
            stockEl.textContent = out ? 'Sold out in this option' : variant.stock <= 5 ? 'Only ' + variant.stock + ' left' : 'In stock — ready to ship';

            if (qtyInput) {
                qtyInput.max = Math.max(1, variant.stock);
                if (+qtyInput.value > variant.stock && variant.stock > 0) qtyInput.value = variant.stock;
            }

            if (mainImage) {
                var src = variant.image || originalImage;
                if (mainImage.getAttribute('src') !== src) mainImage.src = src;
            }

            if (fromUser && window.history.replaceState) {
                var url = new URL(window.location.href);
                url.searchParams.set('variant', variant.id);
                window.history.replaceState(null, '', url);
            }
        }

        form.addEventListener('change', function (e) {
            if (e.target.matches('[data-option] input')) update(true);
        });
        if (fieldsets.length) update(false);
    }

    // Quantity steppers
    document.addEventListener('click', function (e) {
        var step = e.target.closest('[data-qty-step]');
        if (!step) return;
        var input = $('input', step.closest('[data-qty]'));
        var next = (parseInt(input.value, 10) || 1) + parseInt(step.getAttribute('data-qty-step'), 10);
        var max = parseInt(input.max, 10) || 99;
        input.value = Math.min(max, Math.max(1, next));
    });

    // Gallery dots follow the mobile swipe position
    $$('[data-gallery]').forEach(function (gallery) {
        var track = $('[data-gallery-track]', gallery);
        var dots = $$('.gallery__dots span', gallery);
        if (!dots.length) return;
        track.addEventListener('scroll', function () {
            var i = Math.round(track.scrollLeft / track.clientWidth);
            dots.forEach(function (d, n) { d.classList.toggle('is-active', n === i); });
        }, { passive: true });
    });

    // Dialogs (size guide)
    document.addEventListener('click', function (e) {
        var opener = e.target.closest('[data-dialog-open]');
        if (opener) {
            var dialog = $('[data-dialog="' + opener.getAttribute('data-dialog-open') + '"]');
            if (dialog && dialog.showModal) { e.preventDefault(); dialog.showModal(); }
            else window.location.href = '/pages/size-guide';
            return;
        }
        if (e.target.closest('[data-dialog-close]')) {
            e.target.closest('dialog').close();
            return;
        }
        if (e.target.tagName === 'DIALOG') e.target.close();
    });

    /* ---------- listing filters ---------- */

    $$('[data-autosubmit]').forEach(function (el) {
        el.addEventListener('change', function () { el.form.submit(); });
    });

    var filterForm = $('[data-filter-form]');
    if (filterForm) {
        filterForm.addEventListener('change', function (e) {
            // Desktop sidebar applies instantly; the mobile drawer waits for "Apply".
            if (desktop.matches && e.target.type === 'checkbox') filterForm.submit();
        });
    }

    /* ---------- checkout ---------- */

    $$('form').forEach(function (f) {
        var once = $('[data-submit-once]', f);
        if (!once) return;
        f.addEventListener('submit', function () {
            setTimeout(function () { busy(once, true); }, 0);
        });
    });

    var agentInput = $('[data-agent-code]');
    if (agentInput && D.routes.validateAgent) {
        var agentMsg = $('[data-agent-message]');
        agentInput.addEventListener('blur', function () {
            var code = agentInput.value.trim();
            agentMsg.className = 'field__hint';
            if (!code) { agentMsg.textContent = ''; return; }
            var body = new FormData();
            body.append('code', code);
            request(D.routes.validateAgent, body)
                .then(function (data) { agentMsg.textContent = data.message; agentMsg.classList.add('is-valid'); })
                .catch(function (err) { agentMsg.textContent = err.message; agentMsg.classList.add('is-invalid'); });
        });
    }
})();
