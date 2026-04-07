(function () {
  function parseJson(value, fallback) {
    try {
      var decoded = JSON.parse(value || "");
      return decoded && typeof decoded === "object" ? decoded : fallback;
    } catch (e) {
      return fallback;
    }
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

  function toAvatarList(value) {
    return String(value || "")
      .split(",")
      .map(function (item) {
        return item.trim();
      })
      .filter(function (item) {
        return item.length > 0;
      });
  }

  function init(root) {
    if (!root) return;

    var hidden = root.querySelector("#viora-home-banner-data-json");
    if (!hidden) return;

    var source = window.vioraHomeBannerMetaData || {};
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
    var helpToggle = root.querySelector(".viora-home-banner-help-toggle");
    var helpStorageKey = "viora_home_banner_help_mode";
    var data = parseJson(hidden.value, source.data || {});
    var enabledInput = root.querySelector("#viora-home-banner-enabled");
    var mediaFrame;

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
      var fields = root.querySelectorAll(".viora-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        setHelpPlaceholder(field, field.getAttribute("data-path"), enabled);
      });

      var avatarField = root.querySelector("[data-avatar-input='1']");
      if (avatarField) {
        var avatarPath =
          avatarField.getAttribute("data-help-path") || "trust.avatars";
        setHelpPlaceholder(avatarField, avatarPath, enabled);
      }
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

    function writeData() {
      hidden.value = JSON.stringify(data);
    }

    function hydrateSimpleFields() {
      var fields = root.querySelectorAll(".viora-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        var path = field.getAttribute("data-path");
        var value = getByPath(data, path);
        if (field.type === "number") {
          field.value = value !== "" ? String(value) : "";
        } else {
          field.value = typeof value === "string" ? value : value || "";
        }
      });

      var avatarField = root.querySelector("[data-avatar-input='1']");
      if (avatarField) {
        var avatars = getByPath(data, "trust.avatars");
        if (Array.isArray(avatars)) {
          avatarField.value = avatars.join(", ");
        }
      }
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

    function bindFields() {
      var fields = root.querySelectorAll(".viora-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        var path = field.getAttribute("data-path");
        field.addEventListener("input", function () {
          var value =
            field.type === "number"
              ? parseInt(field.value || "0", 10) || 0
              : field.value;
          setByPath(data, path, value);
          writeData();
          var mediaParent = field.closest(".viora-media-field");
          if (mediaParent) {
            updateMediaPreview(mediaParent);
          }
        });
      });

      var avatarField = root.querySelector("[data-avatar-input='1']");
      if (avatarField) {
        avatarField.addEventListener("input", function () {
          setByPath(data, "trust.avatars", toAvatarList(avatarField.value));
          writeData();
        });
      }

      if (enabledInput) {
        enabledInput.checked = !!(
          source.enabled === 1 ||
          source.enabled === true ||
          enabledInput.checked
        );
      }
    }

    function bindMedia() {
      var groups = root.querySelectorAll(".viora-media-field");
      Array.prototype.forEach.call(groups, function (group) {
        updateMediaPreview(group);

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
              writeData();
              hydrateSimpleFields();
              updateMediaPreview(group);
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
            writeData();
            hydrateSimpleFields();
            updateMediaPreview(group);
          });
        }
      });
    }

    hydrateSimpleFields();
    bindFields();
    bindMedia();
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
      init(document.getElementById("viora-home-banner-metabox-root"));
    });
  } else {
    init(document.getElementById("viora-home-banner-metabox-root"));
  }
})();
