import { activateBlurUpImage, deferBlurUpImage, initBlurUp } from "./blur-up";

const INITIAL_COUNT = 9;
const ROW_DELAY_MS = 80;
const BLUR_STAGGER_MS = 75;

let revealTimer = null;
let blurTimers = [];

const getColumnCount = () => {
  if (window.matchMedia("(max-width: 520px)").matches) return 1;
  if (window.matchMedia("(max-width: 1024px)").matches) return 2;
  return 3;
};

const clearBlurTimers = () => {
  blurTimers.forEach((id) => clearTimeout(id));
  blurTimers = [];
};

export const destroyWorkProgressiveReveal = () => {
  if (revealTimer) {
    clearTimeout(revealTimer);
    revealTimer = null;
  }
  clearBlurTimers();
};

export const revealWorkCard = (card) => {
  if (!card?.classList.contains("is-work-card-pending")) return;

  card.classList.remove("is-work-card-pending");
  delete card.dataset.workPending;

  const img = card.querySelector(".c-image__full");
  if (img) activateBlurUpImage(img);
};

const staggerBlurUp = (cards) => {
  clearBlurTimers();

  cards.forEach((card, index) => {
    const id = setTimeout(() => initBlurUp(card), index * BLUR_STAGGER_MS);
    blurTimers.push(id);
  });
};

const scheduleRowReveal = (cards, startIndex) => {
  const batch = cards.slice(startIndex, startIndex + getColumnCount());
  if (!batch.length) return;

  batch.forEach(revealWorkCard);

  const nextIndex = startIndex + batch.length;
  if (nextIndex < cards.length) {
    revealTimer = setTimeout(() => scheduleRowReveal(cards, nextIndex), ROW_DELAY_MS);
  }
};

export const pauseProgressiveReveal = () => {
  if (revealTimer) {
    clearTimeout(revealTimer);
    revealTimer = null;
  }
};

export const revealAllPendingWorkCards = (cards) => {
  pauseProgressiveReveal();
  cards.forEach(revealWorkCard);
};

export const initWorkProgressiveReveal = () => {
  destroyWorkProgressiveReveal();

  const grid = document.querySelector("[data-work-grid]");
  if (!grid) return;

  const cards = [...grid.querySelectorAll(".c-work-card")];
  if (!cards.length) return;

  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  if (prefersReducedMotion || cards.length <= INITIAL_COUNT) {
    staggerBlurUp(cards);
    return;
  }

  cards.forEach((card, index) => {
    if (index < INITIAL_COUNT) return;

    card.classList.add("is-work-card-pending");
    card.dataset.workPending = "true";

    const img = card.querySelector(".c-image__full");
    if (img) deferBlurUpImage(img);
  });

  staggerBlurUp(cards.slice(0, INITIAL_COUNT));
  revealTimer = setTimeout(() => scheduleRowReveal(cards, INITIAL_COUNT), ROW_DELAY_MS);
};
