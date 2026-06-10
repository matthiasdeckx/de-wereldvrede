import { closeProjectAvailableOn } from "./project-available-on";

const getViewportHeight = () =>
  window.visualViewport?.height ?? window.innerHeight;

const closeWorkFiltersPanel = () => {
  const filters = document.querySelector("[data-work-filters].is-open");
  if (!filters) return;

  filters.classList.remove("is-open");

  const panel = filters.querySelector("[data-work-filters-panel]");
  const toggle = filters.querySelector("[data-work-filters-toggle]");
  const showLabel = filters.dataset.filterShowLabel || "Show filters";

  if (panel) panel.hidden = true;
  toggle?.setAttribute("aria-expanded", "false");
  toggle?.setAttribute("aria-label", showLabel);
};

let teardown = null;

export const destroyFloatingUiFooter = () => {
  teardown?.();
  teardown = null;
};

export const initFloatingUiFooter = () => {
  destroyFloatingUiFooter();

  const footer = document.querySelector("#main .c-site-footer");
  const docks = [...document.querySelectorAll("[data-floating-ui-dock]")];

  if (!footer || docks.length === 0) return;

  let hidden = null;

  const update = () => {
    const shouldHide =
      footer.getBoundingClientRect().top <= getViewportHeight();

    if (shouldHide === hidden) return;
    hidden = shouldHide;

    docks.forEach((el) => {
      el.classList.toggle("is-hidden-by-footer", shouldHide);
    });

    if (shouldHide) {
      closeWorkFiltersPanel();
      closeProjectAvailableOn();
    }
  };

  let ticking = false;

  const onScroll = () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      ticking = false;
      update();
    });
  };

  const onResize = () => {
    update();
  };

  const visualViewport = window.visualViewport;

  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", onResize, { passive: true });
  visualViewport?.addEventListener("scroll", onScroll, { passive: true });
  visualViewport?.addEventListener("resize", onResize, { passive: true });

  const resizeObserver =
    typeof ResizeObserver !== "undefined"
      ? new ResizeObserver(update)
      : null;

  resizeObserver?.observe(footer);

  update();

  teardown = () => {
    window.removeEventListener("scroll", onScroll);
    window.removeEventListener("resize", onResize);
    visualViewport?.removeEventListener("scroll", onScroll);
    visualViewport?.removeEventListener("resize", onResize);
    resizeObserver?.disconnect();
    docks.forEach((el) => el.classList.remove("is-hidden-by-footer"));
    hidden = null;
  };
};
