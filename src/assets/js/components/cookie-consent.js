const STORAGE_KEY = "dw-cookie-consent";

const loadAnalytics = () => {
  const config = window.__analyticsConfig;
  if (!config) return;
  if (config.gaId) {
    const s = document.createElement("script");
    s.async = true;
    s.src = `https://www.googletagmanager.com/gtag/js?id=${config.gaId}`;
    document.head.appendChild(s);
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() { window.dataLayer.push(arguments); };
    window.gtag("js", new Date());
    window.gtag("config", config.gaId);
  }
};

export const initCookieConsent = () => {
  const banner = document.querySelector("[data-cookie-banner]");
  if (!banner) return;

  const consent = localStorage.getItem(STORAGE_KEY);
  if (consent === "accepted") {
    loadAnalytics();
    return;
  }
  if (consent === "denied") return;

  banner.hidden = false;

  banner.querySelector("[data-cookie-accept]")?.addEventListener("click", () => {
    localStorage.setItem(STORAGE_KEY, "accepted");
    banner.hidden = true;
    loadAnalytics();
  });
  banner.querySelector("[data-cookie-deny]")?.addEventListener("click", () => {
    localStorage.setItem(STORAGE_KEY, "denied");
    banner.hidden = true;
  });
};
