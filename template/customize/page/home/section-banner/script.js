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
    if (initializedRoots.has(root)) return;

    var hidden = root.querySelector("#viora-home-banner-data");
    if (!hidden) return;

    var initialHidden = root.querySelector("#viora-home-banner-initial-data");
    var initialData = parseJson(initialHidden ? initialHidden.value : "", {});

    initializedRoots.add(root);

    var setting = null;
    if (window.wp && wp.customize && typeof wp.customize === "function") {
      try {
        setting = wp.customize("viora_home_banner_data");
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

    if (hidden.value === "[object Object]") {
      hidden.value = JSON.stringify(data || {});
    }

    var i18n =
      window.vioraHomeI18n && typeof window.vioraHomeI18n === "object"
        ? window.vioraHomeI18n
        : {};
    var helpHints =
      i18n.helpHints && typeof i18n.helpHints === "object"
        ? i18n.helpHints
        : {};
    var helpToggle = root.querySelector(".viora-home-banner-help-toggle");
    var helpStorageKey = "viora_home_banner_help_mode";
    var mediaFrame;
    var syncDelay = 120;
    var syncTimer = null;

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
        "viora_home_banner_live_data",
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

    function writeData(options) {
      options = options || {};
      var syncSetting = options.syncSetting !== false;
      var emitEvents = options.emitEvents === true;
      var immediateSync = options.immediateSync === true;

      if (syncSetting) {
        if (immediateSync) {
          flushSyncSetting();
        } else {
          scheduleSyncSetting();
        }
      }

      hidden.value = JSON.stringify(data || {});

      if (emitEvents) {
        hidden.dispatchEvent(new Event("input"));
        hidden.dispatchEvent(new Event("change"));
      }
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

        function syncFromField(immediateSync) {
          var value =
            field.type === "number"
              ? parseInt(field.value || "0", 10) || 0
              : field.value;
          setByPath(data, path, value);
          if (immediateSync === true) {
            writeData({ immediateSync: true });
          } else {
            writeData();
          }
          sendLivePreviewPatch();
          var mediaParent = field.closest(".viora-media-field");
          if (mediaParent) {
            updateMediaPreview(mediaParent);
          }
        }

        field.addEventListener("input", function () {
          syncFromField(false);
        });

        field.addEventListener("change", function () {
          syncFromField(true);
        });
      });

      var avatarField = root.querySelector("[data-avatar-input='1']");
      if (avatarField) {
        avatarField.addEventListener("input", function () {
          setByPath(data, "trust.avatars", toAvatarList(avatarField.value));
          writeData();
          sendLivePreviewPatch();
        });

        avatarField.addEventListener("change", function () {
          setByPath(data, "trust.avatars", toAvatarList(avatarField.value));
          writeData({ immediateSync: true });
          sendLivePreviewPatch();
        });
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
              writeData({ immediateSync: true });
              sendLivePreviewPatch();
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
            writeData({ immediateSync: true });
            sendLivePreviewPatch();
            hydrateSimpleFields();
            updateMediaPreview(group);
          });
        }
      });
    }

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

        data = deepMerge(cloneData(data, {}), incoming);
        hydrateSimpleFields();

        var groups = root.querySelectorAll(".viora-media-field");
        Array.prototype.forEach.call(groups, function (group) {
          updateMediaPreview(group);
        });

        hidden.value = JSON.stringify(data || {});
      });
    }

    hydrateSimpleFields();
    bindFields();
    bindMedia();
    bindSettingUpdates();
    writeData({ syncSetting: false, emitEvents: false });

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
    var root = document.getElementById("viora-home-banner-customizer-root");
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
      var section = wp.customize.section("viora_home_banner_section");
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
