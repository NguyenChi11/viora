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
        current[key] = {};
      }
      current = current[key];
    }
    current[parts[parts.length - 1]] = value;
  }

  function init(root) {
    if (!root) {
      return;
    }

    var hidden = root.querySelector("#viora-home-portfolio-data-json");
    if (!hidden) {
      return;
    }

    var source = window.vioraHomePortfolioMetaData || {};
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
    var helpToggle = root.querySelector(".viora-home-portfolio-help-toggle");
    var helpStorageKey = "viora_home_portfolio_help_mode";

    var data = parseJson(hidden.value, source.data || {});
    if (!data || typeof data !== "object") {
      data = {};
    }

    var enabledInput = root.querySelector("#viora-home-portfolio-enabled");
    if (enabledInput) {
      enabledInput.checked = !!(
        source.enabled === 1 ||
        source.enabled === true ||
        enabledInput.checked
      );
    }

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

    var fields = root.querySelectorAll(".viora-portfolio-field[data-path]");
    Array.prototype.forEach.call(fields, function (field) {
      var path = field.getAttribute("data-path");
      var value = getByPath(data, path);
      field.value = typeof value === "string" ? value : value || "";

      field.addEventListener("input", function () {
        setByPath(data, path, field.value);
        writeData();
      });
    });

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

    writeData();
  }

  function boot() {
    init(document.getElementById("viora-home-portfolio-metabox-root"));
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
