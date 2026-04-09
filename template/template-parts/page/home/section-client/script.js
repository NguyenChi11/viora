(function () {
  var section = document.getElementById("viora-home-client");
  if (!section) {
    return;
  }

  var stage = section.querySelector("[data-client-stage]");
  var slider = section.querySelector("[data-client-slider]");
  var wrapper = section.querySelector("[data-client-wrapper]");
  var nextButton = section.querySelector("[data-client-next]");
  var prevButton = section.querySelector("[data-client-prev]");
  var pagination = section.querySelector("[data-client-pagination]");

  if (!slider || !wrapper) {
    return;
  }

  var prefersReducedMotion =
    typeof window.matchMedia === "function" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var gsap = window.gsap;
  var clientSwiper = null;
  var currentSlideTimeline = null;
  var stageAnimated = false;
  var hasRenderableItems = false;
  var sectionEnabled = true;

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

  function parseItems(value) {
    if (Array.isArray(value)) {
      return value;
    }

    if (typeof value === "string" && value !== "") {
      try {
        var decoded = JSON.parse(value);
        if (Array.isArray(decoded)) {
          return decoded;
        }
      } catch (e) {
        return [];
      }
    }

    return [];
  }

  function normalizeItems(items) {
    return items
      .map(function (item) {
        if (!item || typeof item !== "object" || Array.isArray(item)) {
          return null;
        }

        var quote = typeof item.quote === "string" ? item.quote.trim() : "";
        var name = typeof item.name === "string" ? item.name.trim() : "";
        var role = typeof item.role === "string" ? item.role.trim() : "";
        var avatar = "";
        if (typeof item.avatar === "string" && item.avatar.trim() !== "") {
          avatar = item.avatar.trim();
        } else if (
          typeof item.avatar_url === "string" &&
          item.avatar_url.trim() !== ""
        ) {
          avatar = item.avatar_url.trim();
        }

        if (quote === "" || name === "" || avatar === "") {
          return null;
        }

        return {
          quote: quote,
          name: name,
          role: role,
          avatar: avatar,
        };
      })
      .filter(function (item) {
        return !!item;
      });
  }

  function setDisplay(node, visible) {
    if (!node) {
      return;
    }

    node.style.display = visible ? "" : "none";
  }

  function syncSectionVisibility() {
    setDisplay(section, sectionEnabled && hasRenderableItems);
  }

  function destroySwiper() {
    if (clientSwiper && typeof clientSwiper.destroy === "function") {
      clientSwiper.destroy(true, true);
    }
    clientSwiper = null;
  }

  function escapeHtml(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function buildSlide(item) {
    var roleMarkup =
      item.role !== ""
        ? '<p class="viora-home-client__author-role">' +
          escapeHtml(item.role) +
          "</p>"
        : "";

    return (
      '<article class="swiper-slide viora-home-client__slide">' +
      '<figure class="viora-home-client__card" data-client-card>' +
      '<blockquote class="viora-home-client__quote" data-client-quote>"' +
      escapeHtml(item.quote) +
      '"</blockquote>' +
      '<figcaption class="viora-home-client__author" data-client-author>' +
      '<img class="viora-home-client__avatar" src="' +
      escapeHtml(item.avatar) +
      '" alt="' +
      escapeHtml(item.name) +
      '" loading="lazy" decoding="async">' +
      '<p class="viora-home-client__author-name">' +
      escapeHtml(item.name) +
      "</p>" +
      roleMarkup +
      "</figcaption>" +
      "</figure>" +
      "</article>"
    );
  }

  function animateStageIn() {
    if (stageAnimated) {
      return;
    }

    stageAnimated = true;

    if (!stage || !gsap) {
      return;
    }

    var heading = stage.querySelector(".viora-home-client__heading");
    var shell = stage.querySelector(".viora-home-client__slider-shell");
    var targets = [heading, shell].filter(function (item) {
      return !!item;
    });

    if (!targets.length) {
      return;
    }

    if (prefersReducedMotion) {
      gsap.set(targets, { autoAlpha: 1, y: 0 });
      return;
    }

    gsap.set(targets, { autoAlpha: 0, y: 24 });

    if (window.ScrollTrigger) {
      gsap.registerPlugin(window.ScrollTrigger);
      gsap.to(targets, {
        autoAlpha: 1,
        y: 0,
        duration: 0.75,
        ease: "power2.out",
        stagger: 0.12,
        scrollTrigger: {
          trigger: section,
          start: "top 80%",
          once: true,
        },
      });
      return;
    }

    gsap.to(targets, {
      autoAlpha: 1,
      y: 0,
      duration: 0.75,
      ease: "power2.out",
      stagger: 0.12,
    });
  }

  function revealActiveSlide(swiper, immediate) {
    if (!swiper || !swiper.slides || !swiper.slides.length) {
      return;
    }

    var activeSlide = swiper.slides[swiper.activeIndex];
    if (!activeSlide) {
      return;
    }

    var quote = activeSlide.querySelector("[data-client-quote]");
    var author = activeSlide.querySelector("[data-client-author]");
    if (!quote || !author) {
      return;
    }

    if (!gsap || prefersReducedMotion) {
      quote.style.opacity = "1";
      quote.style.transform = "none";
      author.style.opacity = "1";
      author.style.transform = "none";
      return;
    }

    if (currentSlideTimeline) {
      currentSlideTimeline.kill();
      currentSlideTimeline = null;
    }

    gsap.killTweensOf([quote, author]);

    if (immediate) {
      gsap.set([quote, author], {
        autoAlpha: 1,
        y: 0,
      });
      return;
    }

    currentSlideTimeline = gsap.timeline();
    currentSlideTimeline
      .fromTo(
        quote,
        {
          autoAlpha: 0.65,
          y: 10,
        },
        {
          autoAlpha: 1,
          y: 0,
          duration: 0.36,
          ease: "power2.out",
          overwrite: "auto",
        },
      )
      .fromTo(
        author,
        {
          autoAlpha: 1,
          y: 8,
        },
        {
          autoAlpha: 1,
          y: 0,
          duration: 0.34,
          ease: "power2.out",
          overwrite: "auto",
        },
        "-=0.2",
      );
  }

  function initSwiper(itemsCount) {
    destroySwiper();

    if (typeof window.Swiper === "undefined") {
      return;
    }

    clientSwiper = new window.Swiper(slider, {
      loop: itemsCount > 1,
      slidesPerView: 1,
      spaceBetween: 16,
      speed: 700,
      autoHeight: true,
      watchOverflow: true,
      navigation: {
        nextEl: nextButton,
        prevEl: prevButton,
      },
      pagination: pagination
        ? {
            el: pagination,
            clickable: true,
          }
        : undefined,
      keyboard: {
        enabled: true,
      },
      on: {
        init: function (swiper) {
          revealActiveSlide(swiper, false);
        },
        slideChangeTransitionStart: function (swiper) {
          revealActiveSlide(swiper, true);
        },
        slideChangeTransitionEnd: function (swiper) {
          revealActiveSlide(swiper, false);
        },
      },
    });
  }

  function renderTestimonials(items) {
    wrapper.innerHTML = items.map(buildSlide).join("");
    hasRenderableItems = items.length > 0;
    syncSectionVisibility();

    if (!hasRenderableItems) {
      destroySwiper();
      return;
    }

    initSwiper(items.length);
  }

  function updateHeading(data) {
    var heading =
      data && data.heading && typeof data.heading === "object"
        ? data.heading
        : {};

    var kicker =
      typeof heading.kicker === "string" ? heading.kicker.trim() : "";
    var title = typeof heading.title === "string" ? heading.title.trim() : "";
    var lede = typeof heading.lede === "string" ? heading.lede.trim() : "";

    var headingWrap = section.querySelector(".viora-home-client__heading");
    var kickerNode = section.querySelector(".viora-home-client__kicker");
    var titleNode = section.querySelector(".viora-home-client__title");
    var ledeNode = section.querySelector(".viora-home-client__lede");

    if (kickerNode) {
      kickerNode.textContent = kicker;
      setDisplay(kickerNode, kicker !== "");
    }

    if (titleNode) {
      titleNode.textContent = title;
      setDisplay(titleNode, title !== "");
    }

    if (ledeNode) {
      ledeNode.textContent = lede;
      setDisplay(ledeNode, lede !== "");
    }

    setDisplay(headingWrap, kicker !== "" || title !== "" || lede !== "");
  }

  function updateLiveClientData(payload) {
    var parsed = parseIncomingData(payload);
    if (!parsed) {
      return;
    }

    updateHeading(parsed);
    var items = normalizeItems(
      parsed.testimonials && Array.isArray(parsed.testimonials)
        ? parsed.testimonials
        : [],
    );
    renderTestimonials(items);
    animateStageIn();
  }

  function updateLiveClientEnabled(enabledValue) {
    sectionEnabled =
      enabledValue === 1 || enabledValue === "1" || enabledValue === true;
    syncSectionVisibility();
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

    bus.bind("viora_home_client_live_data", function (payload) {
      updateLiveClientData(payload);
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
    wp.customize("viora_home_client_data", function (value) {
      value.bind(function (newValue) {
        updateLiveClientData(newValue);
      });
    });

    wp.customize("viora_home_client_enabled", function (value) {
      value.bind(function (newValue) {
        updateLiveClientEnabled(newValue);
      });
    });
  }

  var rawItems = parseItems(section.getAttribute("data-client-items"));
  var testimonials = normalizeItems(rawItems);

  renderTestimonials(testimonials);
  if (!hasRenderableItems) {
    return;
  }

  animateStageIn();
})();
