(function () {
  var section = document.getElementById("viora-home-journey");
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

  function trimValue(value) {
    return typeof value === "string" ? value.trim() : "";
  }

  function setNodeDisplay(node, visible) {
    if (!node) {
      return;
    }
    node.style.display = visible ? "" : "none";
  }

  function getMediaUrl(node, keys) {
    if (!node || typeof node !== "object") {
      return "";
    }

    for (var i = 0; i < keys.length; i += 1) {
      var key = keys[i];
      var value = trimValue(node[key]);
      if (value !== "") {
        return value;
      }
    }

    return "";
  }

  function setIconImage(container, url) {
    if (!container) {
      return;
    }

    container.innerHTML = "";
    if (!url) {
      return;
    }

    var img = document.createElement("img");
    img.src = url;
    img.alt = "";
    img.loading = "lazy";
    img.decoding = "async";
    container.appendChild(img);
  }

  function updateLiveJourneyData(payload) {
    var parsed = parseIncomingData(payload);
    if (!parsed) {
      return;
    }

    var header =
      parsed.header && typeof parsed.header === "object" ? parsed.header : {};
    var cta = header.cta && typeof header.cta === "object" ? header.cta : {};

    var titleValue = trimValue(header.title);
    var ctaText =
      cta && typeof cta.text === "string" ? trimValue(cta.text) : "";
    var ctaUrl = cta && typeof cta.url === "string" ? trimValue(cta.url) : "";

    var headerNode = section.querySelector(".viora-journey__header");

    var titleNode = section.querySelector(".viora-journey__title");
    if (titleNode) {
      titleNode.textContent = titleValue;
      setNodeDisplay(titleNode, titleValue !== "");
    }

    var ctaNode = section.querySelector(".viora-journey__cta");
    if (ctaNode) {
      ctaNode.textContent = ctaText;
      if (ctaUrl !== "") {
        ctaNode.setAttribute("href", ctaUrl);
      } else {
        ctaNode.removeAttribute("href");
      }
      setNodeDisplay(ctaNode, ctaText !== "" && ctaUrl !== "");
    }

    if (headerNode) {
      setNodeDisplay(
        headerNode,
        titleValue !== "" || (ctaText !== "" && ctaUrl !== ""),
      );
    }

    if (items && items.length) {
      var layoutData =
        parsed.layout && typeof parsed.layout === "object" ? parsed.layout : {};
      var timelineData =
        layoutData.timeline && typeof layoutData.timeline === "object"
          ? layoutData.timeline
          : {};
      var timelineItems = Array.isArray(timelineData.items)
        ? timelineData.items
        : [];
      var nextActiveIndex = -1;

      items.forEach(function (item, index) {
        var itemData =
          timelineItems[index] && typeof timelineItems[index] === "object"
            ? timelineItems[index]
            : {};
        var year = trimValue(itemData.year);
        var itemTitle = trimValue(itemData.title);
        var description = trimValue(itemData.description);
        var iconUrl = getMediaUrl(itemData, ["icon_url", "icon"]);
        var hasContent =
          year !== "" ||
          itemTitle !== "" ||
          description !== "" ||
          iconUrl !== "";
        var isActive = !!itemData.isActive;

        item.setAttribute("data-year", year);
        item.setAttribute("data-title", itemTitle);
        item.setAttribute("data-description", description);

        var yearNode = item.querySelector(".viora-journey__year");
        if (yearNode) {
          yearNode.textContent = year;
          setNodeDisplay(yearNode, year !== "");
        }

        var titleNodeInItem = item.querySelector(".viora-journey__item-title");
        if (titleNodeInItem) {
          titleNodeInItem.textContent = itemTitle;
          setNodeDisplay(titleNodeInItem, itemTitle !== "");
        }

        var descNode = item.querySelector(".viora-journey__description");
        if (descNode) {
          descNode.textContent = description;
          setNodeDisplay(descNode, description !== "");
        }

        var iconWrap = item.querySelector(".viora-journey__pin-icon");
        setIconImage(iconWrap, iconUrl);

        setNodeDisplay(item, hasContent);

        if (hasContent && isActive && nextActiveIndex < 0) {
          nextActiveIndex = index;
        }
      });

      if (nextActiveIndex < 0) {
        for (var i = 0; i < items.length; i += 1) {
          if (items[i].style.display !== "none") {
            nextActiveIndex = i;
            break;
          }
        }
      }

      if (nextActiveIndex >= 0 && typeof setJourneyStep === "function") {
        setJourneyStep(nextActiveIndex, false);
      }
    }

    var visualData =
      parsed.layout &&
      parsed.layout.visual &&
      typeof parsed.layout.visual === "object"
        ? parsed.layout.visual
        : {};
    var ringsData =
      visualData.rings && typeof visualData.rings === "object"
        ? visualData.rings
        : {};

    var rocketIconUrl = getMediaUrl(visualData, [
      "rocketIcon_url",
      "rocketIcon",
    ]);
    if (rocket) {
      if (rocketIconUrl !== "") {
        setIconImage(rocket, rocketIconUrl);
        rocketIconMarkup = rocket.innerHTML;
      } else {
        rocket.innerHTML = rocketDefaultMarkup;
        rocketIconMarkup = "";
      }
    }

    var ringFirstNode = section.querySelector(
      ".viora-journey__alarm-ring--one",
    );
    var ringSecondNode = section.querySelector(
      ".viora-journey__alarm-ring--two",
    );
    var flashNode = section.querySelector(".viora-journey__alarm-flash");

    var showRingFirst =
      typeof ringsData.first === "boolean" ? ringsData.first : true;
    var showRingSecond =
      typeof ringsData.second === "boolean" ? ringsData.second : true;
    var showFlash =
      typeof visualData.flash === "boolean" ? visualData.flash : true;

    setNodeDisplay(ringFirstNode, showRingFirst);
    setNodeDisplay(ringSecondNode, showRingSecond);
    setNodeDisplay(flashNode, showFlash);

    if (visual) {
      setNodeDisplay(
        visual,
        rocketIconUrl !== "" || showRingFirst || showRingSecond || showFlash,
      );
    }

    if (typeof syncVisualHeight === "function") {
      syncVisualHeight();
    }

    if (
      window.ScrollTrigger &&
      typeof window.ScrollTrigger.refresh === "function"
    ) {
      window.ScrollTrigger.refresh();
    }
  }

  function updateLiveJourneyEnabled(enabledValue) {
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

    bus.bind("viora_home_journey_live_data", function (payload) {
      updateLiveJourneyData(payload);
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
    wp.customize("viora_home_journey_data", function (value) {
      value.bind(function (newValue) {
        updateLiveJourneyData(newValue);
      });
    });

    wp.customize("viora_home_journey_enabled", function (value) {
      value.bind(function (newValue) {
        updateLiveJourneyEnabled(newValue);
      });
    });
  }

  var timeline = section.querySelector("[data-journey-timeline]");
  var layout = section.querySelector("[data-journey-layout]");
  var visual = section.querySelector("[data-journey-visual]");
  var indicator = section.querySelector("[data-journey-indicator]");
  var progress = section.querySelector("[data-journey-progress]");
  var orb = section.querySelector("[data-journey-orb]");
  var rocket = section.querySelector("[data-journey-rocket]");
  var visualInfo = section.querySelector("[data-journey-visual-info]");
  var visualYear = section.querySelector("[data-journey-visual-year]");
  var visualTitle = section.querySelector("[data-journey-visual-title]");
  var visualDescription = section.querySelector(
    "[data-journey-visual-description]",
  );
  var title = section.querySelector(".viora-journey__title");
  var cta = section.querySelector(".viora-journey__cta");
  var items = Array.prototype.slice.call(
    section.querySelectorAll("[data-journey-item]"),
  );
  var rocketDefaultMarkup = rocket ? rocket.innerHTML : "";
  var rocketIconMarkup = "";

  if (!timeline || !indicator || !progress || !items.length) {
    return;
  }

  var activeIndex = -1;
  var prefersReducedMotion =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function syncVisualHeight() {
    if (!visual || !section) {
      return;
    }

    section.style.removeProperty("--viora-journey-visual-height");

    var visualHeight = Math.round(visual.getBoundingClientRect().height);
    if (visualHeight > 0) {
      section.style.setProperty(
        "--viora-journey-visual-height",
        visualHeight + "px",
      );
    }
  }

  function watchVisualHeight() {
    if (!visual) {
      return;
    }

    if ("ResizeObserver" in window) {
      var resizeObserver = new ResizeObserver(function () {
        syncVisualHeight();
      });
      resizeObserver.observe(visual);
    }

    window.addEventListener("load", function () {
      syncVisualHeight();
    });
  }

  function getItemPoint(index) {
    var item = items[index];
    if (!item) {
      return null;
    }
    return item.querySelector("[data-journey-point]");
  }

  function getItemVisualData(index) {
    var item = items[index];
    if (!item) {
      return null;
    }

    var iconWrap = item.querySelector(".viora-journey__pin-icon");
    var year = (item.getAttribute("data-year") || "").trim();
    var itemTitle = (item.getAttribute("data-title") || "").trim();
    var description = (item.getAttribute("data-description") || "").trim();
    var iconMarkup = iconWrap ? iconWrap.innerHTML.trim() : "";

    return {
      iconMarkup: iconMarkup,
      year: year,
      title: itemTitle,
      description: description,
    };
  }

  function renderRocketContent(data) {
    if (!rocket || !data) {
      return;
    }

    rocket.setAttribute("data-year", data.year || "");
    rocket.setAttribute("data-title", data.title || "");
    rocket.setAttribute("data-description", data.description || "");

    if (!data.iconMarkup) {
      rocket.innerHTML = rocketDefaultMarkup;
      rocketIconMarkup = "";
      return;
    }

    if (rocketIconMarkup === data.iconMarkup) {
      return;
    }

    rocket.innerHTML = data.iconMarkup;
    rocketIconMarkup = data.iconMarkup;
  }

  function renderVisualInfo(data) {
    if (!data) {
      return;
    }

    if (visualYear) {
      visualYear.textContent = data.year || "";
    }
    if (visualTitle) {
      visualTitle.textContent = data.title || "";
    }
    if (visualDescription) {
      visualDescription.textContent = data.description || "";
    }
  }

  function updateRocketFromItem(index, animate) {
    var data = getItemVisualData(index);
    if (!rocket || !data) {
      return;
    }

    var infoChanged =
      visualYear && visualYear.textContent !== (data.year || "")
        ? true
        : visualTitle && visualTitle.textContent !== (data.title || "")
          ? true
          : visualDescription &&
              visualDescription.textContent !== (data.description || "")
            ? true
            : false;
    var iconChanged = rocketIconMarkup !== data.iconMarkup;
    renderRocketContent(data);
    renderVisualInfo(data);

    if (
      animate &&
      iconChanged &&
      !prefersReducedMotion &&
      typeof window.gsap !== "undefined"
    ) {
      window.gsap.fromTo(
        rocket,
        {
          autoAlpha: 0.72,
        },
        {
          autoAlpha: 1,
          duration: 0.24,
          ease: "power2.out",
          overwrite: "auto",
        },
      );
    }

    if (
      animate &&
      infoChanged &&
      visualInfo &&
      !prefersReducedMotion &&
      typeof window.gsap !== "undefined"
    ) {
      window.gsap.fromTo(
        visualInfo,
        {
          autoAlpha: 0.78,
          y: 8,
        },
        {
          autoAlpha: 1,
          y: 0,
          duration: 0.34,
          ease: "power2.out",
          overwrite: "auto",
        },
      );
    }
  }

  function setActiveItem(index) {
    activeIndex = index;
    items.forEach(function (item, itemIndex) {
      if (itemIndex === index) {
        item.classList.add("is-active");
      } else {
        item.classList.remove("is-active");
      }
    });
  }

  function getClosestItemIndexInViewport() {
    if (!items.length) {
      return 0;
    }

    var viewportPivot = window.innerHeight * 0.5;
    var closestIndex = 0;
    var closestDistance = Number.POSITIVE_INFINITY;

    items.forEach(function (item, index) {
      var rect = item.getBoundingClientRect();
      var itemCenter = rect.top + rect.height / 2;
      var distance = Math.abs(itemCenter - viewportPivot);

      if (distance < closestDistance) {
        closestDistance = distance;
        closestIndex = index;
      }
    });

    return closestIndex;
  }

  function syncJourneyStepWithViewport(animate) {
    var index = getClosestItemIndexInViewport();
    setJourneyStep(index, animate);
  }

  function getIndicatorTargetY(index) {
    var point = getItemPoint(index);
    if (!point) {
      return 0;
    }

    var pointRect = point.getBoundingClientRect();
    var timelineRect = timeline.getBoundingClientRect();
    var pointCenter = pointRect.top - timelineRect.top + pointRect.height / 2;
    return Math.max(0, pointCenter - indicator.offsetHeight / 2);
  }

  function updateIndicator(index, animate) {
    var targetY = getIndicatorTargetY(index);
    var progressHeight = targetY + indicator.offsetHeight / 2;

    if (window.gsap && !prefersReducedMotion) {
      if (animate) {
        window.gsap.to(indicator, {
          y: targetY,
          duration: 0.42,
          ease: "power2.out",
          overwrite: "auto",
        });

        window.gsap.to(progress, {
          height: progressHeight,
          duration: 0.42,
          ease: "power2.out",
          overwrite: "auto",
        });
      } else {
        window.gsap.set(indicator, { xPercent: -50, y: targetY });
        window.gsap.set(progress, { height: progressHeight });
      }
    } else {
      indicator.style.transform = "translate(-50%, " + targetY + "px)";
      progress.style.height = progressHeight + "px";
    }
  }

  function setJourneyStep(index, animate) {
    if (index < 0) {
      index = 0;
    }
    if (index >= items.length) {
      index = items.length - 1;
    }

    if (index === activeIndex && animate) {
      return;
    }

    setActiveItem(index);
    updateIndicator(index, animate);
    updateRocketFromItem(index, animate);

    if (window.gsap && rocket && animate && !prefersReducedMotion) {
      window.gsap.fromTo(
        rocket,
        {
          scale: 0.86,
          rotation: -14,
          autoAlpha: 0.72,
        },
        {
          scale: 1,
          rotation: 0,
          autoAlpha: 1,
          duration: 0.55,
          ease: "power3.out",
          overwrite: "auto",
        },
      );
    }
  }

  function bindResizeRefresh() {
    var resizeRaf = null;

    function handleResize() {
      if (resizeRaf) {
        window.cancelAnimationFrame(resizeRaf);
      }

      resizeRaf = window.requestAnimationFrame(function () {
        syncVisualHeight();
        setJourneyStep(activeIndex, false);
        if (window.ScrollTrigger) {
          window.ScrollTrigger.refresh();
        }
      });
    }

    window.addEventListener("resize", handleResize);
  }

  syncVisualHeight();
  watchVisualHeight();
  syncJourneyStepWithViewport(false);

  if (
    typeof window.gsap === "undefined" ||
    typeof window.ScrollTrigger === "undefined"
  ) {
    bindResizeRefresh();
    return;
  }

  window.gsap.registerPlugin(window.ScrollTrigger);
  window.gsap.set(indicator, { xPercent: -50, y: 0 });

  if (!prefersReducedMotion) {
    var introStartThreshold = window.innerHeight * 0.78;
    var isPastIntroStart =
      section.getBoundingClientRect().top <= introStartThreshold;
    var revealTargets = items.map(function (item) {
      return item;
    });

    if (title) {
      revealTargets.unshift(title);
    }
    if (cta) {
      revealTargets.push(cta);
    }

    if (isPastIntroStart) {
      window.gsap.set(revealTargets, { y: 0, autoAlpha: 1 });
      if (visualInfo) {
        window.gsap.set(visualInfo, { y: 0, autoAlpha: 1 });
      }
    } else {
      window.gsap.set(revealTargets, { y: 26, autoAlpha: 0 });
      if (visualInfo) {
        window.gsap.set(visualInfo, { y: 10, autoAlpha: 0 });
      }

      var introTimeline = window.gsap.timeline({
        scrollTrigger: {
          trigger: section,
          start: "top 78%",
          once: true,
        },
      });

      if (title) {
        introTimeline.to(title, {
          y: 0,
          autoAlpha: 1,
          duration: 0.65,
          ease: "power3.out",
        });
      }

      if (cta) {
        introTimeline.to(
          cta,
          {
            y: 0,
            autoAlpha: 1,
            duration: 0.58,
            ease: "power3.out",
          },
          title ? "-=0.38" : 0,
        );
      }

      introTimeline.to(
        items,
        {
          y: 0,
          autoAlpha: 1,
          duration: 0.68,
          stagger: 0.12,
          ease: "power3.out",
        },
        "-=0.35",
      );

      if (visualInfo) {
        introTimeline.to(
          visualInfo,
          {
            y: 0,
            autoAlpha: 1,
            duration: 0.52,
            ease: "power3.out",
          },
          "-=0.32",
        );
      }
    }

    if (orb) {
      window.gsap.to(orb, {
        rotation: 360,
        transformOrigin: "50% 50%",
        duration: 24,
        ease: "none",
        repeat: -1,
      });
    }
  } else {
    if (title) {
      title.style.opacity = "1";
      title.style.transform = "none";
    }
    if (cta) {
      cta.style.opacity = "1";
      cta.style.transform = "none";
    }
    items.forEach(function (item) {
      item.style.opacity = "1";
      item.style.transform = "none";
    });
  }

  window.ScrollTrigger.create({
    trigger: timeline,
    start: "top bottom",
    end: "bottom top",
    invalidateOnRefresh: true,
    onEnter: function () {
      syncJourneyStepWithViewport(false);
    },
    onEnterBack: function () {
      syncJourneyStepWithViewport(false);
    },
    onUpdate: function () {
      syncJourneyStepWithViewport(true);
    },
  });

  syncJourneyStepWithViewport(false);

  window.ScrollTrigger.addEventListener("refresh", function () {
    syncVisualHeight();
    syncJourneyStepWithViewport(false);
  });

  window.addEventListener("load", function () {
    syncVisualHeight();
    syncJourneyStepWithViewport(false);
    window.ScrollTrigger.refresh();
  });

  window.addEventListener("pageshow", function () {
    syncVisualHeight();
    syncJourneyStepWithViewport(false);
    window.ScrollTrigger.refresh();
  });

  bindResizeRefresh();
})();
