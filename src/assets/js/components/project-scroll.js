/** CSS ease (cubic-bezier(0.25, 0.1, 0.25, 1)) */
const easeAnim = (() => {
  const mX1 = 0.25;
  const mY1 = 0.1;
  const mX2 = 0.25;
  const mY2 = 1;

  const sampleValues = [0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1].map(
    (t) => ((1 - 3 * mX2 + 3 * mX1) * t + (3 * mX2 - 6 * mX1)) * t * t + 3 * mX1 * t,
  );

  const getTForX = (x) => {
    let start = 0;
    for (let i = 1; i < sampleValues.length; i += 1) {
      if (sampleValues[i] >= x) {
        const range = sampleValues[i] - sampleValues[i - 1];
        start = (i - 1 + (range ? (x - sampleValues[i - 1]) / range : 0)) * 0.1;
        break;
      }
    }
    return start;
  };

  return (t) => {
    const x = getTForX(t);
    return ((1 - 3 * mY2 + 3 * mY1) * x + (3 * mY2 - 6 * mY1)) * x * x + 3 * mY1 * x;
  };
})();

/** --anim-speed-slow (0.6s) × 1.5 */
const VIEW_MORE_SCROLL_MS = 240;

/**
 * Hero scroll effect timing (progress = scrollY / viewport height).
 *
 * IMAGE_EFFECT_START / IMAGE_EFFECT_END — range for hero background blur, scale,
 * and background opacity.
 *
 * CONTENT_FADE_START / CONTENT_FADE_END — range for fading hero content elements.
 * On desktop, logo/title are excluded and fade separately near content end.
 * On stacked/mobile, logo/title fade with the rest of the hero content.
 *
 * TITLE_FADE_BEFORE_END — px before the content column bottom reaches the
 * fixed hero title when the title/logo should fade out.
 *
 * MAX_BLUR, SCALE_START, SCALE_END — image effect output at full progress.
 */
const IMAGE_EFFECT_START = 0.25;
const IMAGE_EFFECT_END = 1;
const CONTENT_FADE_START = 0.25;
const CONTENT_FADE_END = 0.45;
const TITLE_FADE_BEFORE_END = 200;
const MAX_BLUR = 64;
const SCALE_START = 1;
const SCALE_END = 1.12;

const getScrollY = () =>
  window.pageYOffset ||
  document.documentElement.scrollTop ||
  document.body.scrollTop ||
  0;

const getViewportHeight = () =>
  window.visualViewport?.height ?? window.innerHeight;

const scrollEffectProgress = (rawProgress, start, end) =>
  rawProgress <= start ? 0 : Math.min((rawProgress - start) / (end - start), 1);

const measureHeroTitleBottom = (hero) => {
  const titleEl = hero?.querySelector(
    ".c-hero-feature__logo, .c-hero-feature__title",
  );

  return titleEl?.getBoundingClientRect().bottom ?? getViewportHeight() * 0.5;
};

/**
 * Fixed hero title: compare scrolling content bottom to the title’s screen
 * position (not the viewport bottom — the title does not move on scroll).
 */
const shouldFadeHeroTitle = (
  detailContent,
  cachedTitleBottom,
  scrollY,
  heroHeight,
) => {
  if (!detailContent) return false;
  if (scrollY < heroHeight * 0.15) return false;

  const endMarker =
    detailContent.querySelector("[data-project-detail-sentinel]") ??
    detailContent;
  const contentBottom = endMarker.getBoundingClientRect().bottom;
  const fadeLine = cachedTitleBottom + TITLE_FADE_BEFORE_END;

  return contentBottom <= fadeLine;
};

let teardown = null;

export const destroyProjectScroll = () => {
  teardown?.();
  teardown = null;
};

export const initProjectScroll = () => {
  destroyProjectScroll();

  const root = document.querySelector("[data-project-scroll]");
  const hero = document.querySelector("[data-project-hero]");
  const bg = hero?.querySelector(".c-hero-feature__bg, .c-project-hero__bg");
  const heroContent = hero?.querySelector(".c-hero-feature__content");
  const heroFadeTargetsDesktop = heroContent
    ? heroContent.querySelectorAll(
        ":scope > :not(.c-hero-feature__logo, .c-hero-feature__title)",
      )
    : null;
  const scrollBtn = document.querySelector("[data-project-scroll-down]");
  const detail = document.querySelector("[data-project-detail]");
  const detailContent = detail?.querySelector(".c-project-detail__content");
  const titleEls = hero?.querySelectorAll(
    ".c-hero-feature__logo, .c-hero-feature__title",
  );
  if (!root || !hero) return;

  const motionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
  const desktopQuery = window.matchMedia("(min-width: 1024px)");
  let reducedMotion = motionQuery.matches;
  let scrollAnimFrame = null;
  let cachedTitleBottom = measureHeroTitleBottom(hero);
  let titleFadeState = null;

  const refreshTitleBottomCache = () => {
    cachedTitleBottom = measureHeroTitleBottom(hero);
  };

  const syncTitleFade = (shouldFade) => {
    if (titleFadeState === shouldFade) return;

    titleFadeState = shouldFade;
    hero.classList.toggle("is-hero-title-faded", shouldFade);

    titleEls?.forEach((el) => {
      el.classList.toggle("is-hero-title-faded", shouldFade);
      if (shouldFade) {
        el.setAttribute("aria-hidden", "true");
      } else {
        el.removeAttribute("aria-hidden");
      }
    });
  };

  const cancelScrollAnimation = () => {
    if (scrollAnimFrame !== null) {
      cancelAnimationFrame(scrollAnimFrame);
      scrollAnimFrame = null;
    }
  };

  const animateWindowScrollTo = (targetTop) => {
    cancelScrollAnimation();

    const startTop = getScrollY();
    const distance = targetTop - startTop;

    if (Math.abs(distance) < 1) return;

    if (reducedMotion) {
      window.scrollTo(0, targetTop);
      return;
    }

    const startTime = performance.now();

    const step = (now) => {
      const progress = Math.min((now - startTime) / VIEW_MORE_SCROLL_MS, 1);
      window.scrollTo(0, startTop + distance * easeAnim(progress));

      if (progress < 1) {
        scrollAnimFrame = requestAnimationFrame(step);
      } else {
        window.scrollTo(0, targetTop);
        scrollAnimFrame = null;
      }
    };

    scrollAnimFrame = requestAnimationFrame(step);
  };

  const getScrollAlignTarget = () => {
    const titleEl = hero.querySelector(
      ".c-hero-feature__logo, .c-hero-feature__title",
    );
    const content = detail?.querySelector(".c-project-detail__content");
    const textEl =
      content?.querySelector(".c-project-detail__synopsis") ??
      content?.querySelector(":scope > :first-child");

    if (!titleEl || !textEl) return null;

    const titleTop = titleEl.getBoundingClientRect().top;
    const textTop = textEl.getBoundingClientRect().top;

    return getScrollY() + textTop - titleTop;
  };

  scrollBtn?.addEventListener("click", () => {
    if (desktopQuery.matches) {
      const scrollTarget = getScrollAlignTarget();
      if (scrollTarget !== null) {
        animateWindowScrollTo(scrollTarget);
        return;
      }
    }

    if (detail) {
      animateWindowScrollTo(getScrollY() + detail.getBoundingClientRect().top);
    }
  });

  let ticking = false;

  const update = () => {
    const vh = getViewportHeight();
    const scrollY = getScrollY();
    const heroHeight = hero.getBoundingClientRect().height;
    const rawProgress = scrollY / vh;
    const imageEffectProgress = scrollEffectProgress(
      rawProgress,
      IMAGE_EFFECT_START,
      IMAGE_EFFECT_END,
    );
    const contentFadeProgress = scrollEffectProgress(
      rawProgress,
      CONTENT_FADE_START,
      CONTENT_FADE_END,
    );
    const blur = imageEffectProgress * MAX_BLUR;
    const bgOpacity = 1 - imageEffectProgress;
    const fadeOpacity = 1 - contentFadeProgress;

    heroContent?.style.removeProperty("opacity");

    if (bg) {
      bg.style.filter = `blur(${blur}px)`;
      bg.style.opacity = String(bgOpacity);

      if (reducedMotion) {
        bg.style.removeProperty("transform");
      } else {
        const scale =
          SCALE_START + (SCALE_END - SCALE_START) * imageEffectProgress;
        bg.style.transform = `scale3d(${scale}, ${scale}, 1)`;
      }
    }

    if (desktopQuery.matches) {
      heroFadeTargetsDesktop?.forEach((el) => {
        el.style.opacity = String(fadeOpacity);
      });

      titleEls?.forEach((el) => {
        el.style.removeProperty("opacity");
      });

      syncTitleFade(
        shouldFadeHeroTitle(
          detailContent,
          cachedTitleBottom,
          scrollY,
          heroHeight,
        ),
      );
    } else {
      heroContent?.querySelectorAll(":scope > *").forEach((el) => {
        if (el.matches(".c-project-hero__more, [data-project-scroll-down]")) {
          return;
        }

        el.style.opacity = String(fadeOpacity);
      });

      syncTitleFade(false);
    }

    root.classList.toggle("is-scrolled-past-hero", scrollY >= vh);
  };

  const onScroll = () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      ticking = false;
      update();
    });
  };

  const onMotionChange = () => {
    reducedMotion = motionQuery.matches;
    update();
  };

  const onResize = () => {
    refreshTitleBottomCache();
    update();
  };

  const resizeObserver =
    detailContent && typeof ResizeObserver !== "undefined"
      ? new ResizeObserver(() => {
          refreshTitleBottomCache();
          update();
        })
      : null;

  resizeObserver?.observe(detailContent);

  const visualViewport = window.visualViewport;

  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", onResize, { passive: true });
  visualViewport?.addEventListener("scroll", onScroll, { passive: true });
  visualViewport?.addEventListener("resize", onResize, { passive: true });
  motionQuery.addEventListener("change", onMotionChange);
  refreshTitleBottomCache();
  update();

  teardown = () => {
    cancelScrollAnimation();
    resizeObserver?.disconnect();
    window.removeEventListener("scroll", onScroll);
    window.removeEventListener("resize", onResize);
    visualViewport?.removeEventListener("scroll", onScroll);
    visualViewport?.removeEventListener("resize", onResize);
    motionQuery.removeEventListener("change", onMotionChange);
    hero.classList.remove("is-hero-title-faded");
    titleEls?.forEach((el) => {
      el.classList.remove("is-hero-title-faded");
      el.removeAttribute("aria-hidden");
    });
    titleFadeState = null;
    heroContent?.style.removeProperty("opacity");
    heroFadeTargetsDesktop?.forEach((el) => {
      el.style.removeProperty("opacity");
    });
    heroContent?.querySelectorAll(":scope > *").forEach((el) => {
      el.style.removeProperty("opacity");
    });
    if (bg) {
      bg.style.removeProperty("filter");
      bg.style.removeProperty("opacity");
      bg.style.removeProperty("transform");
    }
    root.classList.remove("is-scrolled-past-hero");
  };
};
