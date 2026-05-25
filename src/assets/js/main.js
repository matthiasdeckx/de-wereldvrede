import Swup from "swup";
import SwupScrollPlugin from "@swup/scroll-plugin";
import SwupBodyClassPlugin from "@swup/body-class-plugin";

import { initMobileNav, closeMobileNav } from "./components/mobile-nav";
import { initOverlays, closeOverlays } from "./components/overlay";
import { initContactOverlay } from "./components/contact-overlay";
import { initTrailerOverlay } from "./components/trailer-overlay";
import { initCreatorOverlay } from "./components/creator-overlay";
import { initHeroVideo } from "./components/hero-video";
import { initHomeSnap } from "./components/home-snap";
import { initHomeFeatureParallax } from "./components/home-feature-parallax";
import { initHomeFeatureTrailerCursor } from "./components/home-feature-trailer-cursor";
import { initFeaturedQuoteCarousel } from "./components/featured-quote-carousel";
import { initProjectScroll } from "./components/project-scroll";
import { initWorkFilters } from "./components/work-filters";
import { initNewsLoadMore } from "./components/news-load-more";
import { initCookieConsent } from "./components/cookie-consent";
import { initBlurUp } from "./components/blur-up";
import { syncPageStyle } from "./components/page-style";

const SWUP_TRANSITION_CLASSES = [
  "is-changing",
  "is-animating",
  "is-leaving",
  "is-rendering",
  "is-popstate",
];

const clearSwupTransitionState = () => {
  document.documentElement.classList.remove(...SWUP_TRANSITION_CLASSES);
};

const initPage = () => {
  syncPageStyle();
  closeOverlays();
  closeMobileNav();
  initOverlays();
  initContactOverlay();
  initTrailerOverlay();
  initCreatorOverlay();
  initHeroVideo();
  initHomeSnap();
  initHomeFeatureParallax();
  initHomeFeatureTrailerCursor();
  initProjectScroll();
  initFeaturedQuoteCarousel();
  initWorkFilters();
  initNewsLoadMore();
  initBlurUp();
};

const initSwup = () => {
  const swup = new Swup({
    containers: ["#main"],
    animateHistoryBrowsing: true,
    animationSelector: "#main",
    linkSelector: 'a[href^="' + window.location.origin + '"]:not([data-no-swup]), a[href^="/"]:not([data-no-swup])',
    plugins: [
      new SwupScrollPlugin({ animateScroll: false }),
      new SwupBodyClassPlugin({ prefix: "site-page-" }),
    ],
  });
  window.swup = swup;

  swup.hooks?.on("content:replace", () => syncPageStyle());
  swup.hooks?.on("page:view", () => syncPageStyle());
  swup.hooks?.on("animation:in:end", () => {
    clearSwupTransitionState();
    requestAnimationFrame(initPage);
  });
  swup.hooks?.on("animation:skip", () => {
    clearSwupTransitionState();
    requestAnimationFrame(initPage);
  });
  swup.hooks?.on("visit:abort", clearSwupTransitionState);
  swup.hooks?.on("visit:end", clearSwupTransitionState);
};

document.addEventListener("DOMContentLoaded", () => {
  initMobileNav();
  initCookieConsent();
  initPage();
  initSwup();

  window.addEventListener("pageshow", (event) => {
    if (!event.persisted) return;
    clearSwupTransitionState();
    initPage();
  });
});
