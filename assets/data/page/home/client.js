const homeClientData = {
  enabled: true,
  heading: {
    kicker: "Loved by teams worldwide",
    title: "Client Success Stories",
    lede: "Trusted by founders, product teams, and global brands to launch premium digital experiences.",
  },
  testimonials: [
    {
      quote:
        "Nexus transformed our digital presence completely. Their attention to detail in the UI design and the seamless performance of our new site exceeded all our expectations.",
      name: "Sarah Jenkins",
      role: "CTO, Global Tech Solutions",
      avatar: "/wp-content/themes/Viora/assets/images/avatar_1.jpg",
    },
    {
      quote:
        "From brand strategy to launch, the team helped us move faster and deliver a cleaner customer experience across every touchpoint.",
      name: "Daniel Harper",
      role: "Founder, Nova Commerce",
      avatar: "/wp-content/themes/Viora/assets/images/avatar_2.jpg",
    },
    {
      quote:
        "Communication was clear, the process was structured, and the final product felt premium from day one. Highly recommended.",
      name: "Emily Carter",
      role: "Marketing Director, Lumina Studio",
      avatar: "/wp-content/themes/Viora/assets/images/avatar_3.jpg",
    },
    {
      quote:
        "We needed a partner that understood both UX and performance. The result was a website that looks sharp and converts better.",
      name: "Michael Ross",
      role: "Product Lead, Axion Labs",
      avatar: "/wp-content/themes/Viora/assets/images/avatar_4.jpg",
    },
  ],
};

if (typeof window !== "undefined") {
  window.homeClientData = homeClientData;
  window.vioraHomeClientData = homeClientData;
}
