let cart = [];
let isPlacingOrder = false;

// Configuration will be passed from the Blade template via window.UserWebConfig
const orderStoreUrl = window.UserWebConfig?.orderStoreUrl || '';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

/* ─── CART LOGIC ─── */
window.addToCart = function(id, name, price, image) {
    const existing = cart.find(i => i.id === id);
    if (existing) { existing.qty++; }
    else { cart.push({ id, name, price: parseFloat(price), image, qty: 1 }); }
    updateCartUI(); updateDishAction(id); showToast(`${name} added to cart! 🛒`);
}

window.removeFromCart = function(id) { cart = cart.filter(i => i.id !== id); updateCartUI(); updateDishAction(id); }

window.changeQty = function(id, delta) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) cart = cart.filter(i => i.id !== id);
    updateCartUI(); updateDishAction(id);
}

function updateDishAction(id) {
    const item = cart.find(i => i.id === id);
    const el = document.getElementById('dish-action-' + id);
    if (!el) return;
    const card = el.closest('.dish-card');
    const name = card ? card.dataset.name : '';
    const price = card ? card.dataset.price : 0;
    const image = card ? card.dataset.image : '';
    if (!item || item.qty === 0) {
        el.innerHTML = `
            <button class="add-btn" onclick="addToCart(${id}, '${name}', ${price}, '${image}')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </button>`;
    } else {
        el.innerHTML = `
            <div class="dish-qty-controls">
                <button class="qty-btn" onclick="changeQty(${id}, -1)">−</button>
                <span class="qty-display">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty(${id}, 1)">+</button>
            </div>`;
    }
}

/* ─── CART UI ─── */
function updateCartUI() {
    const total = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const totalQty = cart.reduce((s, i) => s + i.qty, 0);
    const isEmpty = cart.length === 0;

    document.getElementById('cartNavCount').textContent = totalQty;
    document.getElementById('mobileCartCount').textContent = totalQty;
    document.getElementById('cartNavCount').classList.toggle('hidden', isEmpty);
    document.getElementById('mobileCartCount').classList.toggle('hidden', isEmpty);
    document.getElementById('cartItemCount').textContent = totalQty === 0 ? '0 items' : `${totalQty} item${totalQty > 1 ? 's' : ''}`;
    document.getElementById('cartTotal').textContent = '$' + total.toFixed(2);
    document.getElementById('checkoutBtn').disabled = isEmpty;

    const itemsEl = document.getElementById('cartItems');
    document.getElementById('cartEmpty').style.display = isEmpty ? 'flex' : 'none';
    itemsEl.querySelectorAll('.cart-item').forEach(e => e.remove());

    cart.forEach(item => {
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <img src="${item.image}" alt="${item.name}" class="cart-item-img">
            <div class="cart-item-info">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">$${(item.price * item.qty).toFixed(2)}</div>
            </div>
            <div class="cart-item-controls">
                <button class="cart-qty-btn" onclick="changeQty(${item.id}, -1)">−</button>
                <span class="cart-item-qty">${item.qty}</span>
                <button class="cart-qty-btn" onclick="changeQty(${item.id}, 1)">+</button>
                <button class="cart-item-remove" onclick="removeFromCart(${item.id})">🗑</button>
            </div>`;
        itemsEl.appendChild(div);
    });
}

/* ─── CART DRAWER ─── */
window.openCart = function() {
    document.getElementById('cartDrawer').classList.add('open');
    document.getElementById('cartOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
window.closeCart = function() {
    document.getElementById('cartDrawer').classList.remove('open');
    document.getElementById('cartOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

/* ─── CHECKOUT ─── */
window.openCheckout = function() {
    if (cart.length === 0) return;
    closeCart();
    const summary = document.getElementById('checkoutSummary');
    const total = cart.reduce((s, i) => s + i.price * i.qty, 0);
    let html = '';
    cart.forEach(item => {
        html += `<div class="checkout-order-row">
            <span class="checkout-order-name">${item.name}<span class="checkout-order-qty">×${item.qty}</span></span>
            <span class="checkout-order-price">$${(item.price * item.qty).toFixed(2)}</span>
        </div>`;
    });
    html += `<div class="checkout-order-row checkout-total-row">
        <span class="checkout-order-name">Total</span>
        <span class="checkout-order-price">$${total.toFixed(2)}</span>
    </div>`;
    summary.innerHTML = html;
    document.getElementById('checkoutOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
window.closeCheckout = function() {
    document.getElementById('checkoutOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

/* ─── PLACE ORDER ─── */
window.placeOrder = function() {
    if (isPlacingOrder || cart.length === 0) return;

    const tableNumber = document.getElementById('tableNumber').value.trim();
    const button = document.querySelector('.btn-place-order');
    const tableEl = document.getElementById('tableNumber');

    if (!tableNumber) {
        tableEl.style.borderColor = '#ef4444';
        tableEl.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.15)';
        tableEl.focus();
        showToast('⚠️ Please enter Name or Table', true);
        tableEl.addEventListener('input', () => { tableEl.style.borderColor = ''; tableEl.style.boxShadow = ''; }, { once: true });
        return;
    }

    const total = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const cartSnapshot = cart.map(item => ({ ...item }));

    isPlacingOrder = true;
    if (button) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = 'Sending...';
    }

    fetch(orderStoreUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            table_number: tableNumber,
            source: 'userweb',
            total,
            cart: cartSnapshot,
        }),
    })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Order failed. Please try again.');
            }
            return data;
        })
        .then(data => {
            document.getElementById('successMsg').textContent =
                `Order #${data.order_id} of $${total.toFixed(2)} for ${tableNumber} has been sent to the kitchen. Estimated ready time: ${data.tracking_window || '5-10 min'}.`;
            closeCheckout();
            cart = [];
            updateCartUI();
            cartSnapshot.forEach(item => updateDishAction(item.id));
            tableEl.value = '';
            setTimeout(() => {
                document.getElementById('successOverlay').classList.add('open');
                document.body.style.overflow = 'hidden';
            }, 200);
        })
        .catch(error => {
            showToast(error.message || 'Order failed. Please try again.', true);
        })
        .finally(() => {
            isPlacingOrder = false;
            if (button) {
                button.disabled = false;
                button.innerHTML = button.dataset.originalText || button.innerHTML;
            }
        });
}
window.closeSuccess = function() {
    document.getElementById('successOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

/* ─── TOAST ─── */
function showToast(msg, isError = false) {
    const toast = document.getElementById('toast');
    document.getElementById('toast-text').textContent = msg;
    toast.style.background = isError ? '#ef4444' : '#22c55e';
    toast.classList.add('show');
    clearTimeout(toast._t);
    toast._t = setTimeout(() => toast.classList.remove('show'), 3000);
}

/* ─── MOBILE NAV ─── */
const hamburger = document.getElementById('hamburger');
const mobileNav = document.getElementById('mobileNav');
const closeNavBtn = document.getElementById('closeNav');

window.openMobileNav = function() { mobileNav.classList.add('open'); hamburger.classList.add('open'); document.body.style.overflow = 'hidden'; }
window.closeMobileNav = function() { mobileNav.classList.remove('open'); hamburger.classList.remove('open'); document.body.style.overflow = ''; }
window.toggleMobileNav = function() { mobileNav.classList.contains('open') ? closeMobileNav() : openMobileNav(); }

if (hamburger) hamburger.addEventListener('click', window.toggleMobileNav);
if (closeNavBtn) closeNavBtn.addEventListener('click', window.closeMobileNav);

function checkMobileCart() {
    const mobileCartBtn = document.getElementById('mobileCartBtn');
    if (mobileCartBtn) {
        mobileCartBtn.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
    }
}
checkMobileCart();
window.addEventListener('resize', checkMobileCart);

/* ─── SCROLL REVEAL ─── */
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeCart(); closeCheckout(); closeSuccess(); closeMobileNav(); }
});

/* ─── MENU PAGINATION + FILTER ─── */
const PER_PAGE = 6;
let currentPage = 1;
let activeCatId = 'all';

function getFilteredCards() {
    return [...document.querySelectorAll('#menuGrid .dish-card')].filter(card => {
        return activeCatId === 'all' || card.dataset.category == activeCatId;
    });
}

function renderPage() {
    const cards = getFilteredCards();
    const total = cards.length;
    const pages = Math.ceil(total / PER_PAGE);
    const start = (currentPage - 1) * PER_PAGE;
    const end = start + PER_PAGE;

    document.querySelectorAll('#menuGrid .dish-card').forEach(card => card.style.display = 'none');
    cards.forEach((card, i) => { card.style.display = (i >= start && i < end) ? '' : 'none'; });

    let noMsg = document.getElementById('noProductsMsg');
    if (total === 0) {
        if (!noMsg) {
            noMsg = document.createElement('div');
            noMsg.id = 'noProductsMsg'; noMsg.className = 'no-products';
            noMsg.innerHTML = `<span class="no-products-icon">🔍</span><p style="font-weight:600;">No dishes in this category yet</p>`;
            document.getElementById('menuGrid').appendChild(noMsg);
        }
        noMsg.style.display = '';
    } else if (noMsg) { noMsg.style.display = 'none'; }

    const pag = document.getElementById('menuPagination');
    if (!pag) return;
    if (pages <= 1) { pag.innerHTML = ''; return; }

    let html = `<button class="page-btn" onclick="goPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>‹</button>`;
    for (let p = 1; p <= pages; p++) {
        html += `<button class="page-btn ${p === currentPage ? 'active' : ''}" onclick="goPage(${p})">${p}</button>`;
    }
    html += `<button class="page-btn" onclick="goPage(${currentPage + 1})" ${currentPage === pages ? 'disabled' : ''}>›</button>`;
    html += `<span class="page-info">Showing ${start + 1}–${Math.min(end, total)} of ${total}</span>`;
    pag.innerHTML = html;
}

window.goPage = function(page) {
    const pages = Math.ceil(getFilteredCards().length / PER_PAGE);
    if (page < 1 || page > pages) return;
    currentPage = page; renderPage();
    const menuEl = document.getElementById('menu');
    if (menuEl) menuEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

window.filterMenu = function(btn, categoryId) {
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    activeCatId = categoryId; currentPage = 1; renderPage();
}

/* ─── Helper: filter by category id from external links ─── */
window.filterMenuById = function(catId) {
    const tab = document.querySelector(`.filter-tab[data-cat-id="${catId}"]`);
    window.filterMenu(tab, catId);
    setTimeout(() => {
        const menuEl = document.getElementById('menu');
        if (menuEl) menuEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

document.addEventListener('DOMContentLoaded', renderPage);
