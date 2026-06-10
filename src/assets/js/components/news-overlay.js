import { openOverlay } from "./overlay";
import { initBlurUp } from "./blur-up";
import { initFeaturedQuoteCarousel } from "./featured-quote-carousel";

let newsOverlayListenersBound = false;

const getPanel = () => document.querySelector("[data-news-panel]");

const buildOverlayUrl = (url) => {
  const target = new URL(url, window.location.origin);
  target.searchParams.set("overlay", "1");
  return target.toString();
};

const initOverlayContent = (panel) => {
  initBlurUp(panel);
  initFeaturedQuoteCarousel();
};

export const clearNewsOverlay = () => {
  const panel = getPanel();
  if (!panel) return;

  panel.innerHTML = "";
  panel.removeAttribute("aria-labelledby");
};

export const openNewsOverlay = async (url) => {
  const panel = getPanel();
  if (!panel || !url) return;

  clearNewsOverlay();

  try {
    const response = await fetch(buildOverlayUrl(url), {
      headers: { Accept: "text/html" },
    });

    if (!response.ok) {
      window.location.href = url;
      return;
    }

    const html = (await response.text()).trim();
    if (!html) {
      window.location.href = url;
      return;
    }

    panel.innerHTML = html;
    panel.setAttribute("aria-labelledby", "news-article-title");
    initOverlayContent(panel);
    openOverlay("news");
  } catch {
    window.location.href = url;
  }
};

export const initNewsOverlay = () => {
  if (newsOverlayListenersBound) return;
  newsOverlayListenersBound = true;

  document.addEventListener("click", (event) => {
    const link = event.target.closest("[data-news-open]");
    if (!link) return;

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
      return;
    }

    event.preventDefault();
    openNewsOverlay(link.href);
  });

  document.querySelector('[data-overlay="news"]')?.addEventListener("click", (event) => {
    if (event.target.closest("[data-overlay-close]")) {
      clearNewsOverlay();
    }
  });
};
