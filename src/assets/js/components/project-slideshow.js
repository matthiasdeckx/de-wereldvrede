const POINTER_SLIDESHOW_QUERY = "(min-width: 768px) and (hover: hover) and (pointer: fine)";
const SWIPE_THRESHOLD = 40;

const usesPointerSlideshow = () => window.matchMedia(POINTER_SLIDESHOW_QUERY).matches;

export const initProjectSlideshow = () => {
  document.querySelectorAll("[data-project-slideshow]").forEach((root) => {
    if (root.dataset.projectSlideshowInit === "true") {
      return;
    }

    const viewport = root.querySelector("[data-project-slideshow-viewport]");
    const slides = [...root.querySelectorAll("[data-project-slideshow-slide]")];
    const dots = [...root.querySelectorAll("[data-project-slideshow-dot]")];
    const prevBtn = root.querySelector("[data-project-slideshow-prev]");
    const nextBtn = root.querySelector("[data-project-slideshow-next]");

    if (!slides.length) {
      return;
    }

    root.dataset.projectSlideshowInit = "true";

    if (slides.length < 2) {
      return;
    }

    let index = 0;
    let swipeStartX = 0;
    let swipeActive = false;

    const syncInteractionMode = () => {
      root.classList.toggle("is-pointer-slideshow", usesPointerSlideshow());
    };

    syncInteractionMode();
    window.matchMedia(POINTER_SLIDESHOW_QUERY).addEventListener("change", syncInteractionMode);

    const goTo = (nextIndex) => {
      index = (nextIndex + slides.length) % slides.length;

      slides.forEach((slide, i) => {
        const active = i === index;
        slide.classList.toggle("is-active", active);
        slide.setAttribute("aria-hidden", active ? "false" : "true");
      });

      dots.forEach((dot, i) => {
        const active = i === index;
        dot.classList.toggle("is-active", active);
        dot.setAttribute("aria-selected", active ? "true" : "false");
        dot.tabIndex = active ? 0 : -1;
      });
    };

    const goNext = () => goTo(index + 1);
    const goPrev = () => goTo(index - 1);

    dots.forEach((dot, i) => {
      dot.addEventListener("click", (event) => {
        event.stopPropagation();
        goTo(i);
      });
    });

    prevBtn?.addEventListener("click", (event) => {
      event.stopPropagation();
      goPrev();
    });

    nextBtn?.addEventListener("click", (event) => {
      event.stopPropagation();
      goNext();
    });

    viewport?.addEventListener("click", () => {
      if (!usesPointerSlideshow()) {
        return;
      }

      goNext();
    });

    viewport?.addEventListener(
      "touchstart",
      (event) => {
        if (usesPointerSlideshow()) {
          return;
        }

        swipeActive = true;
        swipeStartX = event.changedTouches[0]?.clientX ?? 0;
      },
      { passive: true }
    );

    viewport?.addEventListener(
      "touchend",
      (event) => {
        if (!swipeActive || usesPointerSlideshow()) {
          return;
        }

        swipeActive = false;

        const endX = event.changedTouches[0]?.clientX ?? swipeStartX;
        const delta = endX - swipeStartX;

        if (Math.abs(delta) < SWIPE_THRESHOLD) {
          return;
        }

        if (delta < 0) {
          goNext();
        } else {
          goPrev();
        }
      },
      { passive: true }
    );

    viewport?.addEventListener(
      "touchcancel",
      () => {
        swipeActive = false;
      },
      { passive: true }
    );

    root.addEventListener("keydown", (event) => {
      if (event.key === "ArrowLeft") {
        event.preventDefault();
        goPrev();
      }
      if (event.key === "ArrowRight") {
        event.preventDefault();
        goNext();
      }
    });
  });
};
