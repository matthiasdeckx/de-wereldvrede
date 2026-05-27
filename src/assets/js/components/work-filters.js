import { revealAllPendingWorkCards, revealWorkCard } from "./work-progressive-reveal";

const splitValues = (value) =>
  (value || "")
    .split(",")
    .map((part) => part.trim())
    .filter(Boolean);

export const initWorkFilters = () => {
  const grid = document.querySelector("[data-work-grid]");
  if (!grid || grid.dataset.workFiltersInit) return;
  grid.dataset.workFiltersInit = "true";

  let activeType = "all";
  let activeStatus = null;

  const cards = [...grid.querySelectorAll(".c-work-card")];
  const statusButtons = [...document.querySelectorAll("[data-filter-status]")];

  const cardMatchesType = (card, type) => {
    if (type === "all") return true;
    return splitValues(card.dataset.type).includes(type);
  };

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
  };

  document.querySelectorAll("[data-filter-type]").forEach((btn) => {
    btn.addEventListener("click", () => {
      activeType = btn.dataset.filterType;
      document.querySelectorAll("[data-filter-type]").forEach((b) => b.classList.toggle("is-active", b === btn));
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
