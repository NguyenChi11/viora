(function () {
  function parseJson(value, fallback) {
    try {
      var decoded = JSON.parse(value || "");
      return decoded && typeof decoded === "object" ? decoded : fallback;
    } catch (e) {
      return fallback;
    }
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
    if (!root) return;

    var hidden = root.querySelector("#viora-home-services-data-json");
    var listEl = root.querySelector("[data-services-cards-list]");
    var addCardBtn = root.querySelector(".viora-add-service-card");
    var cardTemplate = root.querySelector("#viora-services-card-template");
    if (!hidden || !listEl || !addCardBtn || !cardTemplate) return;

    var source = window.vioraHomeServicesMetaData || {};
    var i18n =
      source.i18n && typeof source.i18n === "object"
        ? source.i18n
        : window.vioraHomeI18n && typeof window.vioraHomeI18n === "object"
          ? window.vioraHomeI18n
          : {};
    var helpHints =
      i18n.helpHints && typeof i18n.helpHints === "object"
        ? i18n.helpHints
        : {};
    var helpToggle = root.querySelector(".viora-home-services-help-toggle");
    var helpStorageKey = "viora_home_services_help_mode";
    var expandedCards = {};
    var expandedFeatures = {};

    var data = parseJson(hidden.value, source.data || {});
    if (!Array.isArray(data.items)) {
      data.items = [];
    }

    var enabledInput = root.querySelector("#viora-home-services-enabled");
    if (enabledInput) {
      enabledInput.checked = !!(
        source.enabled === 1 ||
        source.enabled === true ||
        enabledInput.checked
      );
    }

    var mediaFrame;

    function writeData() {
      hidden.value = JSON.stringify(data || {});
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

    root.addEventListener("input", function (event) {
      var target = event.target;
      if (!target) {
        return;
      }

      if (target.matches(".viora-services-field[data-path]")) {
        var path = target.getAttribute("data-path");
        setByPath(data, path, target.value);
        writeData();
        return;
      }

      if (target.matches(".viora-feature-input")) {
        var cardIndex = parseInt(
          target.getAttribute("data-card-index") || "-1",
          10,
        );
        var featureIndex = parseInt(
          target.getAttribute("data-feature-index") || "-1",
          10,
        );

        if (
          cardIndex >= 0 &&
          featureIndex >= 0 &&
          Array.isArray(data.items) &&
          data.items[cardIndex]
        ) {
          if (!Array.isArray(data.items[cardIndex].features)) {
            data.items[cardIndex].features = [];
          }
          data.items[cardIndex].features[featureIndex] = target.value;
        }

        writeData();
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
        writeData();
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
          writeData();
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
          writeData();
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
          writeData();
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

          updateMediaPreview(selectGroup);
          writeData();
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
        updateMediaPreview(removeGroup);
        writeData();
      }
    });

    renderCards();
    writeData();

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

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      init(document.getElementById("viora-home-services-metabox-root"));
    });
  } else {
    init(document.getElementById("viora-home-services-metabox-root"));
  }
})();
