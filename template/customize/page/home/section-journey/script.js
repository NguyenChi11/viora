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

  function init(root) {
    if (!root || initializedRoots.has(root)) {
      return;
    }

    var hidden = root.querySelector("#viora-home-journey-data");
    if (!hidden) {
      return;
    }

    initializedRoots.add(root);

    var initialHidden = root.querySelector("#viora-home-journey-initial-data");
    var initialData = parseJson(initialHidden ? initialHidden.value : "", {});

    var setting = null;
    if (window.wp && wp.customize && typeof wp.customize === "function") {
      try {
        setting = wp.customize("viora_home_journey_data");
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
    if (!data || typeof data !== "object") {
      data = {};
    }

    var i18n =
      window.vioraHomeI18n && typeof window.vioraHomeI18n === "object"
        ? window.vioraHomeI18n
        : {};
    var helpHints =
      i18n.helpHints && typeof i18n.helpHints === "object"
        ? i18n.helpHints
        : {};
    var helpToggle = root.querySelector(".viora-home-journey-help-toggle");
    var helpStorageKey = "viora_home_journey_help_mode";
    var mediaFrame;
    var syncDelay = 120;
    var syncTimer = null;
    var expandedCards = {};
    var cardsList = root.querySelector("[data-journey-cards-list]");
    var cardTemplate = root.querySelector("#viora-journey-card-template");
    var addCardButton = root.querySelector(".viora-add-journey-card");
    var maxTimelineItems = 12;

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
      if (base && typeof base === "object") {
        data = deepMerge(cloneData(base, {}), cloneData(data, {}));
      } else {
        data = cloneData(data, {});
      }
      setting.set(data);
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
        "viora_home_journey_live_data",
        cloneData(data, {}),
      );
    }

    function openJourneyLinkPicker(urlInput, titleInput) {
      if (
        window.vioraOpenLinkPicker &&
        typeof window.vioraOpenLinkPicker === "function"
      ) {
        window.vioraOpenLinkPicker(
          urlInput || null,
          titleInput || null,
          null,
          "viora_home_journey_section",
        );
        return;
      }

      if (
        !window.wp ||
        !wp.customize ||
        typeof wp.customize.section !== "function"
      ) {
        return;
      }

      var targetObj = {
        sectionId: "viora_home_journey_section",
        urlInput: urlInput || null,
        titleInput: titleInput || null,
        targetSelect: null,
        currentUrl: urlInput ? urlInput.value || "" : "",
        currentTitle: titleInput ? titleInput.value || "" : "",
        currentTarget: "",
      };

      window.vioraLinkTarget = targetObj;

      var linkPickerSection = wp.customize.section("viora_link_picker_section");
      if (linkPickerSection && typeof linkPickerSection.expand === "function") {
        linkPickerSection.expand();
      }
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
      var helpPath =
        field.getAttribute("data-help-path") || field.getAttribute("data-path");
      var hint = enabled ? getHintForPath(helpPath) : "";
      if (hint !== "") {
        field.setAttribute("placeholder", hint);
      } else {
        field.removeAttribute("placeholder");
      }
    }

    function applyHelpMode(enabled) {
      var fields = root.querySelectorAll(".viora-journey-field[data-path]");
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

    function enforceSingleActive(activePath) {
      var fields = root.querySelectorAll(".viora-journey-active[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        var fieldPath = field.getAttribute("data-path");
        var checked = fieldPath === activePath;
        field.checked = checked;
        setByPath(data, fieldPath, checked);
      });
    }

    function isCardExpanded(index) {
      return expandedCards[index] !== false;
    }

    function setCardExpanded(card, index, expanded) {
      var body = card.querySelector(".viora-journey-card__body");
      var toggleBtn = card.querySelector(".viora-toggle-journey-card");
      if (body) {
        body.hidden = !expanded;
      }
      card.classList.toggle("is-collapsed", !expanded);
      if (toggleBtn) {
        toggleBtn.setAttribute("aria-expanded", expanded ? "true" : "false");
      }
      expandedCards[index] = expanded;
    }

    function toInt(value, fallback) {
      var parsed = parseInt(value, 10);
      return Number.isFinite(parsed) ? parsed : fallback;
    }

    function createEmptyTimelineItem() {
      return {
        year: "",
        title: "",
        description: "",
        icon_id: 0,
        icon_url: "",
        icon: "",
        isActive: false,
      };
    }

    function ensureTimelineItemsArray() {
      var layout = isObject(data.layout) ? data.layout : {};
      data.layout = layout;

      var timeline = isObject(layout.timeline) ? layout.timeline : {};
      layout.timeline = timeline;

      if (!Array.isArray(timeline.items)) {
        timeline.items = [];
      }

      return timeline.items;
    }

    function ensureSingleActiveItem(items) {
      if (!Array.isArray(items) || items.length === 0) {
        return;
      }

      var activeIndex = -1;
      for (var i = 0; i < items.length; i += 1) {
        if (items[i] && items[i].isActive) {
          activeIndex = i;
          break;
        }
      }

      if (activeIndex < 0) {
        activeIndex = 0;
      }

      for (var j = 0; j < items.length; j += 1) {
        items[j].isActive = j === activeIndex;
      }
    }

    function normalizeTimelineItems() {
      var rawItems = ensureTimelineItemsArray();
      var normalized = [];

      for (var i = 0; i < rawItems.length; i += 1) {
        var item = isObject(rawItems[i]) ? rawItems[i] : {};
        var iconUrl =
          typeof item.icon_url === "string"
            ? item.icon_url
            : typeof item.icon === "string"
              ? item.icon
              : "";

        normalized.push({
          year: typeof item.year === "string" ? item.year : "",
          title: typeof item.title === "string" ? item.title : "",
          description:
            typeof item.description === "string" ? item.description : "",
          icon_id: toInt(item.icon_id, 0),
          icon_url: iconUrl,
          icon: iconUrl,
          isActive: !!item.isActive,
        });
      }

      if (normalized.length > maxTimelineItems) {
        normalized = normalized.slice(0, maxTimelineItems);
      }

      ensureSingleActiveItem(normalized);
      setByPath(data, "layout.timeline.items", normalized);
      return normalized;
    }

    function updateAddButtonState(itemCount) {
      if (!addCardButton) {
        return;
      }

      addCardButton.disabled = itemCount >= maxTimelineItems;
    }

    function updateItemLabels() {
      var cards = root.querySelectorAll(".viora-journey-card[data-item-index]");
      Array.prototype.forEach.call(cards, function (card) {
        var index = parseInt(card.getAttribute("data-item-index") || "-1", 10);
        if (index < 0) {
          return;
        }

        var title = getByPath(
          data,
          "layout.timeline.items." + index + ".title",
        );
        var year = getByPath(data, "layout.timeline.items." + index + ".year");
        var label = "Item " + String(index + 1);
        if (year) {
          label += " (" + String(year) + ")";
        }
        if (title) {
          label += ": " + String(title);
        }

        var titleEl = card.querySelector(".viora-journey-card__title");
        if (titleEl) {
          titleEl.textContent = label;
        }
      });
    }

    function hydrateFields() {
      var fields = root.querySelectorAll(".viora-journey-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        var path = field.getAttribute("data-path");
        var value = getByPath(data, path);
        if (field.type === "checkbox") {
          field.checked = !!value;
          return;
        }
        field.value = typeof value === "string" ? value : value || "";
      });

      var mediaGroups = root.querySelectorAll(".viora-media-field");
      Array.prototype.forEach.call(mediaGroups, function (group) {
        updateMediaPreview(group);
      });

      updateItemLabels();

      var cards = root.querySelectorAll(".viora-journey-card[data-item-index]");
      Array.prototype.forEach.call(cards, function (card) {
        var index = parseInt(card.getAttribute("data-item-index") || "-1", 10);
        if (index < 0) {
          return;
        }
        setCardExpanded(card, index, isCardExpanded(index));
      });
    }

    function bindFields() {
      var fields = root.querySelectorAll(".viora-journey-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        if (field.dataset.journeyFieldBound === "1") {
          return;
        }
        field.dataset.journeyFieldBound = "1";

        var path = field.getAttribute("data-path");

        function syncFromField(immediateSync) {
          var value = field.type === "checkbox" ? !!field.checked : field.value;

          if (field.classList.contains("viora-journey-active")) {
            if (value) {
              enforceSingleActive(path);
            } else {
              setByPath(data, path, false);
              ensureSingleActiveItem(normalizeTimelineItems());
              hydrateFields();
            }
          } else {
            setByPath(data, path, value);
          }

          if (immediateSync === true) {
            writeData({ immediateSync: true });
          } else {
            writeData();
          }

          updateItemLabels();
          sendLivePreviewPatch();
        }

        field.addEventListener("input", function () {
          syncFromField(false);
        });

        field.addEventListener("change", function () {
          syncFromField(true);
        });
      });
    }

    function bindMedia() {
      var groups = root.querySelectorAll(".viora-media-field");
      Array.prototype.forEach.call(groups, function (group) {
        if (group.dataset.journeyMediaBound === "1") {
          return;
        }
        group.dataset.journeyMediaBound = "1";

        var selectBtn = group.querySelector(".viora-select-media");
        var removeBtn = group.querySelector(".viora-remove-media");
        var idPath = group.getAttribute("data-id-path");
        var urlPath = group.getAttribute("data-url-path");
        var fallbackPath = group.getAttribute("data-fallback-path");

        if (selectBtn) {
          selectBtn.addEventListener("click", function (event) {
            event.preventDefault();

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
              var attachment = mediaFrame
                .state()
                .get("selection")
                .first()
                .toJSON();
              var url = attachment && attachment.url ? attachment.url : "";

              setByPath(data, idPath, attachment.id || 0);
              setByPath(data, urlPath, url);
              if (fallbackPath) {
                setByPath(data, fallbackPath, url);
              }

              writeData({ immediateSync: true });
              updateMediaPreview(group);
              sendLivePreviewPatch();
            });

            mediaFrame.open();
          });
        }

        if (removeBtn) {
          removeBtn.addEventListener("click", function (event) {
            event.preventDefault();
            setByPath(data, idPath, 0);
            setByPath(data, urlPath, "");
            if (fallbackPath) {
              setByPath(data, fallbackPath, "");
            }

            writeData({ immediateSync: true });
            updateMediaPreview(group);
            sendLivePreviewPatch();
          });
        }
      });
    }

    function bindChooseLinkButtons() {
      var buttons = root.querySelectorAll(".viora-journey-choose-link");
      Array.prototype.forEach.call(buttons, function (button) {
        if (button.dataset.journeyLinkBound === "1") {
          return;
        }
        button.dataset.journeyLinkBound = "1";

        button.addEventListener("click", function (event) {
          event.preventDefault();

          var urlPath = button.getAttribute("data-url-path") || "";
          var titlePath = button.getAttribute("data-title-path") || "";
          var urlInput = urlPath
            ? root.querySelector(
                '.viora-journey-field[data-path="' + urlPath + '"]',
              )
            : null;
          var titleInput = titlePath
            ? root.querySelector(
                '.viora-journey-field[data-path="' + titlePath + '"]',
              )
            : null;

          openJourneyLinkPicker(urlInput, titleInput);
        });
      });
    }

    function bindCardToggles() {
      var buttons = root.querySelectorAll(".viora-toggle-journey-card");
      Array.prototype.forEach.call(buttons, function (button) {
        if (button.dataset.journeyToggleBound === "1") {
          return;
        }
        button.dataset.journeyToggleBound = "1";

        button.addEventListener("click", function (event) {
          event.preventDefault();

          var card = button.closest(".viora-journey-card[data-item-index]");
          if (!card) {
            return;
          }

          var index = parseInt(
            card.getAttribute("data-item-index") || "-1",
            10,
          );
          if (index < 0) {
            return;
          }

          setCardExpanded(card, index, !isCardExpanded(index));
        });
      });
    }

    function bindRemoveCardButtons() {
      var buttons = root.querySelectorAll(".viora-remove-journey-card");
      Array.prototype.forEach.call(buttons, function (button) {
        if (button.dataset.journeyRemoveBound === "1") {
          return;
        }
        button.dataset.journeyRemoveBound = "1";

        button.addEventListener("click", function (event) {
          event.preventDefault();

          var index = toInt(button.getAttribute("data-card-index"), -1);
          if (index < 0) {
            return;
          }

          var items = normalizeTimelineItems();
          if (index >= items.length) {
            return;
          }

          items.splice(index, 1);
          ensureSingleActiveItem(items);
          setByPath(data, "layout.timeline.items", items);

          writeData({ immediateSync: true });
          sendLivePreviewPatch();

          var openIndex =
            items.length > 0 ? Math.min(index, items.length - 1) : null;
          renderTimelineCards({ openIndex: openIndex });
        });
      });
    }

    function bindAddCardButton() {
      if (!addCardButton || addCardButton.dataset.journeyAddBound === "1") {
        return;
      }
      addCardButton.dataset.journeyAddBound = "1";

      addCardButton.addEventListener("click", function (event) {
        event.preventDefault();

        var items = normalizeTimelineItems();
        if (items.length >= maxTimelineItems) {
          updateAddButtonState(items.length);
          return;
        }

        var nextItem = createEmptyTimelineItem();
        if (items.length === 0) {
          nextItem.isActive = true;
        }
        items.push(nextItem);
        ensureSingleActiveItem(items);
        setByPath(data, "layout.timeline.items", items);

        writeData({ immediateSync: true });
        sendLivePreviewPatch();
        renderTimelineCards({ openIndex: items.length - 1 });
      });
    }

    function renderTimelineCards(options) {
      options = options || {};

      if (!cardsList || !cardTemplate) {
        return;
      }

      var items = normalizeTimelineItems();
      var templateMarkup = cardTemplate.innerHTML || "";
      var previousExpanded = cloneData(expandedCards, {});
      var markup = "";

      for (var i = 0; i < items.length; i += 1) {
        markup += templateMarkup.replace(/__INDEX__/g, String(i));
      }

      cardsList.innerHTML = markup;
      expandedCards = {};

      for (var index = 0; index < items.length; index += 1) {
        if (typeof options.openIndex === "number") {
          expandedCards[index] = index === options.openIndex;
          continue;
        }

        if (typeof previousExpanded[index] === "boolean") {
          expandedCards[index] = previousExpanded[index];
          continue;
        }

        expandedCards[index] = true;
      }

      hydrateFields();
      bindFields();
      bindMedia();
      bindCardToggles();
      bindRemoveCardButtons();
      updateAddButtonState(items.length);
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

    renderTimelineCards();
    bindFields();
    bindMedia();
    bindChooseLinkButtons();
    bindCardToggles();
    bindRemoveCardButtons();
    bindAddCardButton();

    if (helpToggle && helpToggle.checked) {
      applyHelpMode(true);
    }

    writeData({ immediateSync: true });
  }

  function boot() {
    init(document.getElementById("viora-home-journey-customizer-root"));
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }

  if (window.wp && wp.customize && typeof wp.customize.bind === "function") {
    wp.customize.bind("ready", boot);
    wp.customize.bind("pane-contents-reflowed", boot);
  }
})();
