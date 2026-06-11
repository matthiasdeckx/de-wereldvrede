const MOBILE_STACKED_MQ = "(max-width: 520px)";
const COLLAPSED_MAX_HEIGHT = 800;

let teardown = null;

export const destroyAboutAwardsExpand = () => {
  teardown?.();
  teardown = null;
};

export const initAboutAwardsExpand = () => {
  destroyAboutAwardsExpand();

  const section = document.querySelector("[data-about-awards]");
  if (!section) return;

  const panel = section.querySelector("[data-about-awards-panel]");
  const content = section.querySelector("[data-about-awards-content]");
  const toggle = section.querySelector("[data-about-awards-toggle]");
  if (!panel || !content || !toggle) return;

  const mobileQuery = window.matchMedia(MOBILE_STACKED_MQ);
  const showLabel = toggle.dataset.labelMore || "Show more";
  const hideLabel = toggle.dataset.labelLess || "Show less";
  let expanded = false;

  const setExpanded = (open) => {
    expanded = open;
    panel.classList.toggle("is-expanded", open);
    panel.classList.toggle("is-collapsed", !open);
    toggle.textContent = open ? hideLabel : showLabel;
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
  };

  const resetCollapseState = () => {
    panel.classList.remove("is-collapsible", "is-expanded", "is-collapsed");
    toggle.hidden = true;
    expanded = false;
  };

  const sync = () => {
    if (!mobileQuery.matches) {
      resetCollapseState();
      return;
    }

    const needsCollapse = content.scrollHeight > COLLAPSED_MAX_HEIGHT;

    if (!needsCollapse) {
      resetCollapseState();
      return;
    }

    panel.classList.add("is-collapsible");
    toggle.hidden = false;
    setExpanded(expanded);
  };

  const onToggleClick = () => {
    setExpanded(!expanded);
  };

  const onLayoutChange = () => {
    if (!mobileQuery.matches) {
      expanded = false;
    }
    sync();
  };

  toggle.addEventListener("click", onToggleClick);
  window.addEventListener("resize", onLayoutChange, { passive: true });
  mobileQuery.addEventListener("change", onLayoutChange);

  sync();

  teardown = () => {
    toggle.removeEventListener("click", onToggleClick);
    window.removeEventListener("resize", onLayoutChange);
    mobileQuery.removeEventListener("change", onLayoutChange);
    resetCollapseState();
  };
};
