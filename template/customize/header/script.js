(function (wp) {
  if (typeof window === "undefined") return;

  var headerI18n =
    window.vioraHeaderI18n && typeof window.vioraHeaderI18n === "object"
      ? window.vioraHeaderI18n
      : {};

  function t(key, fallback) {
    var value = headerI18n[key];
    return typeof value === "string" && value ? value : fallback;
  }

  var selectBtn = document.getElementById("select_header_logo");
  var removeBtn = document.getElementById("remove_header_logo");
  var input = document.getElementById("header_logo");
  var preview = document.getElementById("header_logo_preview");
  var frame = null;

  if (selectBtn && wp && wp.media) {
    selectBtn.addEventListener("click", function (event) {
      event.preventDefault();

      if (frame) {
        frame.open();
        return;
      }

      frame = wp.media({
        title: t("mediaTitle", "Select Header Logo"),
        button: {
          text: t("useImage", "Use Image"),
        },
        multiple: false,
      });

      frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();
        if (input) {
          input.value = attachment.id;
        }

        if (preview) {
          var imageUrl =
            attachment.sizes && attachment.sizes.thumbnail
              ? attachment.sizes.thumbnail.url
              : attachment.url;
          preview.innerHTML = '<img src="' + imageUrl + '" alt="">';
        }
      });

      frame.open();
    });
  }

  if (removeBtn) {
    removeBtn.addEventListener("click", function (event) {
      event.preventDefault();
      if (input) {
        input.value = "";
      }
      if (preview) {
        preview.innerHTML = "";
      }
    });
  }

  if (wp && wp.customize) {
    var contactState = {
      text: "",
      url: "",
      newTab: false,
    };

    function toBool(value) {
      return value === true || value === 1 || value === "1";
    }

    function applyContactState() {
      var buttons = document.querySelectorAll("a.header-contact-btn");
      Array.prototype.forEach.call(buttons, function (button) {
        if (!button.dataset.vioraDefaultHref) {
          button.dataset.vioraDefaultHref = button.getAttribute("href") || "";
        }
        if (!button.dataset.vioraDefaultText) {
          button.dataset.vioraDefaultText = button.textContent || "";
        }

        var nextHref =
          contactState.url || button.dataset.vioraDefaultHref || "";
        var nextText =
          contactState.text || button.dataset.vioraDefaultText || "Contact Now";

        button.setAttribute("href", nextHref);
        button.textContent = nextText;

        if (contactState.newTab) {
          button.setAttribute("target", "_blank");
          button.setAttribute("rel", "noopener");
        } else {
          button.removeAttribute("target");
          button.removeAttribute("rel");
        }
      });
    }

    function bindSetting(settingId, onChange) {
      try {
        wp.customize(settingId, function (value) {
          onChange(value.get());
          value.bind(onChange);
        });
      } catch (e) {}
    }

    wp.customize("viora_header_title", function (value) {
      value.bind(function (to) {
        var brand = document.querySelector(".site-brand");
        var logoWrap = document.querySelector(".site-brand__logo-wrap");
        var textNode = document.querySelector(".site-brand__text");
        if (!textNode) return;

        var nextText = String(to || "").trim();
        textNode.textContent = nextText;
        textNode.classList.toggle("is-hidden", !nextText);

        if (brand) {
          var hasLogo = !!(logoWrap && logoWrap.querySelector("img"));
          brand.classList.toggle("site-brand--hidden", !hasLogo && !nextText);
        }
      });
    });

    wp.customize("header_logo", function (value) {
      value.bind(function () {
        if (wp.customize.selectiveRefresh) {
          wp.customize.selectiveRefresh.requestFullRefresh();
        }
      });
    });

    bindSetting("viora_header_contact_link_text", function (to) {
      contactState.text = String(to || "").trim();
      applyContactState();
    });

    bindSetting("viora_header_contact_link_url", function (to) {
      contactState.url = String(to || "").trim();
      applyContactState();
    });

    bindSetting("viora_header_contact_link_new_tab", function (to) {
      contactState.newTab = toBool(to);
      applyContactState();
    });
  }
})(window.wp);
