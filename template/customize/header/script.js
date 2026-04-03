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
    wp.customize("viora_header_title", function (value) {
      value.bind(function (to) {
        var textNode = document.querySelector(".site-brand__text");
        if (!textNode) return;

        var nextText = String(to || "").trim();
        if (!nextText || nextText === "0" || nextText === "1") {
          nextText =
            window.headerData && window.headerData.title
              ? window.headerData.title
              : "";
        }

        if (!nextText) {
          nextText = document.title || "";
        }

        textNode.textContent = nextText;
      });
    });

    wp.customize("header_logo", function (value) {
      value.bind(function () {
        if (wp.customize.selectiveRefresh) {
          wp.customize.selectiveRefresh.requestFullRefresh();
        }
      });
    });
  }
})(window.wp);
