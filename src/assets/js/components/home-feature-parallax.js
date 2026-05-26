/** Background moves at this fraction of section scroll-through (subtle lag). */
export const HOME_FEATURE_PARALLAX_FACTOR = 0.2;

let teardown = null;

const prefersReducedMotion = () =>
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const destroyHomeFeatureParallax = () => {
  teardown?.();
  teardown = null;
};

const resetBgTransforms = (backgrounds) => {
  backgrounds.forEach((bg) => {
    bg.style.removeProperty("transform");
  });
};

export const initHomeFeatureParallax = () => {
  destroyHomeFeatureParallax();

  const root = document.querySelector("[data-home-scroll]");
  if (!root) return;

  const slides = [...root.querySelectorAll(".c-home-feature")].flatMap((section) => {
    const bg = section.querySelector(".c-hero-feature__bg");
    return bg ? [{ section, bg }] : [];
  });

  if (!slides.length) return;

  const motionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
  let reducedMotion = prefersReducedMotion();
  let rafId = 0;
  let scrollPending = false;

  const update = () => {
    rafId = 0;
    scrollPending = false;

    if (reducedMotion) {
      resetBgTransforms(slides.map((s) => s.bg));
      return;
    }

    const rootRect = root.getBoundingClientRect();

    slides.forEach(({ section, bg }) => {
      const rect = section.getBoundingClientRect();
      const sectionTop = rect.top - rootRect.top;
      const scrollThrough = Math.min(Math.max(-sectionTop, 0), rect.height);
      const offset = scrollThrough * HOME_FEATURE_PARALLAX_FACTOR;
      bg.style.transform = `translate3d(0, ${offset}px, 0)`;
    });
  };

  const scheduleUpdate = () => {
    if (scrollPending) return;
    scrollPending = true;
    rafId = requestAnimationFrame(update);
  };

  const onMotionChange = () => {
    reducedMotion = prefersReducedMotion();
    scheduleUpdate();
  };

  root.addEventListener("scroll", scheduleUpdate, { passive: true });
  motionQuery.addEventListener("change", onMotionChange);
  window.addEventListener("resize", scheduleUpdate, { passive: true });

  scheduleUpdate();

  teardown = () => {
    root.removeEventListener("scroll", scheduleUpdate);
    motionQuery.removeEventListener("change", onMotionChange);
    window.removeEventListener("resize", scheduleUpdate);
    if (rafId) cancelAnimationFrame(rafId);
    resetBgTransforms(slides.map((s) => s.bg));
  };
};
