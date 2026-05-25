export const initProjectScroll = () => {
  const root = document.querySelector("[data-project-scroll]");
  const hero = document.querySelector("[data-project-hero]");
  const bg = hero?.querySelector(".c-project-hero__bg, .o-image");
  const scrollBtn = document.querySelector("[data-project-scroll-down]");
  const detail = document.querySelector("[data-project-detail]");
  if (!root || !hero) return;

  scrollBtn?.addEventListener("click", () => {
    detail?.scrollIntoView({ behavior: "smooth" });
  });

  const onScroll = () => {
    const vh = window.innerHeight;
    const progress = Math.min(Math.max(window.scrollY / vh, 0), 1.5);
    const blur = progress * 20;
    const opacity = Math.max(1 - progress, 0);
    if (bg) {
      bg.style.filter = `blur(${blur}px)`;
      bg.style.opacity = String(opacity);
    }
    root.classList.toggle("is-scrolled-past-hero", window.scrollY >= vh);
  };

  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();
};
