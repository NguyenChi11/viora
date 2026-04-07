const homeBannerData = {
  enabled: true,
  eyebrow: {
    icon: "/wp-content/themes/Viora/assets/images/icon/icon_1.png",
    text: "Award Winning Agency",
  },
  title: {
    line1: "Crafting",
    highlight: "Digital",
    line2: "Futures",
  },
  description:
    "Elevating brands through premium UI/UX design, web development, and strategic branding services. We turn complex ideas into seamless digital realities.",
  actions: {
    primary: {
      text: "View Portfolio",
      url: "/projects",
      icon: "/wp-content/themes/Viora/assets/images/icon/icon_2.png",
    },
    secondary: {
      text: "Book Consultation",
      url: "/contact",
    },
  },
  trust: {
    avatars: ["S", "M", "K", "50+"],
    text: "Trusted by leading tech companies worldwide",
  },
  visual: {
    mainImage: "/wp-content/themes/Viora/assets/images/image_banner.png",
    previewImage:
      "/wp-content/themes/Viora/assets/images/icon/image_banner_preview.png",
    stats: [
      {
        icon: "/wp-content/themes/Viora/assets/images/icon/icon_3.png",
        value: "150+",
        label: "Projects Done",
      },
      {
        icon: "/wp-content/themes/Viora/assets/images/icon/icon_4.png",
        value: "10+",
        label: "Years Exp.",
      },
    ],
  },
  scrollHint: "Scroll to Explore",
};

if (typeof window !== "undefined") {
  window.homeBannerData = homeBannerData;
  window.vioraHomeBannerData = homeBannerData;
}
