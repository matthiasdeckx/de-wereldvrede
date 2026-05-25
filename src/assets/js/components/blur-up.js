/**
 * Blur-up LQIP: add .is-loaded to .c-image when the full-res image loads.
 * Works for both static pages and dynamically injected content (AJAX overlays).
 */
function markLoaded(wrapper) {
  if (wrapper && !wrapper.classList.contains("is-loaded")) {
    wrapper.classList.add("is-loaded");
  }
}

export function initBlurUp(container = document) {
  const root = container?.querySelectorAll ? container : document;
  const images = root.querySelectorAll(".c-image--blur-up .c-image__full");

  images.forEach((img) => {
    const wrapper = img.closest(".c-image");
    if (!wrapper || wrapper.classList.contains("is-loaded")) {
      return;
    }

    if (img.complete && img.naturalWidth > 0) {
      markLoaded(wrapper);
      return;
    }

    img.addEventListener("load", () => markLoaded(wrapper), { once: true });
    img.addEventListener("error", () => markLoaded(wrapper), { once: true });
  });
}
