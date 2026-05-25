const PER_PAGE_DEFAULT = 12;

let observer = null;

const destroyNewsLoadMore = () => {
  observer?.disconnect();
  observer = null;
};

export const initNewsLoadMore = () => {
  destroyNewsLoadMore();

  const grid = document.querySelector("[data-news-grid]");
  if (!grid) return;

  const perPage =
    Number.parseInt(grid.dataset.newsPerPage ?? "", 10) || PER_PAGE_DEFAULT;
  const cards = [...grid.querySelectorAll("[data-news-card]")];
  const sentinel = grid.querySelector("[data-news-sentinel]");
  const liveRegion = document.querySelector("[data-news-live]");

  if (!sentinel || cards.length <= perPage) {
    sentinel?.remove();
    return;
  }

  let visibleCount = cards.filter((card) => !card.hidden).length;

  const loadMore = () => {
    const batch = cards.slice(visibleCount, visibleCount + perPage);
    if (!batch.length) return;

    batch.forEach((card) => {
      card.hidden = false;
    });
    visibleCount += batch.length;

    if (liveRegion) {
      const template = grid.dataset.newsLoadedMessage ?? "";
      liveRegion.textContent = template.replace("{{ count }}", String(batch.length));
    }

    if (visibleCount >= cards.length) {
      destroyNewsLoadMore();
      sentinel.remove();
    }
  };

  observer = new IntersectionObserver(
    (entries) => {
      if (entries.some((entry) => entry.isIntersecting)) loadMore();
    },
    { rootMargin: "200px 0px" }
  );

  observer.observe(sentinel);
};

export { destroyNewsLoadMore };
