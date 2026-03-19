(function () {
  function initAOS() {
    if (!window.AOS || typeof window.AOS.init !== "function") return;

    // Avoid horizontal scrollbars caused by initial transformed elements.
    if (document.documentElement)
      document.documentElement.classList.add("aos-enabled");
    if (document.body) document.body.classList.add("aos-enabled");

    window.AOS.init({
      once: true,
      duration: 700,
      easing: "ease-out-cubic",
      offset: 80,
    });

    // In case images/swipers change layout after init.
    window.addEventListener("load", function () {
      if (window.AOS && typeof window.AOS.refreshHard === "function") {
        window.AOS.refreshHard();
      } else if (window.AOS && typeof window.AOS.refresh === "function") {
        window.AOS.refresh();
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAOS);
  } else {
    initAOS();
  }
})();
