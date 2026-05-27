/**
 * Blur-up LQIP: add .is-loaded to .c-image when the full-res image loads.
 * Works for both static pages and dynamically injected content (AJAX overlays).
 */
function markLoaded(wrapper) {
  if (wrapper && !wrapper.classList.contains("is-loaded")) {
    wrapper.classList.add("is-loaded");
  }
}

function watchBlurUpImage(img) {
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
}

export function deferBlurUpImage(img) {
  if (!img || img.dataset.blurUpDeferred === "true") return;

  const src = img.getAttribute("src");
  if (!src) return;

  img.dataset.blurUpSrc = src;
  const srcset = img.getAttribute("srcset");
  if (srcset) img.dataset.blurUpSrcset = srcset;

  img.removeAttribute("src");
  img.removeAttribute("srcset");
  img.dataset.blurUpDeferred = "true";
}

export function activateBlurUpImage(img) {
  if (!img || img.dataset.blurUpDeferred !== "true") {
    watchBlurUpImage(img);
    return;
  }

  const src = img.dataset.blurUpSrc;
  if (src) img.setAttribute("src", src);

  const srcset = img.dataset.blurUpSrcset;
  if (srcset) img.setAttribute("srcset", srcset);

  delete img.dataset.blurUpDeferred;
  delete img.dataset.blurUpSrc;
  delete img.dataset.blurUpSrcset;

  watchBlurUpImage(img);
}

export function initBlurUp(container = document) {
  const root = container?.querySelectorAll ? container : document;
  const images = root.querySelectorAll(".c-image--blur-up .c-image__full");

  images.forEach((img) => {
    if (img.dataset.blurUpDeferred === "true") return;
    watchBlurUpImage(img);
  });
}
