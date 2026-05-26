export const initProjectScroll = () => {
  const root = document.querySelector("[data-project-scroll]");
  const hero = document.querySelector("[data-project-hero]");
  const bg = hero?.querySelector(".c-hero-feature__bg, .c-project-hero__bg");
  const heroContent = hero?.querySelector(".c-hero-feature__content");
  const scrollBtn = document.querySelector("[data-project-scroll-down]");
  const detail = document.querySelector("[data-project-detail]");
  const detailContent = detail?.querySelector(".c-project-detail__content");
  if (!root || !hero) return;

  scrollBtn?.addEventListener("click", () => {
    detail?.scrollIntoView({ behavior: "smooth" });
  });

  const EFFECT_START = 0.25;
  const EFFECT_END = 1;
  const MAX_BLUR = 64;
  const SCALE_START = 1;
  const SCALE_END = 1.12;

  /** Last 25% of scroll-through `.c-project-detail__content` */
  const CONTENT_FADE_START = 0.75;

  const motionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
  let reducedMotion = motionQuery.matches;
  let ticking = false;

  const update = () => {
    const vh = window.innerHeight;
    const rawProgress = window.scrollY / vh;
    const effectProgress =
      rawProgress <= EFFECT_START
        ? 0
        : Math.min((rawProgress - EFFECT_START) / (EFFECT_END - EFFECT_START), 1);
    const blur = effectProgress * MAX_BLUR;
    const opacity = 1 - effectProgress;

    if (bg) {
      bg.style.filter = `blur(${blur}px)`;
      bg.style.opacity = String(opacity);

      if (reducedMotion) {
        bg.style.removeProperty("transform");
      } else {
        const scale = SCALE_START + (SCALE_END - SCALE_START) * effectProgress;
        bg.style.transform = `scale3d(${scale}, ${scale}, 1)`;
      }
    }

    if (heroContent && detailContent) {
      const rect = detailContent.getBoundingClientRect();
      const contentHeight = rect.height;
      const journey = vh + contentHeight;
      const scrollThroughProgress =
        journey > 0 ? Math.min(Math.max((vh - rect.top) / journey, 0), 1) : 0;

      let contentOpacity = 1;
      if (scrollThroughProgress >= CONTENT_FADE_START) {
        const fadeProgress =
          (scrollThroughProgress - CONTENT_FADE_START) / (1 - CONTENT_FADE_START);
        contentOpacity = reducedMotion ? 0 : 1 - fadeProgress;
      }

      heroContent.style.opacity = String(contentOpacity);
    }

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
};
