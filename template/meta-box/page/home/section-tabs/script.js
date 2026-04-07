(function () {
  function initTabs(root) {
    if (!root || root.dataset.tabsInit === "1") {
      return;
    }

    var tabs = root.querySelectorAll(".viora-home-admin-tab[data-target]");
    if (!tabs.length) {
      return;
    }

    var showPanel = function (panelId) {
      tabs.forEach(function (tab) {
        var isActive = tab.getAttribute("data-target") === panelId;
        tab.classList.toggle("is-active", isActive);
        tab.setAttribute("aria-selected", isActive ? "true" : "false");
      });

      var panels = root.querySelectorAll(".viora-home-admin-panel[id]");
      panels.forEach(function (panel) {
        var isActive = panel.id === panelId;
        panel.classList.toggle("is-active", isActive);
        if (isActive) {
          panel.removeAttribute("hidden");
        } else {
          panel.setAttribute("hidden", "hidden");
        }
      });
    };

    tabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        showPanel(tab.getAttribute("data-target"));
      });
    });

    showPanel(tabs[0].getAttribute("data-target"));
    root.dataset.tabsInit = "1";
  }

  function init() {
    var metabox = document.getElementById("viora_home_group");
    if (metabox) {
      initTabs(metabox);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
