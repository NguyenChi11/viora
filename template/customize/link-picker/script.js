(function () {
  var linkPickerI18n =
    window.vioraLinkPickerI18n &&
    typeof window.vioraLinkPickerI18n === "object" &&
    window.vioraLinkPickerI18n
      ? window.vioraLinkPickerI18n
      : window.vioraLinkPickerI18n &&
          typeof window.vioraLinkPickerI18n === "object" &&
          window.vioraLinkPickerI18n
        ? window.vioraLinkPickerI18n
        : {};

  function escHtml(s) {
    return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function t(key, fallback) {
    var val = linkPickerI18n ? linkPickerI18n[key] : null;
    return typeof val === "string" && val ? val : fallback;
  }

  function getLinkTarget() {
    if (window.vioraLinkTarget && typeof window.vioraLinkTarget === "object") {
      return window.vioraLinkTarget;
    }
    if (window.vioraLinkTarget && typeof window.vioraLinkTarget === "object") {
      return window.vioraLinkTarget;
    }
    return null;
  }

  function clearLinkTarget() {
    window.vioraLinkTarget = null;
    window.vioraLinkTarget = null;
  }

  function readTargetValue(targetEl) {
    if (!targetEl) return "";
    if (targetEl.type === "checkbox") {
      return targetEl.checked ? "_blank" : "";
    }
    return targetEl.value || "";
  }

  function getSettingInput(settingId) {
    if (!settingId) return null;
    var byData = document.querySelector(
      '[data-customize-setting-link="' + settingId + '"]',
    );
    if (byData) return byData;
    return document.getElementById("_customize-input-" + settingId);
  }

  function navigateToLinkPicker(urlInput, titleInput, targetSelect, sectionId) {
    var targetObj = {
      sectionId: sectionId || "",
      urlInput: urlInput || null,
      titleInput: titleInput || null,
      targetSelect: targetSelect || null,
      currentUrl: urlInput ? urlInput.value || "" : "",
      currentTitle: titleInput ? titleInput.value || "" : "",
      currentTarget: readTargetValue(targetSelect),
    };

    window.vioraLinkTarget = targetObj;
    window.vioraLinkTarget = targetObj;

    if (
      window.wp &&
      wp.customize &&
      typeof wp.customize.section === "function"
    ) {
      var sec =
        wp.customize.section("viora_link_picker_section") ||
        wp.customize.section("viora_link_picker_section");
      if (sec && typeof sec.expand === "function") {
        sec.expand();
      }
    }
  }

  function bindChooseLinkControls() {
    if (window.__vioraChooseLinkControlBound) return;
    window.__vioraChooseLinkControlBound = true;

    document.addEventListener("click", function (e) {
      var target = e.target;
      if (target && target.nodeType === 3) {
        target = target.parentElement;
      }
      if (!target || !target.closest) return;

      var btn = target.closest(".viora-choose-link-control");
      if (!btn) return;

      e.preventDefault();

      var urlInput = getSettingInput(btn.getAttribute("data-url-setting"));
      var titleInput = getSettingInput(btn.getAttribute("data-title-setting"));
      var targetInput = getSettingInput(
        btn.getAttribute("data-target-setting"),
      );
      var returnSection = btn.getAttribute("data-return-section") || "";

      navigateToLinkPicker(urlInput, titleInput, targetInput, returnSection);
    });
  }

  window.vioraOpenLinkPicker = navigateToLinkPicker;

  function ensureDirectOpenNotice(visible) {
    var wrap = document.querySelector(".viora-link-popup");
    if (!wrap) return;

    var id = "viora-link-picker-direct-notice";
    var note = document.getElementById(id);
    if (!note) {
      note = document.createElement("div");
      note.id = id;
      note.className = "notice notice-warning inline";
      note.setAttribute("role", "status");
      note.style.margin = "0 0 12px";
      note.style.padding = "8px 10px";
      note.style.boxSizing = "border-box";
      note.textContent = t(
        "directOpenNotice",
        "Link Picker is used from other sections. Please use the “Choose Link” button in the relevant tab to pick a link.",
      );
      wrap.insertBefore(note, wrap.firstChild);
    }

    note.style.display = visible ? "block" : "none";
  }

  /* ── helpers ───────────────────────────────────────────────── */
  function normalizeTitle(t) {
    if (!t) return "";
    if (typeof t === "string") return t;
    if (t.rendered) return t.rendered;
    return String(t);
  }

  function fetchAll(base) {
    var per = 50;
    function onePage(page) {
      return fetch(base + "&per_page=" + per + "&page=" + page, {
        credentials: "same-origin",
      })
        .then(function (r) {
          var total = parseInt(r.headers.get("X-WP-TotalPages") || "1", 10);
          return r.json().then(function (data) {
            return { data: data, totalPages: total };
          });
        })
        .catch(function () {
          return { data: [], totalPages: 1 };
        });
    }
    return onePage(1).then(function (res1) {
      var all = res1.data || [];
      var total = res1.totalPages;
      if (total <= 1) return all;
      var tasks = [];
      for (var i = 2; i <= total; i++) tasks.push(onePage(i));
      return Promise.all(tasks).then(function (rs) {
        rs.forEach(function (r) {
          all = all.concat(r.data || []);
        });
        return all;
      });
    });
  }

  function resolveRestBase(slug) {
    return fetch("/wp-json/wp/v2/types", { credentials: "same-origin" })
      .then(function (r) {
        return r.json();
      })
      .then(function (types) {
        var t = types && types[slug];
        return t && t.rest_base ? t.rest_base : slug + "s";
      })
      .catch(function () {
        return slug + "s";
      });
  }

  /* ── render ────────────────────────────────────────────────── */
  function renderResults(items, results) {
    if (!results) return;
    if (!items || !items.length) {
      results.innerHTML =
        "<p style='color:#8c8f94;padding:10px'>" +
        escHtml(t("noResults", "No results found.")) +
        "</p>";
      return;
    }
    results.innerHTML = items
      .map(function (it) {
        var title = normalizeTitle(it.title) || it.url || it.link || "";
        var url = it.url || it.link || "";
        var type = it.type || it.subtype || "";
        var chip = type
          ? '<span class="chip">' + String(type).toUpperCase() + "</span>"
          : "";
        return (
          '<div class="result">' +
          '<div class="result-info">' +
          '<div class="result-title">' +
          title +
          chip +
          "</div>" +
          '<div class="meta">' +
          url +
          "</div>" +
          "</div>" +
          '<button type="button" class="button viora-link-pick"' +
          ' data-url="' +
          url +
          '"' +
          ' data-title="' +
          title.replace(/"/g, "&quot;") +
          '">' +
          escHtml(t("select", "Select")) +
          "</button>" +
          "</div>"
        );
      })
      .join("");
  }

  /* ── data loading ──────────────────────────────────────────── */
  function loadDefault(results) {
    if (results) {
      results.innerHTML =
        "<p style='color:#8c8f94;padding:10px'>" +
        escHtml(t("loading", "Loading...")) +
        "</p>";
    }
    Promise.all([
      fetchAll("/wp-json/wp/v2/pages?_fields=title,link").then(function (l) {
        return l.map(function (d) {
          return { title: d.title, url: d.link, type: "page" };
        });
      }),
      fetchAll("/wp-json/wp/v2/posts?_fields=title,link").then(function (l) {
        return l.map(function (d) {
          return { title: d.title, url: d.link, type: "post" };
        });
      }),
      resolveRestBase("project").then(function (base) {
        return fetchAll("/wp-json/wp/v2/" + base + "?_fields=title,link")
          .then(function (l) {
            return l.map(function (d) {
              return { title: d.title, url: d.link, type: "project" };
            });
          })
          .catch(function () {
            return [];
          });
      }),
      resolveRestBase("material").then(function (base) {
        return fetchAll("/wp-json/wp/v2/" + base + "?_fields=title,link")
          .then(function (l) {
            return l.map(function (d) {
              return { title: d.title, url: d.link, type: "material" };
            });
          })
          .catch(function () {
            return [];
          });
      }),
    ])
      .then(function (groups) {
        var merged = [];
        groups.forEach(function (g) {
          merged = merged.concat(g || []);
        });
        renderResults(merged, results);
      })
      .catch(function () {
        renderResults([], results);
      });
  }

  function performSearch(q, results) {
    if (!q) {
      loadDefault(results);
      return;
    }
    fetch(
      "/wp-json/wp/v2/search?search=" + encodeURIComponent(q) + "&per_page=50",
      { credentials: "same-origin" },
    )
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        var items = (data || []).map(function (d) {
          return {
            title: d.title,
            url: d.url,
            type: d.subtype || d.type || "",
          };
        });
        renderResults(items, results);
      })
      .catch(function () {
        renderResults([], results);
      });
  }

  /* ── apply & navigate back ─────────────────────────────────── */
  function goBackToSection(sectionId) {
    if (
      window.wp &&
      wp.customize &&
      typeof wp.customize.section === "function"
    ) {
      if (sectionId) {
        var s = wp.customize.section(sectionId);
        if (s && typeof s.expand === "function") {
          s.expand();
          return;
        }
      }
    }
  }

  function applySelection(urlField, textField, targetToggle) {
    var url = urlField ? urlField.value || "" : "";
    var title = textField ? textField.value || "" : "";
    var targetBlank = targetToggle ? !!targetToggle.checked : false;
    var tgt = getLinkTarget();

    function dispatchBubbling(el, type) {
      if (!el) return;
      try {
        el.dispatchEvent(new Event(type, { bubbles: true }));
        return;
      } catch (e) {}
      try {
        // Legacy fallback
        var ev = document.createEvent("Event");
        ev.initEvent(type, true, true);
        el.dispatchEvent(ev);
      } catch (e2) {}
    }

    if (tgt) {
      var sectionId = tgt.sectionId || "";
      if (tgt.urlInput) {
        tgt.urlInput.value = url;
        dispatchBubbling(tgt.urlInput, "input");
        dispatchBubbling(tgt.urlInput, "change");
      }
      if (tgt.titleInput) {
        tgt.titleInput.value = title;
        dispatchBubbling(tgt.titleInput, "input");
        dispatchBubbling(tgt.titleInput, "change");
      }
      if (tgt.targetSelect) {
        if (tgt.targetSelect.type === "checkbox") {
          tgt.targetSelect.checked = targetBlank;
          dispatchBubbling(tgt.targetSelect, "input");
          dispatchBubbling(tgt.targetSelect, "change");
        } else {
          tgt.targetSelect.value = targetBlank ? "_blank" : "";
          dispatchBubbling(tgt.targetSelect, "change");
        }
      }
      clearLinkTarget();
      goBackToSection(sectionId);
    }
  }

  /* ── setup when elements are in DOM ────────────────────────── */
  function setup() {
    var urlField = document.getElementById("viora-link-url");
    var textField = document.getElementById("viora-link-text");
    var targetToggle = document.getElementById("viora-link-target");
    var searchField = document.getElementById("viora-link-search");
    var results = document.getElementById("viora-link-results");
    var applyBtn = document.getElementById("viora-link-apply");
    var closeBtn = document.getElementById("viora-link-close");
    var currentBox = document.getElementById("viora-link-current");
    var currentUrlEl = document.getElementById("viora-link-current-url");

    if (!urlField || !results || !applyBtn) return;
    if (urlField._blpBound) return; // already bound to this exact element
    urlField._blpBound = true;

    // If user opened Link Picker directly (no target), show a notice.
    // When opened via other sections (Choose Link), hide it.
    ensureDirectOpenNotice(!getLinkTarget());

    /* Pre-fill fields from the banner row that opened us */
    var tgt = getLinkTarget();
    if (tgt) {
      ensureDirectOpenNotice(false);
      if (tgt.currentUrl !== undefined && urlField)
        urlField.value = tgt.currentUrl;
      if (tgt.currentTitle !== undefined && textField)
        textField.value = tgt.currentTitle;
      if (tgt.currentTarget !== undefined && targetToggle)
        targetToggle.checked = tgt.currentTarget === "_blank";
      /* Show the "current link" badge */
      if (currentBox && currentUrlEl && tgt.currentUrl) {
        currentUrlEl.textContent = tgt.currentUrl;
        currentBox.classList.add("visible");
      }
    }

    /* Save original PHP-rendered HTML once so we can restore it on clear */
    var phpHtml = results.innerHTML;

    /* Search input */
    if (searchField) {
      var debounce;
      searchField.addEventListener("input", function () {
        clearTimeout(debounce);
        debounce = setTimeout(function () {
          var q = searchField.value.trim();
          if (!q) {
            /* Restore PHP list when search is cleared */
            results.innerHTML = phpHtml;
          } else {
            performSearch(q, results);
          }
        }, 250);
      });
    }

    /* Click a result row → fill fields + highlight row */
    results.addEventListener("click", function (e) {
      var btn = e.target;
      if (!btn || !btn.classList || !btn.classList.contains("viora-link-pick"))
        return;
      var url = btn.getAttribute("data-url") || "";
      var title = btn.getAttribute("data-title") || "";
      if (urlField) urlField.value = url;
      if (textField) textField.value = title;
      /* Highlight selected row */
      results.querySelectorAll(".result").forEach(function (r) {
        r.classList.remove("selected");
      });
      var row = btn.parentNode;
      if (row && row.classList) row.classList.add("selected");
    });

    /* Apply button – write back to banner and navigate back */
    applyBtn.addEventListener("click", function (e) {
      e.preventDefault();
      applySelection(urlField, textField, targetToggle);
    });

    /* Back / Close button – return without applying */
    if (closeBtn) {
      closeBtn.addEventListener("click", function (e) {
        e.preventDefault();
        var target = getLinkTarget();
        var sectionId = target ? target.sectionId || "" : "";
        clearLinkTarget();
        goBackToSection(sectionId);
      });
    }

    /* Load default list
       – If PHP already rendered items (data-initial-count > 0), keep them.
         REST API is only used when search box is used or list was empty. */
    var phpCount = parseInt(
      results.getAttribute("data-initial-count") || "0",
      10,
    );
    if (phpCount === 0) {
      loadDefault(results);
    }
  }

  /* ── bind on section expand ─────────────────────────────────── */
  function tryBind() {
    if (
      !(window.wp && wp.customize && typeof wp.customize.section === "function")
    ) {
      return false;
    }
    var sec =
      wp.customize.section("viora_link_picker_section") ||
      wp.customize.section("viora_link_picker_section");
    if (!sec || !sec.expanded || typeof sec.expanded.bind !== "function") {
      return false;
    }
    sec.expanded.bind(function (expanded) {
      if (expanded) {
        setTimeout(function () {
          /* Reset bound flag so setup can re-run, but don't wipe PHP list */
          var el = document.getElementById("viora-link-url");
          if (el) el._blpBound = false;
          setup();
        }, 80);
      }
    });
    return true;
  }

  if (!tryBind()) {
    if (window.wp && wp.customize) {
      wp.customize.bind("ready", function () {
        tryBind();
      });
    }
  }

  /* MutationObserver fallback */
  var mo = new MutationObserver(function () {
    if (document.getElementById("viora-link-url")) setup();
  });
  try {
    mo.observe(document.documentElement || document.body, {
      childList: true,
      subtree: true,
    });
  } catch (e) {}

  bindChooseLinkControls();
  setup();
})();
