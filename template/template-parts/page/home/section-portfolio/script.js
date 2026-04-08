(function () {
  var section = document.getElementById("viora-home-portfolio");
  if (!section) {
    return;
  }

  function parseIncomingData(value) {
    if (value && typeof value === "object" && !Array.isArray(value)) {
      return value;
    }

    if (typeof value === "string" && value !== "") {
      try {
        var decoded = JSON.parse(value);
        if (decoded && typeof decoded === "object" && !Array.isArray(decoded)) {
          return decoded;
        }
      } catch (e) {
        return null;
      }
    }

    return null;
  }

  function updateLivePortfolioHeader(payload) {
    var parsed = parseIncomingData(payload);
    if (!parsed) {
      return;
    }

    var title = typeof parsed.title === "string" ? parsed.title.trim() : "";
    var titleEl = section.querySelector(".viora-home-portfolio__title");
    if (titleEl && title !== "") {
      titleEl.textContent = title;
    }
  }

  function updateLivePortfolioEnabled(enabledValue) {
    var enabled =
      enabledValue === 1 || enabledValue === "1" || enabledValue === true;
    section.style.display = enabled ? "" : "none";
  }

  function bindLivePreviewBus() {
    if (!window.wp || !wp.customize) {
      return false;
    }

    var bus =
      wp.customize.preview && typeof wp.customize.preview.bind === "function"
        ? wp.customize.preview
        : typeof wp.customize.bind === "function"
          ? wp.customize
          : null;

    if (!bus || typeof bus.bind !== "function") {
      return false;
    }

    bus.bind("viora_home_portfolio_live_data", function (payload) {
      updateLivePortfolioHeader(payload);
    });

    return true;
  }

  if (!bindLivePreviewBus()) {
    var listenerRetries = 0;
    var listenerRetryLimit = 40;
    var retryTimer = window.setInterval(function () {
      listenerRetries += 1;

      if (bindLivePreviewBus() || listenerRetries >= listenerRetryLimit) {
        window.clearInterval(retryTimer);
      }
    }, 100);
  }

  if (window.wp && wp.customize && typeof wp.customize === "function") {
    wp.customize("viora_home_portfolio_data", function (value) {
      value.bind(function (newValue) {
        updateLivePortfolioHeader(newValue);
      });
    });

    wp.customize("viora_home_portfolio_enabled", function (value) {
      value.bind(function (newValue) {
        updateLivePortfolioEnabled(newValue);
      });
    });
  }

  var sliderElement = section.querySelector("[data-portfolio-slider]");
  if (sliderElement && typeof Swiper !== "undefined") {
    var rootFontSize =
      parseFloat(window.getComputedStyle(document.documentElement).fontSize) ||
      16;
    var breakpoint40Rem = Math.round(40 * rootFontSize);
    var breakpoint80Rem = Math.round(80 * rootFontSize);

    new Swiper(sliderElement, {
      slidesPerView: 4,
      spaceBetween: 24,
      loop: true,
      watchOverflow: false,
      speed: 7000,
      autoplay: {
        delay: 0,
        disableOnInteraction: false,
        pauseOnMouseEnter: false,
      },
      breakpoints: {
        0: {
          slidesPerView: 2,
          spaceBetween: 12,
        },
        [breakpoint40Rem]: {
          slidesPerView: 3,
          spaceBetween: 18,
        },
        [breakpoint80Rem]: {
          slidesPerView: 4,
          spaceBetween: 24,
        },
      },
    });
  }

  var cards = section.querySelectorAll(".viora-home-portfolio__card-link");
  if (typeof gsap !== "undefined") {
    var title = section.querySelector(".viora-home-portfolio__title");
    if (title) {
      gsap.set(title, { y: 24, autoAlpha: 0 });
    }
    if (cards.length) {
      gsap.set(cards, { y: 24, autoAlpha: 0, scale: 0.98 });
    }

    var runIntroAnimation = function () {
      var timeline = gsap.timeline();
      if (title) {
        timeline.to(title, {
          y: 0,
          autoAlpha: 1,
          duration: 0.75,
          ease: "power3.out",
        });
      }

      if (cards.length) {
        timeline.to(
          cards,
          {
            y: 0,
            autoAlpha: 1,
            scale: 1,
            duration: 0.8,
            stagger: 0.12,
            ease: "power3.out",
          },
          title ? "-=0.35" : 0,
        );
      }
    };

    if ("IntersectionObserver" in window) {
      var observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              runIntroAnimation();
              observer.disconnect();
            }
          });
        },
        { threshold: 0.25 },
      );
      observer.observe(section);
    } else {
      runIntroAnimation();
    }

    cards.forEach(function (card) {
      card.addEventListener("mousemove", function (event) {
        var rect = card.getBoundingClientRect();
        var x = (event.clientX - rect.left) / rect.width;
        var y = (event.clientY - rect.top) / rect.height;
        var rotateX = (0.5 - y) * 5;
        var rotateY = (x - 0.5) * 5;

        gsap.to(card, {
          duration: 0.25,
          overwrite: "auto",
          transformPerspective: 900,
          rotationX: rotateX,
          rotationY: rotateY,
          y: -4,
          ease: "power2.out",
        });
      });

      card.addEventListener("mouseleave", function () {
        gsap.to(card, {
          duration: 0.35,
          overwrite: "auto",
          rotationX: 0,
          rotationY: 0,
          y: 0,
          ease: "power2.out",
        });
      });
    });
  }
})();
