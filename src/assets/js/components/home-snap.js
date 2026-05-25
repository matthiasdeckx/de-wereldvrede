let sectionObserver = null;
let footerObserver = null;

const destroyHomeSnap = () => {
  sectionObserver?.disconnect();
  footerObserver?.disconnect();
  sectionObserver = null;
  footerObserver = null;
};

export const initHomeSnap = () => {
  destroyHomeSnap();

  const root = document.querySelector("[data-home-scroll]");
  const indicator = document.querySelector("[data-scroll-indicator]");
  if (!root || !indicator) return;

  const allSections = [...root.querySelectorAll("[data-home-section]")];
  if (!allSections.length) return;

  const footer = root.querySelector(".c-home-footer");
  const sections = allSections.filter((section) => section !== footer);

  indicator.innerHTML = "";
  indicator.classList.remove("is-hidden");
  indicator.setAttribute("aria-hidden", "true");

  sections.forEach((_, i) => {
    const dot = document.createElement("span");
    dot.className = "c-home-scroll-indicator__dot" + (i === 0 ? " is-active" : "");
    indicator.appendChild(dot);
  });

  const dots = indicator.querySelectorAll(".c-home-scroll-indicator__dot");

  const setIndicatorHidden = (hidden) => {
    indicator.classList.toggle("is-hidden", hidden);
  };

  sectionObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const index = sections.indexOf(entry.target);
        if (index < 0) return;
        dots.forEach((dot, i) => dot.classList.toggle("is-active", i === index));
      });
    },
    { root, threshold: 0.6 }
  );
  sections.forEach((section) => sectionObserver.observe(section));

  if (!footer) return;

  footerObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        setIndicatorHidden(entry.isIntersecting);
      });
    },
    { root, threshold: 0.35 }
  );
  footerObserver.observe(footer);
};
