(function () {
  var section = document.querySelector(".viora-service");
  if (!section) {
    return;
  }

  var wrapper = section.querySelector("[data-service-wrapper]");
  var slider = section.querySelector("[data-service-slider]");
  var pagination = section.querySelector(".viora-service__pagination");
  var eyebrow = section.querySelector(".viora-service__eyebrow");
  var title = section.querySelector(".viora-service__title");
  var prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
  ).matches;
  var sliderInstance = null;

  function isObject(value) {
    return !!value && typeof value === "object" && !Array.isArray(value);
  }

  function cloneState(value) {
    try {
      return JSON.parse(JSON.stringify(value));
    } catch (e) {
      return null;
    }
  }

  function normalizeItems(items) {
    if (!Array.isArray(items)) {
      return [];
    }

    return items
      .map(function (item) {
        if (!isObject(item)) {
          return null;
        }

        var safeFeatures = Array.isArray(item.features)
          ? item.features
              .map(function (feature) {
                return String(feature || "").trim();
              })
              .filter(function (feature) {
                return feature !== "";
              })
          : [];

        return {
          iconImage:
            typeof item.iconImage_url === "string" && item.iconImage_url !== ""
              ? item.iconImage_url
              : typeof item.iconImage === "string"
                ? item.iconImage
                : "",
          title: typeof item.title === "string" ? item.title : "",
          description:
            typeof item.description === "string" ? item.description : "",
          features: safeFeatures,
        };
      })
      .filter(function (item) {
        return (
          item &&
          (item.title !== "" ||
            item.description !== "" ||
            item.iconImage !== "" ||
            item.features.length > 0)
        );
      });
  }

  function normalizeState(rawState) {
    var state = isObject(rawState) ? cloneState(rawState) : {};
    if (!state) {
      state = {};
    }

    var fallbackItems = Array.isArray(window.vioraHomeServicesData)
      ? window.vioraHomeServicesData
      : [];
    var itemsSource = Array.isArray(state.items) ? state.items : fallbackItems;

    return {
      enabled: state.enabled !== false,
      eyebrow:
        typeof state.eyebrow === "string"
          ? state.eyebrow
          : eyebrow
            ? eyebrow.textContent || ""
            : "",
      title:
        typeof state.title === "string"
          ? state.title
          : title
            ? title.textContent || ""
            : "",
      items: normalizeItems(itemsSource),
    };
  }

  var servicesState = normalizeState(window.vioraHomeServicesState || {});

  function escapeHtml(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function setDisplay(el, visible) {
    if (!el) {
      return;
    }
    el.style.display = visible ? "" : "none";
  }

  function renderServiceIcon() {
    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.75a.75.75 0 0 1 .75.75v2.1l1.6-.93a.75.75 0 0 1 1.02.28l1.25 2.17a.75.75 0 0 1-.27 1.02l-1.61.93 1.61.93a.75.75 0 0 1 .27 1.02l-1.25 2.17a.75.75 0 0 1-1.02.28l-1.6-.93v1.85h.76a4.75 4.75 0 1 1 0 9.5h-3.52a4.75 4.75 0 1 1 0-9.5h.76v-1.85l-1.6.93a.75.75 0 0 1-1.02-.28L5.88 11a.75.75 0 0 1 .27-1.02l1.61-.93-1.6-.93a.75.75 0 0 1-.28-1.02l1.25-2.17a.75.75 0 0 1 1.02-.28l1.6.93V3.5a.75.75 0 0 1 .75-.75Zm-3 14.14a3.25 3.25 0 1 0 0 6.5h3.52a3.25 3.25 0 1 0 0-6.5H9Z" fill="currentColor"></path></svg>';
  }

  function renderTickIcon() {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M320 576C178.6 576 64 461.4 64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576zM320 112C205.1 112 112 205.1 112 320C112 434.9 205.1 528 320 528C434.9 528 528 434.9 528 320C528 205.1 434.9 112 320 112zM390.7 233.9C398.5 223.2 413.5 220.8 424.2 228.6C434.9 236.4 437.3 251.4 429.5 262.1L307.4 430.1C303.3 435.8 296.9 439.4 289.9 439.9C282.9 440.4 276 437.9 271.1 433L215.2 377.1C205.8 367.7 205.8 352.5 215.2 343.2C224.6 333.9 239.8 333.8 249.1 343.2L285.1 379.2L390.7 234z"></path></svg>';
  }

  function buildCardMarkup(card) {
    var features = Array.isArray(card.features) ? card.features : [];
    var iconImage = typeof card.iconImage === "string" ? card.iconImage : "";
    var iconMarkup =
      iconImage !== ""
        ? '<img src="' +
          escapeHtml(iconImage) +
          '" alt="" loading="lazy" decoding="async">'
        : renderServiceIcon();
    var iconClass =
      iconImage !== ""
        ? "viora-service__icon viora-service__icon--image"
        : "viora-service__icon";

    return (
      '<article class="swiper-slide viora-service__card" data-service-card>' +
      '<span class="' +
      iconClass +
      '" aria-hidden="true">' +
      iconMarkup +
      "</span>" +
      '<h3 class="viora-service__card-title">' +
      escapeHtml(card.title) +
      "</h3>" +
      '<p class="viora-service__card-description">' +
      escapeHtml(card.description) +
      "</p>" +
      '<ul class="viora-service__feature-list">' +
      features
        .map(function (feature) {
          return (
            '<li class="viora-service__feature-item">' +
            '<span class="viora-service__feature-icon" aria-hidden="true">' +
            renderTickIcon() +
            "</span>" +
            "<span>" +
            escapeHtml(feature) +
            "</span>" +
            "</li>"
          );
        })
        .join("") +
      "</ul>" +
      "</article>"
    );
  }

  function bindCardHover(cards) {
    if (typeof window.gsap === "undefined" || prefersReducedMotion) {
      return;
    }

    cards.forEach(function (card) {
      card.addEventListener("mouseenter", function () {
        window.gsap.to(card, {
          y: -7,
          duration: 0.24,
          ease: "power2.out",
        });
      });

      card.addEventListener("mouseleave", function () {
        window.gsap.to(card, {
          y: 0,
          duration: 0.22,
          ease: "power2.out",
        });
      });
    });
  }

  function initSlider() {
    if (!slider || typeof window.Swiper === "undefined") {
      return;
    }

    var cards = section.querySelectorAll("[data-service-card]");
    if (!cards.length) {
      return;
    }

    if (sliderInstance && typeof sliderInstance.destroy === "function") {
      sliderInstance.destroy(true, true);
      sliderInstance = null;
    }

    sliderInstance = new window.Swiper(slider, {
      loop: true,
      slidesPerView: 3,
      spaceBetween: 24,
      speed: 560,
      autoplay: {
        delay: 3200,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      watchOverflow: true,
      grabCursor: true,
      pagination: {
        el: pagination,
        clickable: true,
      },
      breakpoints: {
        0: {
          slidesPerView: 1,
          spaceBetween: 16,
        },
        640: {
          slidesPerView: 2,
          spaceBetween: 18,
        },
        1024: {
          slidesPerView: 3,
          spaceBetween: 24,
        },
      },
    });

    bindCardHover(Array.prototype.slice.call(cards));
  }

  function renderFromState(state) {
    var normalized = normalizeState(state);
    servicesState = normalized;

    if (eyebrow) {
      eyebrow.textContent = normalized.eyebrow;
      setDisplay(eyebrow, normalized.eyebrow.trim() !== "");
    }

    if (title) {
      title.textContent = normalized.title;
      setDisplay(title, normalized.title.trim() !== "");
    }

    if (!wrapper) {
      return;
    }

    if (!normalized.enabled || !normalized.items.length) {
      wrapper.innerHTML = "";
      if (sliderInstance && typeof sliderInstance.destroy === "function") {
        sliderInstance.destroy(true, true);
        sliderInstance = null;
      }
      setDisplay(section, false);
      return;
    }

    setDisplay(section, true);
    wrapper.innerHTML = normalized.items.map(buildCardMarkup).join("");
    initSlider();
  }

  function parseIncomingData(value) {
    if (isObject(value)) {
      return value;
    }

    if (typeof value === "string" && value !== "") {
      try {
        var decoded = JSON.parse(value);
        return isObject(decoded) ? decoded : null;
      } catch (e) {
        return null;
      }
    }

    return null;
  }

  function bindLiveDataListener() {
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

    bus.bind("viora_home_services_live_data", function (payload) {
      var parsed = parseIncomingData(payload);
      if (!parsed) {
        return;
      }

      var merged = cloneState(servicesState) || {};
      Object.keys(parsed).forEach(function (key) {
        merged[key] = parsed[key];
      });
      renderFromState(merged);
    });

    return true;
  }

  if (!bindLiveDataListener()) {
    var listenerRetries = 0;
    var listenerRetryLimit = 40;
    var retryTimer = window.setInterval(function () {
      listenerRetries += 1;

      if (bindLiveDataListener() || listenerRetries >= listenerRetryLimit) {
        window.clearInterval(retryTimer);
      }
    }, 100);
  }

  if (window.wp && wp.customize && typeof wp.customize === "function") {
    wp.customize("viora_home_services_data", function (value) {
      value.bind(function (newValue) {
        var parsed = parseIncomingData(newValue);
        if (!parsed) {
          return;
        }

        var merged = cloneState(servicesState) || {};
        Object.keys(parsed).forEach(function (key) {
          merged[key] = parsed[key];
        });
        renderFromState(merged);
      });
    });

    wp.customize("viora_home_services_enabled", function (value) {
      value.bind(function (newValue) {
        var merged = cloneState(servicesState) || {};
        merged.enabled = Number(newValue) === 1;
        renderFromState(merged);
      });
    });
  }

  renderFromState(servicesState);

  function runReveal() {
    if (typeof window.gsap === "undefined" || prefersReducedMotion) {
      return;
    }

    var cards = section.querySelectorAll("[data-service-card]");

    if (eyebrow) {
      window.gsap.fromTo(
        eyebrow,
        { y: 20, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.45, ease: "power2.out" },
      );
    }

    if (title) {
      window.gsap.fromTo(
        title,
        { y: 30, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.58, delay: 0.08, ease: "power2.out" },
      );
    }

    if (cards.length) {
      window.gsap.fromTo(
        cards,
        { y: 28, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.48,
          delay: 0.16,
          stagger: 0.08,
          ease: "power2.out",
        },
      );
    }
  }

  if ("IntersectionObserver" in window) {
    var hasPlayed = false;
    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting || hasPlayed) {
            return;
          }

          hasPlayed = true;
          runReveal();
          observer.disconnect();
        });
      },
      { threshold: 0.2 },
    );

    observer.observe(section);
  } else {
    runReveal();
  }
})();
