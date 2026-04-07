(function () {
  var banner = document.querySelector(".viora-banner");

  function isObject(value) {
    return !!value && typeof value === "object" && !Array.isArray(value);
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

  function updateLiveBanner(data) {
    var root = document.getElementById("viora-banner");
    if (!root) {
      return;
    }

    var line1 = String(getByPath(data, "title.line1") || "").trim();
    var highlight = String(getByPath(data, "title.highlight") || "").trim();
    var line2 = String(getByPath(data, "title.line2") || "").trim();
    var hasTitle = line1 !== "" || highlight !== "" || line2 !== "";
    var titleEl = root.querySelector(".viora-banner__title");
    if (titleEl) {
      if (hasTitle) {
        var titleParts = [];
        if (line1 !== "") {
          titleParts.push(
            '<span class="viora-banner__title-part viora-banner__title-line1">' +
              escapeHtml(line1) +
              "</span>",
          );
        }
        if (highlight !== "") {
          titleParts.push(
            '<span class="viora-banner__title-part viora-banner__title-highlight">' +
              escapeHtml(highlight) +
              "</span>",
          );
        }
        if (line2 !== "") {
          titleParts.push(
            '<span class="viora-banner__title-part viora-banner__title-line2">' +
              escapeHtml(line2) +
              "</span>",
          );
        }
        titleEl.innerHTML = titleParts.join(" ");
      }
      setDisplay(titleEl, hasTitle);
    }

    var eyebrowText = String(getByPath(data, "eyebrow.text") || "").trim();
    var eyebrowIcon = String(
      getByPath(data, "eyebrow.icon_url") ||
        getByPath(data, "eyebrow.icon") ||
        "",
    ).trim();
    var eyebrowWrap = root.querySelector(".viora-banner__eyebrow");
    var eyebrowTextEl = eyebrowWrap ? eyebrowWrap.querySelector("p") : null;
    var eyebrowIconImg = eyebrowWrap
      ? eyebrowWrap.querySelector(".viora-banner__eyebrow-icon img")
      : null;
    if (eyebrowTextEl) {
      eyebrowTextEl.textContent = eyebrowText;
      setDisplay(eyebrowTextEl, eyebrowText !== "");
    }
    if (eyebrowIconImg) {
      if (eyebrowIcon !== "") {
        eyebrowIconImg.src = eyebrowIcon;
      }
      setDisplay(eyebrowIconImg.parentElement, eyebrowIcon !== "");
    }
    setDisplay(eyebrowWrap, eyebrowText !== "" || eyebrowIcon !== "");

    var description = String(getByPath(data, "description") || "").trim();
    var descriptionEl = root.querySelector(".viora-banner__description");
    if (descriptionEl) {
      descriptionEl.textContent = description;
      setDisplay(descriptionEl, description !== "");
    }

    var primaryText = String(
      getByPath(data, "actions.primary.text") || "",
    ).trim();
    var primaryUrl = String(
      getByPath(data, "actions.primary.url") || "",
    ).trim();
    var primaryIcon = String(
      getByPath(data, "actions.primary.icon_url") ||
        getByPath(data, "actions.primary.icon") ||
        "",
    ).trim();
    var primaryBtn = root.querySelector(".viora-banner__button--primary");
    if (primaryBtn) {
      var primaryLabel = primaryBtn.querySelector("span");
      var primaryIconWrap = primaryBtn.querySelector(
        ".viora-banner__button-icon",
      );
      var primaryIconImg = primaryIconWrap
        ? primaryIconWrap.querySelector("img")
        : null;

      if (primaryLabel) {
        primaryLabel.textContent = primaryText;
      }
      if (primaryUrl !== "") {
        primaryBtn.setAttribute("href", primaryUrl);
      }
      if (primaryIconImg) {
        if (primaryIcon !== "") {
          primaryIconImg.src = primaryIcon;
        }
        setDisplay(primaryIconWrap, primaryIcon !== "");
      }
      setDisplay(primaryBtn, primaryText !== "" && primaryUrl !== "");
    }

    var secondaryText = String(
      getByPath(data, "actions.secondary.text") || "",
    ).trim();
    var secondaryUrl = String(
      getByPath(data, "actions.secondary.url") || "",
    ).trim();
    var secondaryBtn = root.querySelector(".viora-banner__button--secondary");
    if (secondaryBtn) {
      secondaryBtn.textContent = secondaryText;
      if (secondaryUrl !== "") {
        secondaryBtn.setAttribute("href", secondaryUrl);
      }
      setDisplay(secondaryBtn, secondaryText !== "" && secondaryUrl !== "");
    }

    var actionsWrap = root.querySelector(".viora-banner__actions");
    if (actionsWrap) {
      var hasPrimary = primaryText !== "" && primaryUrl !== "";
      var hasSecondary = secondaryText !== "" && secondaryUrl !== "";
      setDisplay(actionsWrap, hasPrimary || hasSecondary);
    }

    var trustText = String(getByPath(data, "trust.text") || "").trim();
    var trustTextEl = root.querySelector(".viora-banner__trust p");
    if (trustTextEl) {
      trustTextEl.textContent = trustText;
      setDisplay(trustTextEl, trustText !== "");
    }

    var avatars = getByPath(data, "trust.avatars");
    var avatarsWrap = root.querySelector(".viora-banner__avatars");
    if (avatarsWrap) {
      var safeAvatars = Array.isArray(avatars)
        ? avatars
            .map(function (item) {
              return String(item || "").trim();
            })
            .filter(function (item) {
              return item !== "";
            })
        : [];

      if (safeAvatars.length > 0) {
        avatarsWrap.innerHTML = safeAvatars
          .map(function (item, index) {
            var className = "viora-banner__avatar";
            if (index === safeAvatars.length - 1) {
              className += " viora-banner__avatar--count";
            }
            return (
              '<span class="' + className + '">' + escapeHtml(item) + "</span>"
            );
          })
          .join("");
      }

      setDisplay(avatarsWrap, safeAvatars.length > 0);
    }

    var trustWrap = root.querySelector(".viora-banner__socials");
    if (trustWrap) {
      var hasAvatars = Array.isArray(avatars) && avatars.length > 0;
      setDisplay(trustWrap, hasAvatars || trustText !== "");
    }

    var laptopImage = String(
      getByPath(data, "visual.mainImage_url") ||
        getByPath(data, "visual.mainImage") ||
        "",
    ).trim();
    var laptopEl = root.querySelector(".viora-banner__laptop");
    if (laptopEl) {
      if (laptopImage !== "") {
        laptopEl.src = laptopImage;
      }
      setDisplay(laptopEl, laptopImage !== "");
    }

    var phoneImage = String(
      getByPath(data, "visual.previewImage_url") ||
        getByPath(data, "visual.previewImage") ||
        "",
    ).trim();
    var phoneWrap = root.querySelector(".viora-banner__phone");
    var phoneEl = phoneWrap ? phoneWrap.querySelector("img") : null;
    if (phoneEl) {
      if (phoneImage !== "") {
        phoneEl.src = phoneImage;
      }
      setDisplay(phoneWrap, phoneImage !== "");
    }

    var scrollHint = String(getByPath(data, "scrollHint") || "").trim();
    var scrollWrap = root.querySelector(".viora-banner__scroll");
    var scrollText = scrollWrap ? scrollWrap.querySelector("p") : null;
    if (scrollText) {
      scrollText.textContent = scrollHint;
      setDisplay(scrollWrap, scrollHint !== "");
    }
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

    bus.bind("viora_home_banner_live_data", function (payload) {
      var parsed = parseIncomingData(payload);
      if (!parsed) {
        return;
      }

      updateLiveBanner(parsed);
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
    wp.customize("viora_home_banner_data", function (value) {
      value.bind(function (newValue) {
        var parsed = parseIncomingData(newValue);
        if (!parsed) {
          return;
        }

        updateLiveBanner(parsed);
      });
    });
  }

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

    function toIfExists(target, vars, position) {
      if (!target) {
        return;
      }

      if (typeof target.length === "number" && target.length === 0) {
        return;
      }

      if (typeof position === "undefined") {
        introTimeline.to(target, vars);
        return;
      }

      introTimeline.to(target, vars, position);
    }

    toIfExists(banner.querySelector('[data-banner-anim="eyebrow"]'), {
      autoAlpha: 1,
      y: 0,
    });
    toIfExists(
      banner.querySelector('[data-banner-anim="title"]'),
      { autoAlpha: 1, y: 0 },
      "-=0.48",
    );
    toIfExists(
      banner.querySelector('[data-banner-anim="description"]'),
      { autoAlpha: 1, y: 0 },
      "-=0.46",
    );
    toIfExists(
      banner.querySelector('[data-banner-anim="actions"]'),
      { autoAlpha: 1, y: 0 },
      "-=0.48",
    );
    toIfExists(
      banner.querySelector('[data-banner-anim="socials"]'),
      { autoAlpha: 1, y: 0 },
      "-=0.45",
    );
    toIfExists(
      banner.querySelectorAll('[data-banner-anim="trust"]'),
      { autoAlpha: 1, y: 0, stagger: 0.08 },
      "-=0.45",
    );
    toIfExists(
      banner.querySelector('[data-banner-anim="visual"]'),
      { autoAlpha: 1, y: 0, x: 0 },
      "-=0.58",
    );
    toIfExists(
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
