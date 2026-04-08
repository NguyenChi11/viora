document.addEventListener("DOMContentLoaded", function () {
  var header = document.querySelector(".site-header");
  var toggleBtn = document.querySelector(".mobile-menu-toggle");
  var sidebar = document.getElementById("mobile-sidebar");
  var backdrop = document.querySelector(".mobile-sidebar-backdrop");
  var closeBtn = document.querySelector(".mobile-sidebar-close");
  var lastScrollY = Math.max(0, window.scrollY || 0);
  var scrollThreshold = 72;
  var directionDelta = 4;

  function onScroll() {
    if (!header) return;

    var currentScrollY = Math.max(0, window.scrollY || 0);
    var delta = currentScrollY - lastScrollY;
    var sidebarOpen = !!(sidebar && sidebar.classList.contains("open"));

    if (currentScrollY > 2) {
      header.classList.add("is-scrolled");
    } else {
      header.classList.remove("is-scrolled");
    }

    if (sidebarOpen) {
      header.classList.remove("is-hidden");
      lastScrollY = currentScrollY;
      return;
    }

    if (currentScrollY <= 2) {
      header.classList.remove("is-hidden");
      lastScrollY = currentScrollY;
      return;
    }

    if (delta > directionDelta && currentScrollY > scrollThreshold) {
      header.classList.add("is-hidden");
    } else if (delta < -directionDelta) {
      header.classList.remove("is-hidden");
    }

    lastScrollY = currentScrollY;
  }

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add("open");
    sidebar.setAttribute("aria-hidden", "false");
    if (header) {
      header.classList.remove("is-hidden");
    }
    if (backdrop) {
      backdrop.classList.add("visible");
    }
    document.documentElement.classList.add("mobile-sidebar-open");
    document.body.classList.add("mobile-sidebar-open");
    if (toggleBtn) {
      toggleBtn.setAttribute("aria-expanded", "true");
    }
  }

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove("open");
    sidebar.setAttribute("aria-hidden", "true");
    if (backdrop) {
      backdrop.classList.remove("visible");
    }
    document.documentElement.classList.remove("mobile-sidebar-open");
    document.body.classList.remove("mobile-sidebar-open");
    if (toggleBtn) {
      toggleBtn.setAttribute("aria-expanded", "false");
    }

    onScroll();
  }

  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
      if (sidebar && sidebar.classList.contains("open")) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", closeSidebar);
  }

  if (backdrop) {
    backdrop.addEventListener("click", closeSidebar);
  }

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeSidebar();
    }
  });

  if (sidebar) {
    var mobileLinks = sidebar.querySelectorAll(".mobile-navigation a");
    mobileLinks.forEach(function (link) {
      link.addEventListener("click", closeSidebar);
    });
  }

  window.addEventListener("resize", function () {
    if (window.innerWidth > 1024) {
      closeSidebar();
    }
  });
});
