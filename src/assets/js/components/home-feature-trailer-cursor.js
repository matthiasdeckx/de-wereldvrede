import { openTrailerFromHost } from "./trailer-overlay";

const INTERACTIVE_SELECTOR =
  "a, button, input, select, textarea, label, [role=\"button\"], [data-no-trailer]";

const MOBILE_MQ = "(max-width: 768px)";

const isMobileTrailer = () => window.matchMedia(MOBILE_MQ).matches;

const hasTrailer = (section) => {
  const vimeo = (section.dataset.trailerVimeo || "").trim();
  const file = (section.dataset.trailerFile || "").trim();
  return Boolean(vimeo || file);
};

let teardown = null;

const destroyHomeFeatureTrailerCursor = () => {
  teardown?.();
  teardown = null;
};

const bindMobileTrailerButtons = () => {
  const cleanups = [...document.querySelectorAll("[data-hero-feature-trailer-mobile]")].map(
    (button) => {
      const selector = button.dataset.trailerSection || "[data-project-hero]";
      const section = document.querySelector(selector);

      const onClick = () => {
        if (!section || !hasTrailer(section)) return;
        openTrailerFromHost(section);
      };

      button.addEventListener("click", onClick);
      return () => button.removeEventListener("click", onClick);
    },
  );

  return () => cleanups.forEach((cleanup) => cleanup());
};

export const initHomeFeatureTrailerCursor = () => {
  destroyHomeFeatureTrailerCursor();

  const mobileCleanup = bindMobileTrailerButtons();

  const sections = [...document.querySelectorAll(".c-hero-feature.has-trailer")].filter(
    hasTrailer,
  );
  if (!sections.length) {
    teardown = mobileCleanup;
    return;
  }

  const cleanups = sections.map((section) => {
    const label = section.querySelector("[data-hero-feature-trailer-label]");
    if (!label) return () => {};

    const content = section.querySelector(".c-hero-feature__content");

    const isOverContent = (target) =>
      content && content.contains(target) && target !== content;

    const showLabel = () => {
      if (isMobileTrailer()) return;
      label.hidden = false;
      label.setAttribute("aria-hidden", "false");
      label.classList.add("is-visible");
    };

    const hideLabel = () => {
      label.classList.remove("is-visible", "is-over-content");
      label.hidden = true;
      label.setAttribute("aria-hidden", "true");
    };

    const onMove = (event) => {
      if (isMobileTrailer()) return;
      const rect = section.getBoundingClientRect();
      const x = event.clientX - rect.left;
      const y = event.clientY - rect.top;
      label.style.setProperty("--trailer-cursor-x", `${x}px`);
      label.style.setProperty("--trailer-cursor-y", `${y}px`);
      showLabel();
      label.classList.toggle("is-over-content", isOverContent(event.target));
    };

    const onLeave = () => {
      hideLabel();
    };

    const onClick = (event) => {
      if (isMobileTrailer()) return;
      if (event.target.closest(INTERACTIVE_SELECTOR)) return;
      openTrailerFromHost(section);
    };

    section.addEventListener("mousemove", onMove);
    section.addEventListener("mouseleave", onLeave);
    section.addEventListener("click", onClick);

    hideLabel();

    return () => {
      section.removeEventListener("mousemove", onMove);
      section.removeEventListener("mouseleave", onLeave);
      section.removeEventListener("click", onClick);
      hideLabel();
      label.style.removeProperty("--trailer-cursor-x");
      label.style.removeProperty("--trailer-cursor-y");
    };
  });

  teardown = () => {
    cleanups.forEach((cleanup) => cleanup());
    mobileCleanup();
  };
};
