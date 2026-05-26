export const initFeaturedQuoteCarousel = () => {
  document.querySelectorAll("[data-featured-quote-carousel]").forEach((root) => {
    const viewport = root.querySelector("[data-featured-quote-viewport]");
    const track = viewport?.querySelector("[data-featured-quote-track]");
    const slides = [...root.querySelectorAll("[data-featured-quote-slide]")];
    const prevBtn = root.querySelector("[data-featured-quote-prev]");
    const nextBtn = root.querySelector("[data-featured-quote-next]");

    if (!track || !viewport) return;

    if (slides.length < 2) {
      viewport.classList.add("is-at-end");
      return;
    }

    let index = 0;

    const updateViewportEndState = () => {
      const scrollMax = track.scrollWidth - track.clientWidth;
      const atScrollEnd = scrollMax <= 1 || track.scrollLeft >= scrollMax - 2;
      const atLastSlide = index >= slides.length - 1;
      viewport.classList.toggle("is-at-end", atLastSlide || atScrollEnd);
    };

    const scrollToIndex = (nextIndex, behavior = "smooth") => {
      index = (nextIndex + slides.length) % slides.length;
      const slide = slides[index];
      const offset = slide.offsetLeft - track.offsetLeft;
      track.scrollTo({ left: offset, behavior });
      updateViewportEndState();
    };

    const syncIndexFromScroll = () => {
      const scrollLeft = track.scrollLeft;
      const trackOffset = track.offsetLeft;
      let closest = 0;
      let minDistance = Infinity;

      slides.forEach((slide, i) => {
        const distance = Math.abs(slide.offsetLeft - trackOffset - scrollLeft);
        if (distance < minDistance) {
          minDistance = distance;
          closest = i;
        }
      });

      index = closest;
      updateViewportEndState();
    };

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
    track.addEventListener("scroll", () => {
      window.clearTimeout(scrollEndTimer);
      scrollEndTimer = window.setTimeout(syncIndexFromScroll, 80);
    }, { passive: true });

    scrollToIndex(0, "auto");
  });
};
