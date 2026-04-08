const homeJourneyData = {
  enabled: true,
  header: {
    title: "Our Journey",
    cta: {
      text: "Explore about us",
      url: "/about-us",
    },
  },
  layout: {
    timeline: {
      items: [
        {
          year: "2018",
          title: "The Genesis",
          description:
            "Founded with a vision to merge art and technology. We started as a small team of 3 dreamers in a tiny studio.",
          icon: "/wp-content/themes/Viora/assets/images/icon/service_1.png",
          isActive: true,
        },
        {
          year: "2020",
          title: "Global Expansion",
          description:
            "Scaled our operations globally, partnering with Fortune 500 companies and winning our first international design awards.",
          icon: "/wp-content/themes/Viora/assets/images/icon/service_2.png",
          isActive: false,
        },
        {
          year: "2025",
          title: "Innovation Hub",
          description:
            "Pioneering AI-driven user experiences and sustainable digital ecosystems for the next generation of the web.",
          icon: "/wp-content/themes/Viora/assets/images/icon/service_3.png",
          isActive: false,
        },
        {
          year: "2028",
          title: "The Genesis",
          description:
            "Founded with a vision to merge art and technology. We started as a small team of 3 dreamers in a tiny studio.",
          icon: "/wp-content/themes/Viora/assets/images/icon/service_1.png",
          isActive: true,
        },
        {
          year: "2030",
          title: "The Genesis",
          description:
            "Founded with a vision to merge art and technology. We started as a small team of 3 dreamers in a tiny studio.",
          icon: "/wp-content/themes/Viora/assets/images/icon/service_1.png",
          isActive: true,
        },
      ],
    },
    visual: {
      rocketIcon: "/wp-content/themes/Viora/assets/images/icon/service_1.png",
      rings: {
        first: true,
        second: true,
      },
      flash: true,
    },
  },
};

if (typeof window !== "undefined") {
  window.homeJourneyData = homeJourneyData;
  window.vioraHomeJourneyData = homeJourneyData;
}
