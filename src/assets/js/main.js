import Swup from "swup";
import SwupScrollPlugin from "@swup/scroll-plugin";
import SwupBodyClassPlugin from "@swup/body-class-plugin";

import { initMobileNav, closeMobileNav } from "./components/mobile-nav";
import { initOverlays, closeOverlays } from "./components/overlay";
import { closeContactPanel, initContactPanel } from "./components/contact-panel";
import { initTrailerOverlay } from "./components/trailer-overlay";
import { initCreatorOverlay } from "./components/creator-overlay";
import { initNewsOverlay } from "./components/news-overlay";
import { initCreatorsListPortrait } from "./components/creators-list-portrait";
import { initHeroVideo } from "./components/hero-video";
import { initHomeSnap } from "./components/home-snap";
import { initHomeFeatureParallax } from "./components/home-feature-parallax";
import { initHomeFeatureTrailerCursor } from "./components/home-feature-trailer-cursor";
import { initFeaturedQuoteCarousel } from "./components/featured-quote-carousel";
import {
  destroyProjectScroll,
  initProjectScroll,
} from "./components/project-scroll";
import {
  closeProjectAvailableOn,
  initProjectAvailableOn,
} from "./components/project-available-on";
import {
  destroyFloatingUiFooter,
  initFloatingUiFooter,
} from "./components/floating-ui-footer";
import { initWorkFilters } from "./components/work-filters";
import { destroyWorkProgressiveReveal, initWorkProgressiveReveal } from "./components/work-progressive-reveal";
import { initNewsLoadMore } from "./components/news-load-more";
import { initCookieConsent } from "./components/cookie-consent";
import { initBlurUp } from "./components/blur-up";
import {
  clearHomeIntro,
  initPreloader,
  prepareHomeIntro,
} from "./components/preloader";
import {
  syncPageBrowserChrome,
  syncPageStyle,
  syncPageThemeClasses,
} from "./components/page-style";

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
  destroyWorkProgressiveReveal();
  syncPageStyle();
  closeOverlays();
  closeContactPanel();
  closeMobileNav();
  closeProjectAvailableOn();
  initOverlays();
  initContactPanel();
  initTrailerOverlay();
  initCreatorOverlay();
  initNewsOverlay();
  initCreatorsListPortrait();
  initHeroVideo();
  initHomeSnap();
  initHomeFeatureParallax();
  initHomeFeatureTrailerCursor();
  destroyProjectScroll();
  destroyFloatingUiFooter();
  initProjectScroll();
  initFeaturedQuoteCarousel();
  initProjectAvailableOn();
  initWorkFilters();
  initFloatingUiFooter();
  initWorkProgressiveReveal();
  initNewsLoadMore();
  initBlurUp();
};

const prefersReducedMotion = () =>
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const initSwup = () => {
  const swup = new Swup({
    containers: ["#main"],
    animateHistoryBrowsing: true,
    animationSelector: '[class*="transition-fade"]',
    linkSelector: 'a[href^="' + window.location.origin + '"]:not([data-no-swup]), a[href^="/"]:not([data-no-swup])',
    plugins: [
      new SwupScrollPlugin({ animateScroll: false }),
      new SwupBodyClassPlugin({ prefix: "site-page-" }),
    ],
  });
  window.swup = swup;

  swup.hooks?.on("visit:start", (visit) => {
    if (prefersReducedMotion()) {
      visit.animation.animate = false;
    }
    clearHomeIntro();
  });

  // During pause: theme classes + bg pseudo only; defer meta/color-scheme until fade-in ends (Safari)
  swup.hooks?.on("content:replace", () => {
    syncPageThemeClasses();
    prepareHomeIntro();
  });

  // Swup already waits for CSS transitions; init on the next frame
  swup.hooks?.on("animation:in:end", () => {
    syncPageBrowserChrome();
    requestAnimationFrame(() => {
      initPreloader();
      initPage();
    });
  });

  swup.hooks?.on("animation:skip", () => {
    syncPageBrowserChrome();
    requestAnimationFrame(() => {
      initPreloader();
      initPage();
    });
  });

  swup.hooks?.on("visit:abort", clearSwupTransitionState);
};

document.addEventListener("DOMContentLoaded", () => {
  initPreloader();
  initMobileNav();
  if (document.querySelector("[data-cookie-banner]")) {
    initCookieConsent();
  }
  initPage();
  initSwup();

  window.addEventListener("pageshow", (event) => {
    if (!event.persisted) return;
    clearSwupTransitionState();
    initPage();
  });
});
