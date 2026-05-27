let sectionObserver = null;
let footerObserver = null;
let heroTitleClickHandler = null;
let scrollAnimFrame = null;

/** Snappy ease-out (approx. easeOutQuart / cubic-bezier(0.22, 1, 0.36, 1)) */
const easeOutSnappy = (t) => 1 - (1 - t) ** 4;

const SCROLL_DURATION_MS = 480;

const prefersReducedMotion = () =>
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const getSectionScrollTop = (root, section) =>
  root.scrollTop + section.getBoundingClientRect().top - root.getBoundingClientRect().top;

const cancelScrollAnimation = (root) => {
  if (scrollAnimFrame !== null) {
    cancelAnimationFrame(scrollAnimFrame);
    scrollAnimFrame = null;
  }
  root?.classList.remove("is-scroll-animating");
};

const scrollHomeTo = (root, targetTop) => {
  if (!root) return;

  cancelScrollAnimation(root);

  const startTop = root.scrollTop;
  const distance = targetTop - startTop;

  if (Math.abs(distance) < 1) return;

  if (prefersReducedMotion()) {
    root.scrollTop = targetTop;
    return;
  }

  root.classList.add("is-scroll-animating");

  const startTime = performance.now();

  const step = (now) => {
    const progress = Math.min((now - startTime) / SCROLL_DURATION_MS, 1);
    root.scrollTop = startTop + distance * easeOutSnappy(progress);

    if (progress < 1) {
      scrollAnimFrame = requestAnimationFrame(step);
    } else {
      root.scrollTop = targetTop;
      root.classList.remove("is-scroll-animating");
      scrollAnimFrame = null;
    }
  };

  scrollAnimFrame = requestAnimationFrame(step);
};

const scrollToSection = (root, section) => {
  if (!root || !section) return;
  scrollHomeTo(root, getSectionScrollTop(root, section));
};

export const scrollHomeToTop = (root) => {
  const scrollRoot = root ?? document.querySelector("[data-home-scroll]");
  scrollHomeTo(scrollRoot, 0);
};

const HOME_LOGO_SELECTOR = ".c-floating-header__brand, .c-site-footer__logo-link";

const normalizePathname = (url) => url.pathname.replace(/\/$/, "") || "/";

const isHomeLogoLink = (link) => {
  if (!link?.matches?.(HOME_LOGO_SELECTOR)) return false;
  const homeHref = document.querySelector('link[rel="home"]')?.href;
  if (!homeHref) return false;
  const target = new URL(link.href);
  const home = new URL(homeHref);
  return (
    target.origin === home.origin &&
    normalizePathname(target) === normalizePathname(home)
  );
};

let logoClickHandler = null;

const setupHomeLogoScroll = (root) => {
  if (logoClickHandler) {
    document.removeEventListener("click", logoClickHandler, true);
    logoClickHandler = null;
  }
  if (!root) return;

  logoClickHandler = (event) => {
    const link = event.target.closest("a");
    if (!link || !isHomeLogoLink(link)) return;
    if (!document.querySelector("[data-home-scroll]")) return;

    event.preventDefault();
    scrollHomeToTop(root);
  };

  document.addEventListener("click", logoClickHandler, true);
};

const destroyHomeSnap = () => {
  sectionObserver?.disconnect();
  footerObserver?.disconnect();
  sectionObserver = null;
  footerObserver = null;

  const heroTitle = document.querySelector(".c-home-hero__title");
  if (heroTitle && heroTitleClickHandler) {
    heroTitle.removeEventListener("click", heroTitleClickHandler);
    heroTitleClickHandler = null;
  }

  const root = document.querySelector("[data-home-scroll]");
  cancelScrollAnimation(root);

  if (logoClickHandler) {
    document.removeEventListener("click", logoClickHandler, true);
    logoClickHandler = null;
  }
};

export const initHomeSnap = () => {
  destroyHomeSnap();

  const root = document.querySelector("[data-home-scroll]");
  setupHomeLogoScroll(root);

  const indicator = document.querySelector("[data-scroll-indicator]");
  if (!root || !indicator) return;

  const allSections = [...root.querySelectorAll("[data-home-section]")];
  if (!allSections.length) return;

  const footer = root.querySelector(".c-home-footer");
  const sections = allSections.filter((section) => section !== footer);

  indicator.innerHTML = "";
  indicator.classList.remove("is-hidden");
  indicator.setAttribute("aria-hidden", "true");

  sections.forEach((_, i) => {
    const dot = document.createElement("span");
    dot.className = "c-home-scroll-indicator__dot" + (i === 0 ? " is-active" : "");
    indicator.appendChild(dot);
  });

  const dots = indicator.querySelectorAll(".c-home-scroll-indicator__dot");

  const setIndicatorHidden = (hidden) => {
    indicator.classList.toggle("is-hidden", hidden);
  };

  sectionObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const index = sections.indexOf(entry.target);
        if (index < 0) return;
        dots.forEach((dot, i) => dot.classList.toggle("is-active", i === index));
      });
    },
    { root, threshold: 0.6 }
  );
  sections.forEach((section) => sectionObserver.observe(section));

  const heroTitle = root.querySelector(".c-home-hero__title");
  const nextSection = sections[1];

  if (heroTitle && nextSection) {
    heroTitleClickHandler = (event) => {
      event.stopPropagation();
      scrollToSection(root, nextSection);
    };
    heroTitle.addEventListener("click", heroTitleClickHandler);
  }

  if (!footer) return;

  footerObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        setIndicatorHidden(entry.isIntersecting);
      });
    },
    { root, threshold: 0.35 }
  );
  footerObserver.observe(footer);
};
