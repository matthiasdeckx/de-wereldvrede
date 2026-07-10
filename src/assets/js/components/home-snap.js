let sectionObserver = null;
let footerObserver = null;
let heroTitleClickHandler = null;
let footerScrollHandler = null;
let footerWheelHandler = null;
let headerScrollHandler = null;
let scrollAnimFrame = null;

const HEADER_SCROLL_HIDDEN_CLASS = "is-scroll-hidden";

const getFloatingHeader = () => document.querySelector("[data-floating-header]");

const shouldKeepHeaderVisible = () => {
  const header = getFloatingHeader();
  if (!header) return true;
  if (document.documentElement.classList.contains("is-home-intro-active")) return true;
  if (header.classList.contains("is-mobile-nav-open")) return true;
  if (header.classList.contains("is-overlay-mode")) return true;
  if (header.classList.contains("is-contact-open")) return true;
  if (document.body.classList.contains("is-overlay-open")) return true;
  if (document.body.classList.contains("is-mobile-nav-open")) return true;
  return false;
};

const setHeaderScrollHidden = (hidden) => {
  getFloatingHeader()?.classList.toggle(HEADER_SCROLL_HIDDEN_CLASS, hidden);
};

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

  if (footerScrollHandler) {
    const root = document.querySelector("[data-home-scroll]");
    root?.removeEventListener("scroll", footerScrollHandler);
    footerScrollHandler = null;
    root?.classList.remove("is-footer-scroll");
  }

  if (footerWheelHandler) {
    const root = document.querySelector("[data-home-scroll]");
    root?.removeEventListener("wheel", footerWheelHandler);
    footerWheelHandler = null;
  }

  if (headerScrollHandler) {
    const root = document.querySelector("[data-home-scroll]");
    root?.removeEventListener("scroll", headerScrollHandler);
    headerScrollHandler = null;
    setHeaderScrollHidden(false);
  }
};

export const initHomeSnap = () => {
  destroyHomeSnap();

  const root = document.querySelector("[data-home-scroll]");
  setupHomeLogoScroll(root);

  const indicator = document.querySelector("[data-scroll-indicator]");
  if (!root || !indicator) return;

  const sections = [...root.querySelectorAll("[data-home-section]")];
  if (!sections.length) return;

  const footer = root.querySelector(".c-home-footer");

  indicator.innerHTML = "";
  indicator.classList.remove("is-hidden");
  indicator.setAttribute("aria-hidden", "true");

  sections.forEach((_, i) => {
    const dot = document.createElement("span");
    dot.className = "c-scroll-dot" + (i === 0 ? " is-active" : "");
    indicator.appendChild(dot);
  });

  indicator.classList.add("c-scroll-dots");
  const dots = indicator.querySelectorAll(".c-scroll-dot");

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

  const hero = sections[0];
  if (hero) {
    let headerHidden = false;
    let lastHeaderScrollTop = root.scrollTop;

    const isPastHero = () => {
      const rootTop = root.getBoundingClientRect().top;
      return hero.getBoundingClientRect().bottom <= rootTop + 8;
    };

    headerScrollHandler = () => {
      if (shouldKeepHeaderVisible()) {
        if (headerHidden) {
          headerHidden = false;
          setHeaderScrollHidden(false);
        }
        lastHeaderScrollTop = root.scrollTop;
        return;
      }

      const scrollTop = root.scrollTop;
      const scrollingDown = scrollTop > lastHeaderScrollTop;
      const scrollingUp = scrollTop < lastHeaderScrollTop;
      lastHeaderScrollTop = scrollTop;

      if (!isPastHero()) {
        if (headerHidden) {
          headerHidden = false;
          setHeaderScrollHidden(false);
        }
        return;
      }

      if (scrollingDown && !headerHidden) {
        headerHidden = true;
        setHeaderScrollHidden(true);
      } else if (scrollingUp && headerHidden) {
        headerHidden = false;
        setHeaderScrollHidden(false);
      }
    };

    root.addEventListener("scroll", headerScrollHandler, { passive: true });
    headerScrollHandler();
  }

  if (!footer) return;

  const lastSection = sections[sections.length - 1];
  let footerZoneActive = false;
  let lastKnownScrollTop = root.scrollTop;

  const getLastSectionTop = () => getSectionScrollTop(root, lastSection);

  const activateFooterScroll = () => {
    if (footerZoneActive) return;
    footerZoneActive = true;
    root.classList.add("is-footer-scroll");
  };

  const deactivateFooterScroll = () => {
    if (!footerZoneActive) return;
    footerZoneActive = false;
    root.classList.remove("is-footer-scroll");
  };

  const isAtLastSection = () =>
    Math.abs(root.scrollTop - getLastSectionTop()) < 12;

  const isPastLastSection = () => root.scrollTop > getLastSectionTop() + 8;

  footerScrollHandler = () => {
    if (!lastSection) return;

    const scrollTop = root.scrollTop;
    const scrollingDown = scrollTop > lastKnownScrollTop;
    const scrollingUp = scrollTop < lastKnownScrollTop;
    lastKnownScrollTop = scrollTop;

    if (isPastLastSection()) {
      activateFooterScroll();
      return;
    }

    if (scrollingDown && isAtLastSection()) {
      activateFooterScroll();
      return;
    }

    if (scrollingUp && scrollTop <= getLastSectionTop() + 1) {
      deactivateFooterScroll();
    }
  };

  footerWheelHandler = (event) => {
    if (!lastSection) return;

    if (event.deltaY > 0 && (isAtLastSection() || footerZoneActive)) {
      activateFooterScroll();
    }

    if (event.deltaY < 0 && root.scrollTop <= getLastSectionTop() + 1) {
      deactivateFooterScroll();
    }
  };

  root.addEventListener("scroll", footerScrollHandler, { passive: true });
  root.addEventListener("wheel", footerWheelHandler, { passive: true });
  footerScrollHandler();

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
