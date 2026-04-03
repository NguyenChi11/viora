(function () {
  "use strict";

  var wrap = document.querySelector(".header-cart-wrap");
  if (!wrap) return;

  var dropdown = wrap.querySelector(".header-cart-dropdown");
  var hideTimer = null;
  var qtyTimers = {};

  function show() {
    clearTimeout(hideTimer);
    if (dropdown) dropdown.classList.add("header-cart-dropdown--visible");
  }

  function hide() {
    hideTimer = setTimeout(function () {
      if (dropdown) dropdown.classList.remove("header-cart-dropdown--visible");
    }, 200);
  }

  wrap.addEventListener("mouseenter", show);
  wrap.addEventListener("mouseleave", hide);

  // ---- Close button ----
  function bindCloseBtn() {
    var closeBtn = dropdown && dropdown.querySelector(".hcd__close");
    if (!closeBtn) return;
    closeBtn.addEventListener("click", function () {
      dropdown.classList.remove("header-cart-dropdown--visible");
    });
  }

  // ---- Recalculate & update dropdown total ----
  function updateDropdownTotal() {
    var totalEl = dropdown && dropdown.querySelector(".hcd__total-value");
    if (!totalEl) return;
    var sum = 0;
    dropdown.querySelectorAll(".hcd__qty").forEach(function (qtyEl) {
      var valEl = qtyEl.querySelector(".hcd__qty-val");
      var qty = valEl ? parseInt(valEl.textContent, 10) || 0 : 0;
      var price = parseFloat(qtyEl.dataset.price) || 0;
      sum += qty * price;
    });
    totalEl.textContent = "$" + sum.toFixed(1);
  }

  // ---- Update price display locally ----
  function updatePriceDisplay(qtyEl) {
    var valEl = qtyEl.querySelector(".hcd__qty-val");
    if (!valEl) return;
    var val = parseInt(valEl.textContent, 10) || 0;
    var unitPrice = parseFloat(qtyEl.dataset.price) || 0;
    var priceEl =
      qtyEl.closest(".hcd__item-bottom") &&
      qtyEl.closest(".hcd__item-bottom").querySelector(".hcd__item-price");
    if (priceEl && unitPrice) {
      var unitLabel = qtyEl.dataset.unitLabel || "";
      var total = (unitPrice * val).toFixed(1);
      priceEl.innerHTML =
        "$" +
        total +
        (unitLabel
          ? ' <span class="hcd__item-unit-label">/' + unitLabel + "</span>"
          : "");
    }
    updateDropdownTotal();
  }

  // ---- AJAX: update single item qty ----
  function doUpdateCartQty(cartKey, qty, onSuccess) {
    if (!window.buildproCart || !window.buildproCart.ajaxUrl) {
      return; // buildproCart not available — keep UI as-is, no reload
    }
    var fd = new FormData();
    fd.append("action", "buildpro_update_cart_qty");
    fd.append("nonce", window.buildproCart.nonce);
    fd.append("cart_key", cartKey);
    fd.append("qty", qty);
    fetch(window.buildproCart.ajaxUrl, {
      method: "POST",
      body: fd,
      credentials: "same-origin",
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data && data.data && data.data.nonce) {
          window.buildproCart.nonce = data.data.nonce;
        }
        if (data && data.success && data.data) {
          if (dropdown && typeof data.data.html === "string") {
            dropdown.innerHTML = data.data.html;
            bindCloseBtn();
            bindQtySteppers();
            bindDeleteBtns();
          }
          if (typeof window.buildproUpdateCartBadge === "function") {
            window.buildproUpdateCartBadge(data.data.count);
          }
          if (typeof onSuccess === "function") onSuccess();
        }
        // On server error: keep UI as-is, cart session is still intact
      })
      .catch(function () {
        // Network error: keep UI as-is, no reload
      });
  }

  // ---- Delete item via AJAX ----
  function doRemoveCartItem(cartKey, removeUrl, itemEl) {
    if (!window.buildproCart || !window.buildproCart.ajaxUrl) {
      // Fallback: navigate to WC remove URL directly
      window.location.href = removeUrl;
      return;
    }
    if (itemEl) itemEl.classList.add("hcd__item--removing");

    // Use WC ajax remove endpoint via POST for reliability
    var fd = new FormData();
    fd.append("action", "buildpro_remove_cart_item");
    fd.append("nonce", window.buildproCart.nonce);
    fd.append("cart_key", cartKey);

    fetch(window.buildproCart.ajaxUrl, {
      method: "POST",
      body: fd,
      credentials: "same-origin",
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data && data.data && data.data.nonce) {
          window.buildproCart.nonce = data.data.nonce;
        }
        if (data && data.success && data.data) {
          if (dropdown && typeof data.data.html === "string") {
            dropdown.innerHTML = data.data.html;
            bindCloseBtn();
            bindQtySteppers();
            bindDeleteBtns();
          }
          if (typeof window.buildproUpdateCartBadge === "function") {
            window.buildproUpdateCartBadge(data.data.count);
          }
        } else {
          // Remove from DOM optimistically if AJAX fails
          if (itemEl) itemEl.remove();
          updateDropdownTotal();
        }
      })
      .catch(function () {
        if (itemEl) itemEl.remove();
        updateDropdownTotal();
      });
  }

  // ---- Delete buttons ----
  function bindDeleteBtns() {
    if (!dropdown) return;
    dropdown.querySelectorAll(".hcd__item-delete").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var cartKey = btn.dataset.cartKey;
        var removeUrl = btn.dataset.removeUrl;
        if (!cartKey || !removeUrl) return;
        var itemEl = btn.closest(".hcd__item");
        doRemoveCartItem(cartKey, removeUrl, itemEl);
      });
    });
  }

  // ---- Qty steppers ----
  function bindQtySteppers() {
    if (!dropdown) return;
    dropdown.querySelectorAll(".hcd__qty").forEach(function (qtyEl) {
      var cartKey = qtyEl.dataset.cartKey;
      var valEl = qtyEl.querySelector(".hcd__qty-val");
      var minus = qtyEl.querySelector(".hcd__qty-minus");
      var plus = qtyEl.querySelector(".hcd__qty-plus");
      if (!valEl || !minus || !plus) return;

      var currentQty = parseInt(valEl.textContent, 10) || 1;

      minus.addEventListener("click", function () {
        if (currentQty > 1) {
          currentQty--;
          valEl.textContent = currentQty;
          updatePriceDisplay(qtyEl);
          clearTimeout(qtyTimers[cartKey]);
          qtyTimers[cartKey] = setTimeout(function () {
            doUpdateCartQty(cartKey, currentQty);
          }, 400);
        }
      });

      plus.addEventListener("click", function () {
        currentQty++;
        valEl.textContent = currentQty;
        updatePriceDisplay(qtyEl);
        clearTimeout(qtyTimers[cartKey]);
        qtyTimers[cartKey] = setTimeout(function () {
          doUpdateCartQty(cartKey, currentQty);
        }, 400);
      });
    });
  }

  // ---- Refresh mini cart inner HTML via AJAX ----
  function refreshMiniCart(showAfter) {
    if (!window.buildproCart || !window.buildproCart.ajaxUrl) return;
    if (dropdown) dropdown.classList.add("header-cart-dropdown--loading");

    var fd = new FormData();
    fd.append("action", "buildpro_mini_cart");
    fd.append("nonce", window.buildproCart.nonce);

    fetch(window.buildproCart.ajaxUrl, {
      method: "POST",
      body: fd,
      credentials: "same-origin",
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (dropdown)
          dropdown.classList.remove("header-cart-dropdown--loading");
        if (data && data.data && data.data.nonce) {
          window.buildproCart.nonce = data.data.nonce;
        }
        if (data && data.success && data.data) {
          if (dropdown && typeof data.data.html === "string") {
            dropdown.innerHTML = data.data.html;
            bindCloseBtn();
            bindQtySteppers();
            bindDeleteBtns();
          }
          if (
            typeof data.data.count !== "undefined" &&
            typeof window.buildproUpdateCartBadge === "function"
          ) {
            window.buildproUpdateCartBadge(data.data.count);
          }
        }
        if (showAfter) show();
      })
      .catch(function () {
        if (dropdown)
          dropdown.classList.remove("header-cart-dropdown--loading");
        if (showAfter) show();
      });
  }

  // Initial bindings
  bindCloseBtn();
  bindQtySteppers();
  bindDeleteBtns();

  // Expose globally for cart.js to call after add_to_cart
  window.buildproRefreshMiniCart = refreshMiniCart;
})();
