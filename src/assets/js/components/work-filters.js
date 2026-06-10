import { revealAllPendingWorkCards, revealWorkCard } from "./work-progressive-reveal";

const splitValues = (value) =>
  (value || "")
    .split(",")
    .map((part) => part.trim())
    .filter(Boolean);

const MOBILE_FILTER_MQ = "(max-width: 768px)";

const isMobileFilter = () => window.matchMedia(MOBILE_FILTER_MQ).matches;

const bindMobileFilterPanel = (filtersRoot, { getSummary }) => {
  const toggle = filtersRoot.querySelector("[data-work-filters-toggle]");
  const panel = filtersRoot.querySelector("[data-work-filters-panel]");
  if (!toggle || !panel) return null;

  const showLabel = filtersRoot.dataset.filterShowLabel || "Show filters";
  const hideLabel = filtersRoot.dataset.filterHideLabel || "Hide filters";

  const syncPanelVisibility = () => {
    if (isMobileFilter()) {
      panel.hidden = !filtersRoot.classList.contains("is-open");
      return;
    }

    panel.hidden = false;
    filtersRoot.classList.remove("is-open");
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-label", showLabel);
  };

  const setOpen = (open) => {
    if (!isMobileFilter()) return;

    filtersRoot.classList.toggle("is-open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    toggle.setAttribute("aria-label", open ? hideLabel : showLabel);
    panel.hidden = !open;
  };

  const closePanel = () => setOpen(false);

  toggle.addEventListener("click", () => {
    if (!isMobileFilter()) return;
    setOpen(!filtersRoot.classList.contains("is-open"));
  });

  panel.querySelectorAll(".c-work-filters__btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (isMobileFilter()) {
        closePanel();
      }
    });
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && filtersRoot.classList.contains("is-open")) {
      closePanel();
    }
  });

  document.addEventListener("click", (event) => {
    if (!filtersRoot.classList.contains("is-open")) return;
    if (filtersRoot.contains(event.target)) return;
    closePanel();
  });

  window.addEventListener("resize", syncPanelVisibility);

  syncPanelVisibility();

  const updateSummary = () => {
    const summaryEl = filtersRoot.querySelector("[data-work-filters-summary]");
    if (summaryEl) {
      summaryEl.textContent = getSummary();
    }
  };

  return { updateSummary, closePanel };
};

export const initWorkFilters = () => {
  const filtersRoot = document.querySelector("[data-work-filters]");
  const grid = document.querySelector("[data-work-grid]");
  if (!filtersRoot || !grid || filtersRoot.dataset.workFiltersInit) return;
  filtersRoot.dataset.workFiltersInit = "true";

  let activeType = "all";
  let activeStatus = null;

  const cards = [...grid.querySelectorAll(".c-work-card")];
  const statusButtons = [...filtersRoot.querySelectorAll("[data-filter-status]")];

  const cardMatchesType = (card, type) => {
    if (type === "all") return true;
    return splitValues(card.dataset.type).includes(type);
  };

  const getSummary = () => {
    const typeBtn = filtersRoot.querySelector("[data-filter-type].is-active");
    const typeText = typeBtn?.textContent.trim() ?? "All";

    if (!activeStatus) {
      return typeText;
    }

    const statusBtn = statusButtons.find((btn) => btn.dataset.filterStatus === activeStatus);
    const statusLabel = statusBtn?.dataset.filterLabel || activeStatus;

    return `${typeText.split(" (")[0]} · ${statusLabel}`;
  };

  const mobilePanel = bindMobileFilterPanel(filtersRoot, { getSummary });

  const updateStatusCounts = () => {
    statusButtons.forEach((btn) => {
      const status = btn.dataset.filterStatus;
      const label = btn.dataset.filterLabel || status;
      const count = cards.filter(
        (card) =>
          cardMatchesType(card, activeType) &&
          splitValues(card.dataset.status).includes(status),
      ).length;
      btn.textContent = `${label} (${count})`;
    });
    mobilePanel?.updateSummary();
  };

  const isFilterActive = () => activeType !== "all" || activeStatus !== null;

  const apply = () => {
    const filterActive = isFilterActive();

    if (filterActive) {
      revealAllPendingWorkCards(cards);
    }

    cards.forEach((card) => {
      const types = splitValues(card.dataset.type);
      const statuses = splitValues(card.dataset.status);
      const typeMatch = activeType === "all" || types.includes(activeType);
      const statusMatch = !activeStatus || statuses.includes(activeStatus);
      const matches = typeMatch && statusMatch;

      if (matches && filterActive && card.dataset.workPending === "true") {
        revealWorkCard(card);
      }

      card.hidden = !matches;
    });

    mobilePanel?.updateSummary();
  };

  filtersRoot.querySelectorAll("[data-filter-type]").forEach((btn) => {
    btn.addEventListener("click", () => {
      activeType = btn.dataset.filterType;
      filtersRoot.querySelectorAll("[data-filter-type]").forEach((b) => b.classList.toggle("is-active", b === btn));
      updateStatusCounts();
      apply();
    });
  });

  statusButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      activeStatus = activeStatus === btn.dataset.filterStatus ? null : btn.dataset.filterStatus;
      statusButtons.forEach((b) => b.classList.toggle("is-active", b.dataset.filterStatus === activeStatus));
      apply();
    });
  });

  updateStatusCounts();
  apply();
};
