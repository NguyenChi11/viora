(function () {
  var initializedRoots = new WeakSet();

  function parseJson(value, fallback) {
    try {
      var decoded = JSON.parse(value || "");
      return decoded && typeof decoded === "object" ? decoded : fallback;
    } catch (e) {
      return fallback;
    }
  }

  function cloneData(value, fallback) {
    try {
      return JSON.parse(JSON.stringify(value));
    } catch (e) {
      return fallback;
    }
  }

  function isObject(value) {
    return !!value && typeof value === "object" && !Array.isArray(value);
  }

  function deepMerge(base, patch) {
    if (Array.isArray(patch)) {
      return patch.slice();
    }

    if (!isObject(patch)) {
      return patch;
    }

    var output = isObject(base) ? cloneData(base, {}) : {};
    Object.keys(patch).forEach(function (key) {
      var patchValue = patch[key];
      if (Array.isArray(patchValue)) {
        output[key] = patchValue.slice();
        return;
      }

      if (isObject(patchValue)) {
        output[key] = deepMerge(output[key], patchValue);
        return;
      }

      output[key] = patchValue;
    });

    return output;
  }

  function getByPath(obj, path) {
    var parts = String(path || "").split(".");
    var current = obj;
    for (var i = 0; i < parts.length; i += 1) {
      if (!current || typeof current !== "object") {
        return "";
      }
      current = current[parts[i]];
    }
    return typeof current === "undefined" ? "" : current;
  }

  function setByPath(obj, path, value) {
    var parts = String(path || "").split(".");
    var current = obj;
    for (var i = 0; i < parts.length - 1; i += 1) {
      var key = parts[i];
      if (!current[key] || typeof current[key] !== "object") {
        var next = parts[i + 1];
        current[key] = /^\d+$/.test(next) ? [] : {};
      }
      current = current[key];
    }
    current[parts[parts.length - 1]] = value;
  }

  function escapeHtml(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function getDefaultCard() {
    return {
      quote: "",
      name: "",
      role: "",
      avatar_id: 0,
      avatar_url: "",
      avatar: "",
    };
  }

  function hasTestimonialsData(candidate) {
    if (!candidate || typeof candidate !== "object") {
      return false;
    }

    var testimonials = Array.isArray(candidate.testimonials)
      ? candidate.testimonials
      : [];

    for (var i = 0; i < testimonials.length; i += 1) {
      var item = testimonials[i];
      if (!item || typeof item !== "object") {
        continue;
      }

      var quote = typeof item.quote === "string" ? item.quote.trim() : "";
      var name = typeof item.name === "string" ? item.name.trim() : "";
      var role = typeof item.role === "string" ? item.role.trim() : "";
      var avatar =
        typeof item.avatar_url === "string" && item.avatar_url.trim() !== ""
          ? item.avatar_url.trim()
          : typeof item.avatar === "string" && item.avatar.trim() !== ""
            ? item.avatar.trim()
            : "";
      var avatarId = Number(item.avatar_id || 0);

      if (
        quote !== "" ||
        name !== "" ||
        role !== "" ||
        avatar !== "" ||
        avatarId > 0
      ) {
        return true;
      }
    }

    return false;
  }

  function init(root) {
    if (!root || initializedRoots.has(root)) {
      return false;
    }

    var hidden = root.querySelector("#viora-home-client-data");
    var listEl = root.querySelector("[data-client-cards-list]");
    var addCardBtn = root.querySelector(".viora-add-client-card");
    var cardTemplate = root.querySelector("#viora-client-card-template");
    if (!hidden || !listEl || !addCardBtn || !cardTemplate) {
      return false;
    }

    var initialHidden = root.querySelector("#viora-home-client-initial-data");
    var initialData = parseJson(initialHidden ? initialHidden.value : "", {});

    initializedRoots.add(root);

    var setting = null;
    if (window.wp && wp.customize && typeof wp.customize === "function") {
      try {
        setting = wp.customize("viora_home_client_data");
      } catch (e) {
        setting = null;
      }
    }

    var settingValue =
      setting && typeof setting.get === "function" ? setting.get() : null;
    var data = {};
    var settingData = null;
    if (settingValue && typeof settingValue === "object") {
      settingData = cloneData(settingValue, {});
    } else if (typeof settingValue === "string" && settingValue !== "") {
      settingData = parseJson(settingValue, null);
    }

    var hiddenData = parseJson(hidden.value, {});
    var candidates = [settingData, initialData, hiddenData];

    for (var ci = 0; ci < candidates.length; ci += 1) {
      if (hasTestimonialsData(candidates[ci])) {
        data = cloneData(candidates[ci], {});
        break;
      }
    }

    if (!data || typeof data !== "object" || Object.keys(data).length === 0) {
      for (var cj = 0; cj < candidates.length; cj += 1) {
        if (
          candidates[cj] &&
          typeof candidates[cj] === "object" &&
          Object.keys(candidates[cj]).length > 0
        ) {
          data = cloneData(candidates[cj], {});
          break;
        }
      }
    }

    if (!data || typeof data !== "object") {
      data = {};
    }

    if (!isObject(data.heading)) {
      data.heading = {};
    }
    if (!Array.isArray(data.testimonials)) {
      data.testimonials = [];
    }

    var i18n =
      window.vioraHomeI18n && typeof window.vioraHomeI18n === "object"
        ? window.vioraHomeI18n
        : {};
    var helpHints =
      i18n.helpHints && typeof i18n.helpHints === "object"
        ? i18n.helpHints
        : {};
    var helpToggle = root.querySelector(".viora-home-client-help-toggle");
    var helpStorageKey = "viora_home_client_help_mode";
    var mediaFrame;
    var syncDelay = 120;
    var syncTimer = null;
    var lastLocalSettingJson = "";
    var expandedCards = {};

    function clearSyncTimer() {
      if (!syncTimer) {
        return;
      }
      window.clearTimeout(syncTimer);
      syncTimer = null;
    }

    function syncSettingNow() {
      if (!setting || typeof setting.set !== "function") {
        return;
      }

      var base = setting.get();
      var nextData;
      if (base && typeof base === "object") {
        nextData = deepMerge(cloneData(base, {}), cloneData(data, {}));
      } else {
        nextData = cloneData(data, {});
      }

      data = nextData;
      lastLocalSettingJson = JSON.stringify(nextData || {});
      setting.set(nextData);
    }

    function scheduleSyncSetting() {
      clearSyncTimer();
      syncTimer = window.setTimeout(function () {
        syncTimer = null;
        syncSettingNow();
      }, syncDelay);
    }

    function flushSyncSetting() {
      clearSyncTimer();
      syncSettingNow();
    }

    function sendLivePreviewPatch() {
      if (
        !window.wp ||
        !wp.customize ||
        !wp.customize.previewer ||
        typeof wp.customize.previewer.send !== "function"
      ) {
        return;
      }

      wp.customize.previewer.send(
        "viora_home_client_live_data",
        cloneData(data, {}),
      );
    }

    function getHintForPath(path) {
      if (!path) {
        return "";
      }
      var hint = helpHints[path];
      return typeof hint === "string" ? hint : "";
    }

    function setHelpPlaceholder(field, enabled) {
      if (!field) {
        return;
      }

      var hintPath =
        field.getAttribute("data-help-path") || field.getAttribute("data-path");
      var hint = enabled ? getHintForPath(hintPath) : "";
      if (hint !== "") {
        field.setAttribute("placeholder", hint);
      } else {
        field.removeAttribute("placeholder");
      }
    }

    function applyHelpMode(enabled) {
      var fields = root.querySelectorAll(".viora-client-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        setHelpPlaceholder(field, enabled);
      });
    }

    function readHelpMode() {
      try {
        if (!window.localStorage) {
          return false;
        }
        return window.localStorage.getItem(helpStorageKey) === "1";
      } catch (e) {
        return false;
      }
    }

    function saveHelpMode(enabled) {
      try {
        if (!window.localStorage) {
          return;
        }
        window.localStorage.setItem(helpStorageKey, enabled ? "1" : "0");
      } catch (e) {}
    }

    function writeData(options) {
      options = options || {};
      hidden.value = JSON.stringify(data || {});

      if (options.immediateSync === true) {
        flushSyncSetting();
      } else {
        scheduleSyncSetting();
      }
    }

    function updateMediaPreview(group) {
      var idPath = group.getAttribute("data-id-path");
      var urlPath = group.getAttribute("data-url-path");
      var fallbackPath = group.getAttribute("data-fallback-path");
      var url = getByPath(data, urlPath) || getByPath(data, fallbackPath) || "";
      var preview = group.querySelector(".viora-media-preview");
      if (!preview) {
        return;
      }

      if (url) {
        preview.innerHTML = "<img src='" + escapeHtml(url) + "' alt='' />";
      } else {
        preview.textContent = "";
      }

      var idField = group.querySelector(".viora-media-id-field");
      if (idField && idPath) {
        var idValue = getByPath(data, idPath);
        idField.value = idValue ? String(idValue) : "";
      }
    }

    function createCardMarkup(index) {
      return cardTemplate.innerHTML.replace(/__INDEX__/g, String(index));
    }

    function isCardExpanded(index) {
      if (Object.prototype.hasOwnProperty.call(expandedCards, index)) {
        return expandedCards[index] === true;
      }

      return index === 0;
    }

    function setCardExpanded(card, index, expanded) {
      var body = card.querySelector(".viora-client-card__body");
      var toggleBtn = card.querySelector(".viora-toggle-client-card");
      if (body) {
        body.hidden = !expanded;
      }
      card.classList.toggle("is-collapsed", !expanded);
      if (toggleBtn) {
        toggleBtn.setAttribute("aria-expanded", expanded ? "true" : "false");
      }
      expandedCards[index] = expanded;
    }

    function collapseOtherCards(activeIndex) {
      var cards = listEl.querySelectorAll(".viora-client-card");
      Array.prototype.forEach.call(cards, function (card, index) {
        if (index === activeIndex) {
          return;
        }
        setCardExpanded(card, index, false);
      });
    }

    function shiftExpandedCardsAfterRemove(removedIndex) {
      var nextState = {};
      Object.keys(expandedCards).forEach(function (key) {
        var idx = parseInt(key, 10);
        if (idx < removedIndex) {
          nextState[idx] = expandedCards[key];
          return;
        }
        if (idx > removedIndex) {
          nextState[idx - 1] = expandedCards[key];
        }
      });
      expandedCards = nextState;
    }

    function hydrateSimpleFields() {
      var fields = root.querySelectorAll(".viora-client-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        var path = field.getAttribute("data-path");
        var value = getByPath(data, path);
        field.value = typeof value === "string" ? value : value || "";
      });
    }

    function renderCards() {
      if (!Array.isArray(data.testimonials)) {
        data.testimonials = [];
      }

      if (data.testimonials.length === 0) {
        data.testimonials.push(getDefaultCard());
      }

      var html = "";
      for (var i = 0; i < data.testimonials.length; i += 1) {
        html += createCardMarkup(i);
      }
      listEl.innerHTML = html;

      var cards = listEl.querySelectorAll(".viora-client-card");
      Array.prototype.forEach.call(cards, function (card, index) {
        var item = data.testimonials[index] || getDefaultCard();

        var titleEl = card.querySelector(".viora-client-card__title");
        if (titleEl) {
          var label = "Testimonial " + String(index + 1);
          if (typeof item.name === "string" && item.name.trim() !== "") {
            label += ": " + item.name.trim();
          }
          titleEl.textContent = label;
        }

        var quoteField = card.querySelector(
          '.viora-client-field[data-path="testimonials.' +
            String(index) +
            '.quote"]',
        );
        if (quoteField) {
          quoteField.value = typeof item.quote === "string" ? item.quote : "";
        }

        var nameField = card.querySelector(
          '.viora-client-field[data-path="testimonials.' +
            String(index) +
            '.name"]',
        );
        if (nameField) {
          nameField.value = typeof item.name === "string" ? item.name : "";
        }

        var roleField = card.querySelector(
          '.viora-client-field[data-path="testimonials.' +
            String(index) +
            '.role"]',
        );
        if (roleField) {
          roleField.value = typeof item.role === "string" ? item.role : "";
        }

        var removeBtn = card.querySelector(".viora-remove-client-card");
        if (removeBtn) {
          removeBtn.setAttribute("data-card-index", String(index));
          removeBtn.disabled = data.testimonials.length <= 1;
        }

        var toggleBtn = card.querySelector(".viora-toggle-client-card");
        if (toggleBtn) {
          toggleBtn.setAttribute("data-card-index", String(index));
        }

        setCardExpanded(card, index, isCardExpanded(index));
      });

      var mediaGroups = root.querySelectorAll(".viora-media-field");
      Array.prototype.forEach.call(mediaGroups, function (group) {
        updateMediaPreview(group);
      });
    }

    root.addEventListener("input", function (event) {
      var target = event.target;
      if (!target) {
        return;
      }

      if (target.matches(".viora-client-field[data-path]")) {
        var path = target.getAttribute("data-path");
        setByPath(data, path, target.value);
        writeData();
        sendLivePreviewPatch();
      }
    });

    root.addEventListener("change", function (event) {
      var target = event.target;
      if (!target) {
        return;
      }

      if (target.matches(".viora-client-field[data-path]")) {
        var path = target.getAttribute("data-path");
        setByPath(data, path, target.value);
        writeData({ immediateSync: true });
        sendLivePreviewPatch();
      }
    });

    root.addEventListener("click", function (event) {
      var target = event.target;
      if (!target) {
        return;
      }

      var toggleBtn = target.closest(".viora-toggle-client-card");
      if (toggleBtn) {
        event.preventDefault();
        var toggleIndex = parseInt(
          toggleBtn.getAttribute("data-card-index") || "-1",
          10,
        );
        if (toggleIndex >= 0) {
          var card = toggleBtn.closest(".viora-client-card");
          var nextExpanded = !isCardExpanded(toggleIndex);
          if (nextExpanded) {
            collapseOtherCards(toggleIndex);
          }
          if (card) {
            setCardExpanded(card, toggleIndex, nextExpanded);
          }
        }
        return;
      }

      if (target.closest(".viora-add-client-card")) {
        event.preventDefault();
        data.testimonials.push(getDefaultCard());
        expandedCards[data.testimonials.length - 1] = true;
        renderCards();
        collapseOtherCards(data.testimonials.length - 1);
        writeData({ immediateSync: true });
        sendLivePreviewPatch();
        applyHelpMode(helpToggle ? helpToggle.checked : false);
        return;
      }

      var removeBtn = target.closest(".viora-remove-client-card");
      if (removeBtn) {
        event.preventDefault();
        var cardIndex = parseInt(removeBtn.getAttribute("data-card-index"), 10);
        if (!isNaN(cardIndex) && data.testimonials[cardIndex]) {
          data.testimonials.splice(cardIndex, 1);
          if (data.testimonials.length === 0) {
            data.testimonials.push(getDefaultCard());
          }
          shiftExpandedCardsAfterRemove(cardIndex);
          renderCards();
          writeData({ immediateSync: true });
          sendLivePreviewPatch();
          applyHelpMode(helpToggle ? helpToggle.checked : false);
        }
        return;
      }

      var mediaSelect = target.closest(".viora-select-media");
      if (mediaSelect) {
        event.preventDefault();
        var mediaGroup = mediaSelect.closest(".viora-media-field");
        if (!mediaGroup || !window.wp || !wp.media) {
          return;
        }

        if (!mediaFrame) {
          mediaFrame = wp.media({
            title:
              i18n.selectImage ||
              (window.vioraHomeAdminI18n &&
                window.vioraHomeAdminI18n.selectImage) ||
              "Select image",
            button: {
              text:
                i18n.useImage ||
                (window.vioraHomeAdminI18n &&
                  window.vioraHomeAdminI18n.useImage) ||
                "Use image",
            },
            multiple: false,
          });
        }

        mediaFrame.off("select");
        mediaFrame.on("select", function () {
          var attachment = mediaFrame.state().get("selection").first().toJSON();
          var idPath = mediaGroup.getAttribute("data-id-path");
          var urlPath = mediaGroup.getAttribute("data-url-path");
          var fallbackPath = mediaGroup.getAttribute("data-fallback-path");
          setByPath(data, idPath, attachment.id || 0);
          setByPath(data, urlPath, attachment.url || "");
          if (fallbackPath) {
            setByPath(data, fallbackPath, attachment.url || "");
          }
          updateMediaPreview(mediaGroup);
          writeData({ immediateSync: true });
          sendLivePreviewPatch();
        });

        mediaFrame.open();
        return;
      }

      var mediaRemove = target.closest(".viora-remove-media");
      if (mediaRemove) {
        event.preventDefault();
        var group = mediaRemove.closest(".viora-media-field");
        if (!group) {
          return;
        }
        var removeIdPath = group.getAttribute("data-id-path");
        var removeUrlPath = group.getAttribute("data-url-path");
        var removeFallbackPath = group.getAttribute("data-fallback-path");
        setByPath(data, removeIdPath, 0);
        setByPath(data, removeUrlPath, "");
        if (removeFallbackPath) {
          setByPath(data, removeFallbackPath, "");
        }
        updateMediaPreview(group);
        writeData({ immediateSync: true });
        sendLivePreviewPatch();
      }
    });

    function applyExternalData(nextData) {
      if (!nextData || typeof nextData !== "object") {
        return;
      }

      data = cloneData(nextData, {});
      if (!isObject(data.heading)) {
        data.heading = {};
      }
      if (!Array.isArray(data.testimonials)) {
        data.testimonials = [];
      }

      hydrateSimpleFields();
      renderCards();
      writeData({ immediateSync: false });
      applyHelpMode(helpToggle ? helpToggle.checked : false);
    }

    if (setting && typeof setting.bind === "function") {
      setting.bind(function (nextValue) {
        var normalized = nextValue;
        if (typeof normalized === "string") {
          normalized = parseJson(normalized, null);
        }
        if (!normalized || typeof normalized !== "object") {
          return;
        }

        if (
          !hasTestimonialsData(normalized) &&
          hasTestimonialsData(data) &&
          hasTestimonialsData(initialData)
        ) {
          return;
        }

        var nextJson = JSON.stringify(normalized);
        if (nextJson === lastLocalSettingJson) {
          return;
        }

        applyExternalData(normalized);
      });
    }

    if (helpToggle) {
      var helpEnabled = readHelpMode();
      helpToggle.checked = helpEnabled;
      helpToggle.addEventListener("change", function () {
        var checked = !!helpToggle.checked;
        saveHelpMode(checked);
        applyHelpMode(checked);
      });
    }

    hydrateSimpleFields();
    renderCards();
    writeData({ immediateSync: false });
    applyHelpMode(helpToggle ? helpToggle.checked : false);

    return true;
  }

  function boot() {
    return init(document.getElementById("viora-home-client-customizer-root"));
  }

  function bootWithRetry() {
    if (boot()) {
      return;
    }

    var attempts = 0;
    var maxAttempts = 40;
    var timer = window.setInterval(function () {
      attempts += 1;
      if (boot() || attempts >= maxAttempts) {
        window.clearInterval(timer);
      }
    }, 250);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootWithRetry);
  } else {
    bootWithRetry();
  }
})();
