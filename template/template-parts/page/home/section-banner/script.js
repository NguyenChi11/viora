(function () {
  var banner = document.querySelector(".viora-banner");

  if (!banner || typeof window.gsap === "undefined") {
    return;
  }

  var prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  ).matches;
  var animatedBlocks = banner.querySelectorAll("[data-banner-anim]");
  var floatBlocks = banner.querySelectorAll("[data-banner-float]");
  var parallaxBlocks = banner.querySelectorAll(
    "[data-banner-parallax], [data-banner-float]",
  );
  var scrollDot = banner.querySelector(".viora-banner__scroll-dot");

  if (!prefersReducedMotion) {
    window.gsap.set(animatedBlocks, {
      autoAlpha: 0,
      y: 36,
    });

    var introTimeline = window.gsap.timeline({
      defaults: {
        ease: "power3.out",
        duration: 0.78,
      },
    });

    introTimeline
      .to(banner.querySelector('[data-banner-anim="eyebrow"]'), {
        autoAlpha: 1,
        y: 0,
      })
      .to(
        banner.querySelector('[data-banner-anim="title"]'),
        { autoAlpha: 1, y: 0 },
        "-=0.48",
      )
      .to(
        banner.querySelector('[data-banner-anim="description"]'),
        { autoAlpha: 1, y: 0 },
        "-=0.46",
      )
      .to(
        banner.querySelector('[data-banner-anim="actions"]'),
        { autoAlpha: 1, y: 0 },
        "-=0.48",
      )
      .to(
        banner.querySelectorAll('[data-banner-anim="trust"]'),
        { autoAlpha: 1, y: 0, stagger: 0.08 },
        "-=0.45",
      )
      .to(
        banner.querySelector('[data-banner-anim="visual"]'),
        { autoAlpha: 1, y: 0, x: 0 },
        "-=0.58",
      )
      .to(
        banner.querySelector('[data-banner-anim="scroll"]'),
        { autoAlpha: 1, y: 0 },
        "-=0.42",
      );

    floatBlocks.forEach(function (item, index) {
      window.gsap.to(item, {
        y: -10 - index * 2,
        duration: 2.2 + index * 0.45,
        repeat: -1,
        yoyo: true,
        ease: "sine.inOut",
      });
    });

    if (scrollDot) {
      window.gsap.to(scrollDot, {
        y: 12,
        opacity: 0.2,
        repeat: -1,
        yoyo: true,
        duration: 1.1,
        ease: "power1.inOut",
      });
    }
  } else {
    window.gsap.set(animatedBlocks, {
      autoAlpha: 1,
      y: 0,
      clearProps: "all",
    });
  }

  if (
    window.matchMedia("(min-width: 992px)").matches &&
    !prefersReducedMotion
  ) {
    banner.addEventListener("mousemove", function (event) {
      var bounds = banner.getBoundingClientRect();
      var pointerX = (event.clientX - bounds.left) / bounds.width - 0.5;
      var pointerY = (event.clientY - bounds.top) / bounds.height - 0.5;

      parallaxBlocks.forEach(function (item) {
        var depth = Number(item.getAttribute("data-depth") || 12);
        window.gsap.to(item, {
          x: pointerX * depth,
          y: pointerY * depth,
          duration: 0.75,
          ease: "power2.out",
          overwrite: "auto",
        });
      });
    });

    banner.addEventListener("mouseleave", function () {
      parallaxBlocks.forEach(function (item) {
        window.gsap.to(item, {
          x: 0,
          y: 0,
          duration: 0.85,
          ease: "power2.out",
          overwrite: "auto",
        });
      });
    });
  }
})();
