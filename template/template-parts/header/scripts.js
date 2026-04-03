document.addEventListener("DOMContentLoaded", function () {
  var header = document.querySelector(".site-header");
  var toggleBtn = document.querySelector(".mobile-menu-toggle");
  var sidebar = document.getElementById("mobile-sidebar");
  var backdrop = document.querySelector(".mobile-sidebar-backdrop");
  var closeBtn = document.querySelector(".mobile-sidebar-close");

  function onScroll() {
    if (!header) return;
    if (window.scrollY > 2) {
      header.classList.add("is-scrolled");
    } else {
      header.classList.remove("is-scrolled");
    }
  }

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add("open");
    sidebar.setAttribute("aria-hidden", "false");
    if (backdrop) {
      backdrop.classList.add("visible");
    }
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
    document.body.classList.remove("mobile-sidebar-open");
    if (toggleBtn) {
      toggleBtn.setAttribute("aria-expanded", "false");
    }
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
