import { openTrailerFromHost } from "./trailer-overlay";

const INTERACTIVE_SELECTOR =
  "a, button, input, select, textarea, label, [role=\"button\"], [data-no-trailer]";

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

export const initHomeFeatureTrailerCursor = () => {
  destroyHomeFeatureTrailerCursor();

  const sections = [...document.querySelectorAll(".c-home-feature.has-trailer")].filter(
    hasTrailer
  );
  if (!sections.length) return;

  const cleanups = sections.map((section) => {
    const label = section.querySelector("[data-home-feature-trailer-label]");
    if (!label) return () => {};

    const content = section.querySelector(".c-home-feature__content");

    const isOverContent = (target) =>
      content && content.contains(target) && target !== content;

    const showLabel = () => {
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
  };
};
