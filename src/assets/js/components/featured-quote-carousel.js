export const initFeaturedQuoteCarousel = () => {
  document.querySelectorAll("[data-featured-quote-carousel]").forEach((root) => {
    const track = root.querySelector("[data-featured-quote-track]");
    const slides = [...root.querySelectorAll("[data-featured-quote-slide]")];
    const prevBtn = root.querySelector("[data-featured-quote-prev]");
    const nextBtn = root.querySelector("[data-featured-quote-next]");
    const dots = [...root.querySelectorAll("[data-featured-quote-dot]")];

    if (!track || slides.length < 2) return;

    let index = slides.findIndex((slide) => slide.classList.contains("is-active"));
    if (index < 0) index = 0;

    const setSlide = (nextIndex) => {
      index = (nextIndex + slides.length) % slides.length;

      slides.forEach((slide, i) => {
        const active = i === index;
        slide.classList.toggle("is-active", active);
        slide.hidden = !active;
        slide.setAttribute("aria-hidden", active ? "false" : "true");
      });

      dots.forEach((dot, i) => {
        const active = i === index;
        dot.classList.toggle("is-active", active);
        dot.setAttribute("aria-selected", active ? "true" : "false");
      });
    };

    prevBtn?.addEventListener("click", () => setSlide(index - 1));
    nextBtn?.addEventListener("click", () => setSlide(index + 1));

    dots.forEach((dot) => {
      dot.addEventListener("click", () => {
        const target = Number.parseInt(dot.dataset.featuredQuoteDot ?? "", 10);
        if (!Number.isNaN(target)) setSlide(target);
      });
    });

    root.addEventListener("keydown", (event) => {
      if (event.key === "ArrowLeft") {
        event.preventDefault();
        setSlide(index - 1);
      }
      if (event.key === "ArrowRight") {
        event.preventDefault();
        setSlide(index + 1);
      }
    });

    setSlide(index);
  });
};
