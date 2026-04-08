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
    if (!isObject(patch)) {
      return patch;
    }

    var output = isObject(base) ? cloneData(base, {}) : {};
    Object.keys(patch).forEach(function (key) {
      var patchValue = patch[key];
      if (isObject(patchValue)) {
        output[key] = deepMerge(output[key], patchValue);
      } else {
        output[key] = patchValue;
      }
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
        current[key] = {};
      }
      current = current[key];
    }
    current[parts[parts.length - 1]] = value;
  }

  function init(root) {
    if (!root || initializedRoots.has(root)) {
      return;
    }

    var hidden = root.querySelector("#viora-home-portfolio-data");
    if (!hidden) {
      return;
    }

    initializedRoots.add(root);

    var setting = null;
    if (window.wp && wp.customize && typeof wp.customize === "function") {
      try {
        setting = wp.customize("viora_home_portfolio_data");
      } catch (e) {
        setting = null;
      }
    }

    var initialHidden = root.querySelector(
      "#viora-home-portfolio-initial-data",
    );
    var initialData = parseJson(initialHidden ? initialHidden.value : "", {});
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
    var helpToggle = root.querySelector(".viora-home-portfolio-help-toggle");
    var helpStorageKey = "viora_home_portfolio_help_mode";
    var syncTimer = null;
    var syncDelay = 120;

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
      var fields = root.querySelectorAll(".viora-portfolio-field[data-path]");
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
        "viora_home_portfolio_live_data",
        cloneData(data, {}),
      );
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

    function hydrateFields() {
      var fields = root.querySelectorAll(".viora-portfolio-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        var path = field.getAttribute("data-path");
        var value = getByPath(data, path);
        field.value = typeof value === "string" ? value : value || "";
      });
    }

    function bindFields() {
      var fields = root.querySelectorAll(".viora-portfolio-field[data-path]");
      Array.prototype.forEach.call(fields, function (field) {
        var path = field.getAttribute("data-path");

        field.addEventListener("input", function () {
          setByPath(data, path, field.value);
          writeData();
          sendLivePreviewPatch();
        });

        field.addEventListener("change", function () {
          setByPath(data, path, field.value);
          writeData({ immediateSync: true });
          sendLivePreviewPatch();
        });
      });
    }

    if (helpToggle) {
      var helpEnabled = readHelpMode();
      helpToggle.checked = helpEnabled;
      applyHelpMode(helpEnabled);
      helpToggle.addEventListener("change", function () {
        var checked = !!helpToggle.checked;
        saveHelpMode(checked);
        applyHelpMode(checked);
      });
    }

    hydrateFields();
    bindFields();
    writeData({ immediateSync: true });
  }

  function boot() {
    init(document.getElementById("viora-home-portfolio-customizer-root"));
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
