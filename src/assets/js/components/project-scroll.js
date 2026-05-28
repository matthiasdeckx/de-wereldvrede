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
 * CONTENT_FADE_START / CONTENT_FADE_END — range for fading hero content elements
 * (logo/title excluded via selector). Independent of image effects.
 *
 * TITLE_FADE_START — last portion of detail scroll-through before logo/title fade.
 *
 * MAX_BLUR, SCALE_START, SCALE_END — image effect output at full progress.
 */
const IMAGE_EFFECT_START = 0.25;
const IMAGE_EFFECT_END = 1;
const CONTENT_FADE_START = 0.25;
const CONTENT_FADE_END = 0.45;
const TITLE_FADE_START = 0.75;
const MAX_BLUR = 64;
const SCALE_START = 1;
const SCALE_END = 1.12;

const scrollEffectProgress = (rawProgress, start, end) =>
  rawProgress <= start ? 0 : Math.min((rawProgress - start) / (end - start), 1);

const getScrollThroughProgress = (detailContent, viewportHeight) => {
  if (!detailContent) return 0;

  const rect = detailContent.getBoundingClientRect();
  const journey = viewportHeight + rect.height;

  if (journey <= 0) return 0;

  return Math.min(Math.max((viewportHeight - rect.top) / journey, 0), 1);
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
  const heroFadeTargets = heroContent
    ? heroContent.querySelectorAll(
        ":scope > :not(.c-hero-feature__logo, .c-hero-feature__title)",
      )
    : null;
  const scrollBtn = document.querySelector("[data-project-scroll-down]");
  const detail = document.querySelector("[data-project-detail]");
  const detailContent = detail?.querySelector(".c-project-detail__content");
  if (!root || !hero) return;

  const motionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
  const desktopQuery = window.matchMedia("(min-width: 1024px)");
  let reducedMotion = motionQuery.matches;
  let scrollAnimFrame = null;

  const cancelScrollAnimation = () => {
    if (scrollAnimFrame !== null) {
      cancelAnimationFrame(scrollAnimFrame);
      scrollAnimFrame = null;
    }
  };

  const animateWindowScrollTo = (targetTop) => {
    cancelScrollAnimation();

    const startTop = window.scrollY;
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
    const detailContent = detail?.querySelector(".c-project-detail__content");
    const textEl =
      detailContent?.querySelector(".c-project-detail__synopsis") ??
      detailContent?.querySelector(":scope > :first-child");

    if (!titleEl || !textEl) return null;

    const titleTop = titleEl.getBoundingClientRect().top;
    const textTop = textEl.getBoundingClientRect().top;

    return window.scrollY + textTop - titleTop;
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
      animateWindowScrollTo(
        window.scrollY + detail.getBoundingClientRect().top,
      );
    }
  });

  let ticking = false;

  const update = () => {
    const vh = window.innerHeight;
    const rawProgress = window.scrollY / vh;
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
    const scrollThroughProgress = getScrollThroughProgress(detailContent, vh);
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

    heroFadeTargets?.forEach((el) => {
      el.style.opacity = String(fadeOpacity);
    });

    hero.classList.toggle(
      "is-hero-title-faded",
      scrollThroughProgress >= TITLE_FADE_START,
    );

    root.classList.toggle("is-scrolled-past-hero", window.scrollY >= vh);
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

  window.addEventListener("scroll", onScroll, { passive: true });
  motionQuery.addEventListener("change", onMotionChange);
  update();

  teardown = () => {
    cancelScrollAnimation();
    window.removeEventListener("scroll", onScroll);
    motionQuery.removeEventListener("change", onMotionChange);
    hero.classList.remove("is-hero-title-faded");
    heroContent?.style.removeProperty("opacity");
    heroFadeTargets?.forEach((el) => {
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
