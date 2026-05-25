/**
 * Stagger-reveal production overlay media items via Intersection Observer.
 * Image loading is handled natively by the browser (loading="lazy")
 * and blur-up handles the placeholder-to-full transition.
 */
const STAGGER_INDEX_PROP = "--i";

export function initProductionOverlayImages(container) {
  if (!container || !(container instanceof Element)) {
    return;
  }

  const items = container.querySelectorAll(".c-production__media-item");

  items.forEach((item, index) => {
    item.style.setProperty(STAGGER_INDEX_PROP, String(index));
  });

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    },
    {
      root: container.closest(".c-production-modal__content") || null,
      rootMargin: "80px",
      threshold: 0.01,
    }
  );

  items.forEach((item) => observer.observe(item));
}
