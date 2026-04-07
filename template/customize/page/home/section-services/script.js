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
      icon_id: 0,
      iconImage_url: "",
      iconImage: "",
      title: "",
      description: "",
      features: [],
    };
  }

  function init(root) {
    if (!root || initializedRoots.has(root)) {
      return;
    }

    var hidden = root.querySelector("#viora-home-services-data");
    var listEl = root.querySelector("[data-services-cards-list]");
    var addCardBtn = root.querySelector(".viora-add-service-card");
    var cardTemplate = root.querySelector("#viora-services-card-template");
    if (!hidden) {
      return;
    }

    if (!listEl || !addCardBtn || !cardTemplate) {
      return;
    }

    var initialHidden = root.querySelector("#viora-home-services-initial-data");
    var initialData = parseJson(initialHidden ? initialHidden.value : "", {});

    initializedRoots.add(root);

    var setting = null;
    if (window.wp && wp.customize && typeof wp.customize === "function") {
      try {
        setting = wp.customize("viora_home_services_data");
      } catch (e) {
        setting = null;
      }
    }

    var settingValue =
      setting && typeof setting.get === "function" ? setting.get() : null;
    var data = {};

    if (settingValue && typeof settingValue === "object") {
      data = cloneData(settingValue, {});
    }

    if (!data || typeof data !== "object" || Object.keys(data).length === 0) {
      data = cloneData(initialData, {});
    }

    if (!data || typeof data !== "object" || Object.keys(data).length === 0) {
      data = parseJson(hidden.value, {});
    }

    if (
      (!data || typeof data !== "object" || Object.keys(data).length === 0) &&
      typeof settingValue === "string" &&
      settingValue !== ""
    ) {
      data = parseJson(settingValue, {});
    }

    if (!data || typeof data !== "object") {
      data = {};
    }

    if (!Array.isArray(data.items)) {
      data.items = [];
    }

    var i18n =
      window.vioraHomeI18n && typeof window.vioraHomeI18n === "object"
        ? window.vioraHomeI18n
        : {};
    var helpHints =
      i18n.helpHints && typeof i18n.helpHints === "object"
        ? i18n.helpHints
        : {};
    var helpToggle = root.querySelector(".viora-home-services-help-toggle");
    var helpStorageKey = "viora_home_services_help_mode";
    var mediaFrame;
    var syncDelay = 120;
    var syncTimer = null;
    var lastLocalSettingJson = "";
    var expandedCards = {};
    var expandedFeatures = {};

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
        "viora_home_services_live_data",
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

    function setHelpPlaceholder(field, path, enabled) {
      if (!field) {
        return;
      }

      var hint = enabled ? getHintForPath(path) : "";
      if (hint !== "") {
        field.setAttribute("placeholder", hint);
      } else {
        field.removeAttribute("placeholder");
      }
    }

    function applyHelpMode(enabled) {
      var fields = root.querySelectorAll(".viora-services-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        setHelpPlaceholder(field, field.getAttribute("data-path"), enabled);
      });

      var featureFields = root.querySelectorAll(
        ".viora-feature-input[data-help-path]",
      );
      Array.prototype.forEach.call(featureFields, function (field) {
        var hintPath = field.getAttribute("data-help-path") || "";
        setHelpPlaceholder(field, hintPath, enabled);
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
      var immediateSync = options.immediateSync === true;

      if (immediateSync) {
        flushSyncSetting();
      } else {
        scheduleSyncSetting();
      }

      hidden.value = JSON.stringify(data || {});
    }

    function createCardMarkup(index) {
      return cardTemplate.innerHTML.replace(/__INDEX__/g, String(index));
    }

    function normalizeFeatures(features) {
      if (!Array.isArray(features)) {
        return [];
      }

      return features.map(function (feature) {
        return String(feature || "");
      });
    }

    function createFeatureOptionMarkup(
      cardIndex,
      featureIndex,
      value,
      hintPath,
    ) {
      return (
        '<div class="viora-feature-option" data-feature-index="' +
        String(featureIndex) +
        '">' +
        '<input type="text" class="regular-text viora-feature-input" data-card-index="' +
        String(cardIndex) +
        '" data-feature-index="' +
        String(featureIndex) +
        '" data-help-path="' +
        escapeHtml(hintPath || "") +
        '" value="' +
        escapeHtml(value) +
        '">' +
        '<button type="button" class="button-link-delete viora-remove-feature-option" data-card-index="' +
        String(cardIndex) +
        '" data-feature-index="' +
        String(featureIndex) +
        '">Remove</button>' +
        "</div>"
      );
    }

    function isCardExpanded(index) {
      return expandedCards[index] !== false;
    }

    function setCardExpanded(card, index, expanded) {
      var body = card.querySelector(".viora-services-card__body");
      var toggleBtn = card.querySelector(".viora-toggle-service-card");
      if (body) {
        body.hidden = !expanded;
      }
      card.classList.toggle("is-collapsed", !expanded);
      if (toggleBtn) {
        toggleBtn.setAttribute("aria-expanded", expanded ? "true" : "false");
      }
      expandedCards[index] = expanded;
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

    function isFeaturesExpanded(index) {
      return expandedFeatures[index] === true;
    }

    function setFeaturesExpanded(control, index, expanded) {
      var body = control.querySelector(".viora-features-body");
      var toggleBtn = control.querySelector(".viora-toggle-features");
      if (body) {
        body.hidden = !expanded;
      }
      control.classList.toggle("is-open", expanded);
      if (toggleBtn) {
        toggleBtn.setAttribute("aria-expanded", expanded ? "true" : "false");
      }
      expandedFeatures[index] = expanded;
    }

    function shiftExpandedFeaturesAfterRemove(removedIndex) {
      var nextState = {};
      Object.keys(expandedFeatures).forEach(function (key) {
        var idx = parseInt(key, 10);
        if (idx < removedIndex) {
          nextState[idx] = expandedFeatures[key];
          return;
        }
        if (idx > removedIndex) {
          nextState[idx - 1] = expandedFeatures[key];
        }
      });
      expandedFeatures = nextState;
    }

    function hydrateSimpleFields() {
      var fields = root.querySelectorAll(".viora-services-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        var path = field.getAttribute("data-path");
        var value = getByPath(data, path);
        if (field.tagName === "SELECT") {
          field.value = typeof value === "string" && value ? value : "design";
          return;
        }
        field.value = typeof value === "string" ? value : value || "";
      });

      var featureFields = root.querySelectorAll(".viora-feature-input");
      Array.prototype.forEach.call(featureFields, function (field) {
        var cardIndex = parseInt(
          field.getAttribute("data-card-index") || "-1",
          10,
        );
        var featureIndex = parseInt(
          field.getAttribute("data-feature-index") || "-1",
          10,
        );
        if (
          cardIndex < 0 ||
          featureIndex < 0 ||
          !Array.isArray(data.items) ||
          !data.items[cardIndex]
        ) {
          field.value = "";
          return;
        }

        var features = normalizeFeatures(data.items[cardIndex].features);
        field.value =
          typeof features[featureIndex] === "string"
            ? features[featureIndex]
            : "";
      });
    }

    function renderCards() {
      if (!Array.isArray(data.items)) {
        data.items = [];
      }

      if (data.items.length === 0) {
        data.items.push(getDefaultCard());
      }

      var html = "";
      for (var i = 0; i < data.items.length; i += 1) {
        html += createCardMarkup(i);
      }
      listEl.innerHTML = html;

      var cards = listEl.querySelectorAll(".viora-services-card");
      Array.prototype.forEach.call(cards, function (card, index) {
        var item = data.items[index] || getDefaultCard();

        var titleEl = card.querySelector(".viora-services-card__title");
        if (titleEl) {
          var label = "Card " + String(index + 1);
          if (typeof item.title === "string" && item.title.trim() !== "") {
            label += ": " + item.title.trim();
          }
          titleEl.textContent = label;
        }

        var titleField = card.querySelector(
          '.viora-services-field[data-path="items.' +
            String(index) +
            '.title"]',
        );
        if (titleField) {
          titleField.value = typeof item.title === "string" ? item.title : "";
        }

        var descField = card.querySelector(
          '.viora-services-field[data-path="items.' +
            String(index) +
            '.description"]',
        );
        if (descField) {
          descField.value =
            typeof item.description === "string" ? item.description : "";
        }

        var featuresControl = card.querySelector("[data-features-control]");
        if (featuresControl) {
          var featuresHintPath =
            featuresControl.getAttribute("data-help-path") || "";
          var features = normalizeFeatures(item.features);

          var featuresCount = featuresControl.querySelector(
            ".viora-features-count",
          );
          if (featuresCount) {
            featuresCount.textContent = String(features.length);
          }

          var featuresToggleBtn = featuresControl.querySelector(
            ".viora-toggle-features",
          );
          if (featuresToggleBtn) {
            featuresToggleBtn.setAttribute("data-card-index", String(index));
          }

          var addFeatureBtn = featuresControl.querySelector(
            ".viora-add-feature-option",
          );
          if (addFeatureBtn) {
            addFeatureBtn.setAttribute("data-card-index", String(index));
          }

          var featuresList = featuresControl.querySelector(
            "[data-features-list]",
          );
          if (featuresList) {
            var featuresHtml = "";
            for (var j = 0; j < features.length; j += 1) {
              featuresHtml += createFeatureOptionMarkup(
                index,
                j,
                features[j],
                featuresHintPath,
              );
            }
            featuresList.innerHTML = featuresHtml;
          }

          setFeaturesExpanded(
            featuresControl,
            index,
            isFeaturesExpanded(index),
          );
        }

        var removeCardBtn = card.querySelector(".viora-remove-service-card");
        if (removeCardBtn) {
          removeCardBtn.setAttribute("data-card-index", String(index));
          removeCardBtn.disabled = data.items.length <= 1;
        }

        var toggleBtn = card.querySelector(".viora-toggle-service-card");
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

    function updateMediaPreview(group) {
      var idPath = group.getAttribute("data-id-path");
      var urlPath = group.getAttribute("data-url-path");
      var fallbackPath = group.getAttribute("data-fallback-path");
      var url = getByPath(data, urlPath) || getByPath(data, fallbackPath) || "";
      var preview = group.querySelector(".viora-media-preview");
      if (!preview) return;

      if (url) {
        preview.innerHTML = "<img src='" + url + "' alt='' />";
      } else {
        preview.textContent = "";
      }

      var idField = group.querySelector(".viora-media-id-field");
      if (idField && idPath) {
        var idValue = getByPath(data, idPath);
        idField.value = idValue ? String(idValue) : "";
      }
    }

    root.addEventListener("input", function (event) {
      var target = event.target;
      if (!target) {
        return;
      }

      if (target.matches(".viora-services-field[data-path]")) {
        var path = target.getAttribute("data-path");
        setByPath(data, path, target.value);
        writeData({ immediateSync: false });
        sendLivePreviewPatch();
        return;
      }

      if (target.matches(".viora-feature-input")) {
        var inputCardIndex = parseInt(
          target.getAttribute("data-card-index") || "-1",
          10,
        );
        var inputFeatureIndex = parseInt(
          target.getAttribute("data-feature-index") || "-1",
          10,
        );

        if (
          inputCardIndex >= 0 &&
          inputFeatureIndex >= 0 &&
          Array.isArray(data.items) &&
          data.items[inputCardIndex]
        ) {
          if (!Array.isArray(data.items[inputCardIndex].features)) {
            data.items[inputCardIndex].features = [];
          }
          data.items[inputCardIndex].features[inputFeatureIndex] = target.value;
        }

        writeData({ immediateSync: false });
        sendLivePreviewPatch();
      }
    });

    root.addEventListener("change", function (event) {
      var target = event.target;
      if (!target) {
        return;
      }

      if (target.matches(".viora-services-field[data-path]")) {
        var path = target.getAttribute("data-path");
        setByPath(data, path, target.value);
        writeData({ immediateSync: true });
        sendLivePreviewPatch();
        return;
      }

      if (target.matches(".viora-feature-input")) {
        var changeCardIndex = parseInt(
          target.getAttribute("data-card-index") || "-1",
          10,
        );
        var changeFeatureIndex = parseInt(
          target.getAttribute("data-feature-index") || "-1",
          10,
        );

        if (
          changeCardIndex >= 0 &&
          changeFeatureIndex >= 0 &&
          Array.isArray(data.items) &&
          data.items[changeCardIndex]
        ) {
          if (!Array.isArray(data.items[changeCardIndex].features)) {
            data.items[changeCardIndex].features = [];
          }
          data.items[changeCardIndex].features[changeFeatureIndex] =
            target.value;
        }

        writeData({ immediateSync: true });
        sendLivePreviewPatch();
      }
    });

    root.addEventListener("click", function (event) {
      var target = event.target;
      if (!target) {
        return;
      }

      var addBtn = target.closest(".viora-add-service-card");
      if (addBtn) {
        event.preventDefault();
        data.items.push(getDefaultCard());
        expandedCards[data.items.length - 1] = true;
        renderCards();
        writeData({ immediateSync: true });
        sendLivePreviewPatch();
        applyHelpMode(readHelpMode());
        return;
      }

      var toggleCardBtn = target.closest(".viora-toggle-service-card");
      if (toggleCardBtn) {
        event.preventDefault();
        var toggleIndex = parseInt(
          toggleCardBtn.getAttribute("data-card-index") || "-1",
          10,
        );
        if (toggleIndex >= 0) {
          var cardEl = toggleCardBtn.closest(".viora-services-card");
          var nextExpanded = !isCardExpanded(toggleIndex);
          if (cardEl) {
            setCardExpanded(cardEl, toggleIndex, nextExpanded);
          } else {
            expandedCards[toggleIndex] = nextExpanded;
          }
        }
        return;
      }

      var removeCardBtn = target.closest(".viora-remove-service-card");
      if (removeCardBtn) {
        event.preventDefault();
        var index = parseInt(
          removeCardBtn.getAttribute("data-card-index") || "-1",
          10,
        );
        if (index >= 0 && index < data.items.length && data.items.length > 1) {
          data.items.splice(index, 1);
          shiftExpandedCardsAfterRemove(index);
          shiftExpandedFeaturesAfterRemove(index);
          renderCards();
          writeData({ immediateSync: true });
          sendLivePreviewPatch();
          applyHelpMode(readHelpMode());
        }
        return;
      }

      var toggleFeaturesBtn = target.closest(".viora-toggle-features");
      if (toggleFeaturesBtn) {
        event.preventDefault();
        var featuresCardIndex = parseInt(
          toggleFeaturesBtn.getAttribute("data-card-index") || "-1",
          10,
        );
        if (featuresCardIndex >= 0) {
          var featuresControl = toggleFeaturesBtn.closest(
            "[data-features-control]",
          );
          var nextExpanded = !isFeaturesExpanded(featuresCardIndex);
          if (featuresControl) {
            setFeaturesExpanded(
              featuresControl,
              featuresCardIndex,
              nextExpanded,
            );
          } else {
            expandedFeatures[featuresCardIndex] = nextExpanded;
          }
        }
        return;
      }

      var addFeatureBtn = target.closest(".viora-add-feature-option");
      if (addFeatureBtn) {
        event.preventDefault();
        var addCardIndex = parseInt(
          addFeatureBtn.getAttribute("data-card-index") || "-1",
          10,
        );
        if (
          addCardIndex >= 0 &&
          Array.isArray(data.items) &&
          data.items[addCardIndex]
        ) {
          if (!Array.isArray(data.items[addCardIndex].features)) {
            data.items[addCardIndex].features = [];
          }
          data.items[addCardIndex].features.push("");
          expandedCards[addCardIndex] = true;
          expandedFeatures[addCardIndex] = true;
          var nextFeatureIndex = data.items[addCardIndex].features.length - 1;
          renderCards();
          writeData({ immediateSync: true });
          sendLivePreviewPatch();
          applyHelpMode(readHelpMode());

          var nextInput = root.querySelector(
            '.viora-feature-input[data-card-index="' +
              String(addCardIndex) +
              '"][data-feature-index="' +
              String(nextFeatureIndex) +
              '"]',
          );
          if (nextInput && typeof nextInput.focus === "function") {
            nextInput.focus();
          }
        }
        return;
      }

      var removeFeatureBtn = target.closest(".viora-remove-feature-option");
      if (removeFeatureBtn) {
        event.preventDefault();
        var removeCardIndex = parseInt(
          removeFeatureBtn.getAttribute("data-card-index") || "-1",
          10,
        );
        var removeFeatureIndex = parseInt(
          removeFeatureBtn.getAttribute("data-feature-index") || "-1",
          10,
        );

        if (
          removeCardIndex >= 0 &&
          removeFeatureIndex >= 0 &&
          Array.isArray(data.items) &&
          data.items[removeCardIndex]
        ) {
          if (!Array.isArray(data.items[removeCardIndex].features)) {
            data.items[removeCardIndex].features = [];
          }
          data.items[removeCardIndex].features.splice(removeFeatureIndex, 1);
          expandedCards[removeCardIndex] = true;
          expandedFeatures[removeCardIndex] = true;
          renderCards();
          writeData({ immediateSync: true });
          sendLivePreviewPatch();
          applyHelpMode(readHelpMode());
        }
        return;
      }

      var selectMediaBtn = target.closest(".viora-select-media");
      if (selectMediaBtn) {
        event.preventDefault();
        var selectGroup = selectMediaBtn.closest(".viora-media-field");
        if (!selectGroup) {
          return;
        }

        if (!mediaFrame) {
          mediaFrame = wp.media({
            title:
              typeof i18n.selectImage === "string" && i18n.selectImage
                ? i18n.selectImage
                : "Select image",
            button: {
              text:
                typeof i18n.useImage === "string" && i18n.useImage
                  ? i18n.useImage
                  : "Use image",
            },
            multiple: false,
          });
        }

        if (typeof mediaFrame.off === "function") {
          mediaFrame.off("select");
        }

        mediaFrame.on("select", function () {
          var attachment = mediaFrame.state().get("selection").first().toJSON();
          var url = attachment && attachment.url ? attachment.url : "";
          var idPath = selectGroup.getAttribute("data-id-path");
          var urlPath = selectGroup.getAttribute("data-url-path");
          var fallbackPath = selectGroup.getAttribute("data-fallback-path");

          setByPath(data, idPath, attachment.id || 0);
          setByPath(data, urlPath, url);
          if (fallbackPath) {
            setByPath(data, fallbackPath, url);
          }

          writeData({ immediateSync: true });
          sendLivePreviewPatch();
          updateMediaPreview(selectGroup);
        });

        mediaFrame.open();
        return;
      }

      var removeMediaBtn = target.closest(".viora-remove-media");
      if (removeMediaBtn) {
        event.preventDefault();
        var removeGroup = removeMediaBtn.closest(".viora-media-field");
        if (!removeGroup) {
          return;
        }

        var removeIdPath = removeGroup.getAttribute("data-id-path");
        var removeUrlPath = removeGroup.getAttribute("data-url-path");
        var removeFallbackPath = removeGroup.getAttribute("data-fallback-path");
        setByPath(data, removeIdPath, 0);
        setByPath(data, removeUrlPath, "");
        if (removeFallbackPath) {
          setByPath(data, removeFallbackPath, "");
        }

        writeData({ immediateSync: true });
        sendLivePreviewPatch();
        updateMediaPreview(removeGroup);
      }
    });

    function bindSettingUpdates() {
      if (!setting || typeof setting.bind !== "function") {
        return;
      }

      setting.bind(function (newValue) {
        clearSyncTimer();

        var incoming = null;
        if (newValue && typeof newValue === "object") {
          incoming = cloneData(newValue, null);
        } else if (typeof newValue === "string" && newValue !== "") {
          incoming = parseJson(newValue, null);
        }

        if (!incoming || typeof incoming !== "object") {
          return;
        }

        var incomingJson = JSON.stringify(incoming || {});
        if (
          lastLocalSettingJson !== "" &&
          incomingJson === lastLocalSettingJson
        ) {
          lastLocalSettingJson = "";
          hidden.value = incomingJson;
          return;
        }

        data = deepMerge(cloneData(data, {}), incoming);
        if (!Array.isArray(data.items)) {
          data.items = [];
        }
        renderCards();
        hydrateSimpleFields();

        hidden.value = JSON.stringify(data || {});
      });
    }

    renderCards();
    hydrateSimpleFields();
    bindSettingUpdates();
    writeData({ immediateSync: false });

    if (typeof window.addEventListener === "function") {
      window.addEventListener("beforeunload", flushSyncSetting);
    }

    var saveButton = document.getElementById("save");
    if (saveButton) {
      saveButton.addEventListener("click", function () {
        writeData({ immediateSync: true });
      });
    }

    var helpEnabled = readHelpMode();
    applyHelpMode(helpEnabled);
    if (helpToggle) {
      helpToggle.checked = helpEnabled;
      helpToggle.addEventListener("change", function () {
        var enabled = !!helpToggle.checked;
        applyHelpMode(enabled);
        saveHelpMode(enabled);
      });
    }
  }

  function bootstrap() {
    var root = document.getElementById("viora-home-services-customizer-root");
    if (root) {
      init(root);
    }
  }

  function watchControlLifecycle() {
    if (
      window.wp &&
      wp.customize &&
      typeof wp.customize.section === "function"
    ) {
      var section = wp.customize.section("viora_home_services_section");
      if (
        section &&
        section.expanded &&
        typeof section.expanded.bind === "function"
      ) {
        section.expanded.bind(function (expanded) {
          if (expanded) {
            setTimeout(bootstrap, 50);
          }
        });
      }
    }

    if (window.MutationObserver && document.body) {
      var observer = new MutationObserver(function () {
        bootstrap();
      });
      observer.observe(document.body, { childList: true, subtree: true });
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootstrap);
  } else {
    bootstrap();
  }

  watchControlLifecycle();
})();
