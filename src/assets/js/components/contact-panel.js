import { syncHeaderCloseButton } from "./mobile-nav";

const HEADER_CONTACT_OPEN_CLASS = "is-contact-open";
const PANEL_OPEN_CLASS = "is-open";
const PANEL_TRANSITION_MS = 360;

let contactListenersBound = false;

const getContactPanel = () => document.querySelector("[data-contact-panel]");
const getHeader = () => document.querySelector("[data-floating-header]");

const setHeaderContactOpen = (open) => {
  getHeader()?.classList.toggle(HEADER_CONTACT_OPEN_CLASS, open);
  document.querySelectorAll("[data-contact-open]").forEach((btn) => {
    btn.setAttribute("aria-expanded", open ? "true" : "false");
  });
  syncHeaderCloseButton();
};

export const closeContactPanel = () =>
  new Promise((resolve) => {
    const panel = getContactPanel();
    if (!panel || panel.hidden) {
      resolve();
      return;
    }

    if (!panel.classList.contains(PANEL_OPEN_CLASS)) {
      panel.hidden = true;
      panel.setAttribute("aria-hidden", "true");
      setHeaderContactOpen(false);
      document.body.classList.remove("is-contact-open");
      resolve();
      return;
    }

    panel.classList.remove(PANEL_OPEN_CLASS);
    panel.setAttribute("aria-hidden", "true");
    setHeaderContactOpen(false);
    document.body.classList.remove("is-contact-open");

    let done = false;
    const finish = () => {
      if (done) return;
      done = true;
      panel.removeEventListener("transitionend", onEnd);
      panel.hidden = true;
      resolve();
    };

    const animatedEl = panel.querySelector(".c-contact-panel__grid");

    const onEnd = (event) => {
      if (event.target !== animatedEl) return;
      finish();
    };

    panel.addEventListener("transitionend", onEnd);
    window.setTimeout(finish, PANEL_TRANSITION_MS);
  });

export const openContactPanel = () => {
  const panel = getContactPanel();
  if (!panel) return;

  panel.hidden = false;
  panel.setAttribute("aria-hidden", "false");
  setHeaderContactOpen(true);
  document.body.classList.add("is-contact-open");

  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      panel.classList.add(PANEL_OPEN_CLASS);
    });
  });
};

export const toggleContactPanel = () => {
  const panel = getContactPanel();
  if (!panel) return;

  if (panel.hidden || !panel.classList.contains(PANEL_OPEN_CLASS)) {
    openContactPanel();
  } else {
    closeContactPanel();
  }
};

export const initContactPanel = () => {
  if (contactListenersBound) return;
  contactListenersBound = true;

  document.addEventListener("mobile-nav:close", () => {
    closeContactPanel();
  });

  document.querySelectorAll("[data-contact-open]").forEach((btn) => {
    btn.addEventListener("click", (event) => {
      event.preventDefault();
      toggleContactPanel();
    });
  });
};
