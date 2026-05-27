const CAROUSEL_SCROLL_MS = 380;
const DRAG_CLICK_THRESHOLD = 6;

/** Snappy ease-out (approx. cubic-bezier(0.22, 1, 0.36, 1)) */
const easeOutSnappy = (t) => 1 - (1 - t) ** 3;

const prefersReducedMotion = () =>
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

export const initFeaturedQuoteCarousel = () => {
  document.querySelectorAll("[data-featured-quote-carousel]").forEach((root) => {
    if (root.dataset.featuredQuoteInit === "true") {
      return;
    }

    const viewport = root.querySelector("[data-featured-quote-viewport]");
    const track = viewport?.querySelector("[data-featured-quote-track]");
    const slides = [...root.querySelectorAll("[data-featured-quote-slide]")];
    const prevBtn = root.querySelector("[data-featured-quote-prev]");
    const nextBtn = root.querySelector("[data-featured-quote-next]");

    if (!track || !viewport) {
      return;
    }

    root.dataset.featuredQuoteInit = "true";

    if (slides.length < 2) {
      viewport.classList.add("is-at-end");
      return;
    }

    let index = 0;
    let scrollAnimFrame = null;
    let isDragging = false;
    let dragPointerId = null;
    let dragStartX = 0;
    let dragStartScroll = 0;
    let dragDelta = 0;

    const cancelScrollAnimation = () => {
      if (scrollAnimFrame !== null) {
        cancelAnimationFrame(scrollAnimFrame);
        scrollAnimFrame = null;
      }
    };

    const slideOffset = (slide) => slide.offsetLeft - track.offsetLeft;

    const updateViewportEndState = () => {
      const scrollMax = track.scrollWidth - track.clientWidth;
      const atScrollEnd = scrollMax <= 1 || track.scrollLeft >= scrollMax - 2;
      const atLastSlide = index >= slides.length - 1;
      viewport.classList.toggle("is-at-end", atLastSlide || atScrollEnd);
    };

    const syncIndexFromScroll = () => {
      const scrollLeft = track.scrollLeft;
      const trackOffset = track.offsetLeft;
      let closest = 0;
      let minDistance = Infinity;

      slides.forEach((slide, i) => {
        const distance = Math.abs(slideOffset(slide) - trackOffset - scrollLeft);
        if (distance < minDistance) {
          minDistance = distance;
          closest = i;
        }
      });

      index = closest;
      updateViewportEndState();
    };

    const animateScrollTo = (targetLeft, onComplete) => {
      cancelScrollAnimation();

      if (prefersReducedMotion()) {
        track.scrollLeft = targetLeft;
        onComplete?.();
        return;
      }

      const startLeft = track.scrollLeft;
      const delta = targetLeft - startLeft;

      if (Math.abs(delta) < 1) {
        onComplete?.();
        return;
      }

      const startTime = performance.now();

      const step = (now) => {
        const progress = Math.min(1, (now - startTime) / CAROUSEL_SCROLL_MS);
        track.scrollLeft = startLeft + delta * easeOutSnappy(progress);

        if (progress < 1) {
          scrollAnimFrame = requestAnimationFrame(step);
        } else {
          scrollAnimFrame = null;
          onComplete?.();
        }
      };

      scrollAnimFrame = requestAnimationFrame(step);
    };

    const scrollToIndex = (nextIndex) => {
      index = (nextIndex + slides.length) % slides.length;
      const slide = slides[index];
      animateScrollTo(slideOffset(slide), () => {
        syncIndexFromScroll();
      });
    };

    const snapToNearest = () => {
      syncIndexFromScroll();
      animateScrollTo(slideOffset(slides[index]), () => {
        syncIndexFromScroll();
      });
    };

    const setDragging = (active) => {
      isDragging = active;
      track.classList.toggle("is-dragging", active);

      if (active) {
        track.style.scrollSnapType = "none";
        track.style.scrollBehavior = "auto";
      } else {
        track.style.scrollSnapType = "";
        track.style.scrollBehavior = "";
      }
    };

    const onPointerDown = (event) => {
      if (event.button !== 0 && event.pointerType === "mouse") {
        return;
      }

      cancelScrollAnimation();
      isDragging = true;
      dragPointerId = event.pointerId;
      dragStartX = event.clientX;
      dragStartScroll = track.scrollLeft;
      dragDelta = 0;
      setDragging(true);
      track.setPointerCapture(event.pointerId);
    };

    const onPointerMove = (event) => {
      if (!isDragging || event.pointerId !== dragPointerId) {
        return;
      }

      dragDelta = event.clientX - dragStartX;
      track.scrollLeft = dragStartScroll - dragDelta;
      updateViewportEndState();
    };

    const onPointerEnd = (event) => {
      if (!isDragging || event.pointerId !== dragPointerId) {
        return;
      }

      if (track.hasPointerCapture(event.pointerId)) {
        track.releasePointerCapture(event.pointerId);
      }

      dragPointerId = null;
      setDragging(false);

      if (Math.abs(dragDelta) > DRAG_CLICK_THRESHOLD) {
        root.dataset.featuredQuoteDragged = "true";
        window.setTimeout(() => {
          delete root.dataset.featuredQuoteDragged;
        }, 0);
      }

      snapToNearest();
    };

    track.addEventListener("pointerdown", onPointerDown);
    track.addEventListener("pointermove", onPointerMove);
    track.addEventListener("pointerup", onPointerEnd);
    track.addEventListener("pointercancel", onPointerEnd);

    track.addEventListener(
      "click",
      (event) => {
        if (root.dataset.featuredQuoteDragged === "true") {
          event.preventDefault();
          event.stopPropagation();
        }
      },
      true
    );

    prevBtn?.addEventListener("click", () => scrollToIndex(index - 1));
    nextBtn?.addEventListener("click", () => scrollToIndex(index + 1));

    root.addEventListener("keydown", (event) => {
      if (event.key === "ArrowLeft") {
        event.preventDefault();
        scrollToIndex(index - 1);
      }
      if (event.key === "ArrowRight") {
        event.preventDefault();
        scrollToIndex(index + 1);
      }
    });

    let scrollEndTimer;
    track.addEventListener(
      "scroll",
      () => {
        if (isDragging) {
          return;
        }

        window.clearTimeout(scrollEndTimer);
        scrollEndTimer = window.setTimeout(syncIndexFromScroll, 80);
      },
      { passive: true }
    );

    track.scrollLeft = slideOffset(slides[0]);
    syncIndexFromScroll();
  });
};
