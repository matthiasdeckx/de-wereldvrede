import { closeMobileNav } from "./mobile-nav";

const OVERLAY_ATTR = "data-overlay";
const OVERLAY_OPEN_CLASS = "is-open";
const OVERLAY_TRANSITION_MS = 650;

let overlayListenersBound = false;

const getOverlays = () => document.querySelectorAll(`[${OVERLAY_ATTR}]`);

const setHeaderOverlayMode = (open) => {
  const header = document.querySelector("[data-floating-header]");
  const closeBtn = header?.querySelector(".c-floating-header__close");
  if (!header) return;

  header.classList.toggle("is-overlay-mode", open);
  closeBtn?.setAttribute("aria-hidden", open ? "false" : "true");
  closeBtn?.setAttribute("tabindex", open ? "0" : "-1");
};

const closeOverlay = (overlay) =>
  new Promise((resolve) => {
    if (!overlay || overlay.hidden) {
      resolve();
      return;
    }

    if (!overlay.classList.contains(OVERLAY_OPEN_CLASS)) {
      overlay.hidden = true;
      overlay.setAttribute("aria-hidden", "true");
      resolve();
      return;
    }

    overlay.classList.remove(OVERLAY_OPEN_CLASS);
    overlay.setAttribute("aria-hidden", "true");

    const backdrop = overlay.querySelector(".c-overlay__backdrop");
    let done = false;

    const finish = () => {
      if (done) return;
      done = true;
      backdrop?.removeEventListener("transitionend", onEnd);
      overlay.hidden = true;
      resolve();
    };

    const onEnd = (event) => {
      if (event.target !== backdrop) return;
      finish();
    };

    backdrop?.addEventListener("transitionend", onEnd);
    window.setTimeout(finish, OVERLAY_TRANSITION_MS);
  });

export const openOverlay = (name) => {
  const overlay = document.querySelector(`[${OVERLAY_ATTR}="${name}"]`);
  if (!overlay) return;

  closeMobileNav();

  getOverlays().forEach((item) => {
    if (item !== overlay) {
      item.classList.remove(OVERLAY_OPEN_CLASS);
      item.hidden = true;
      item.setAttribute("aria-hidden", "true");
    }
  });

  overlay.hidden = false;
  overlay.setAttribute("aria-hidden", "false");
  document.body.classList.add("is-overlay-open");
  setHeaderOverlayMode(true);

  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      overlay.classList.add(OVERLAY_OPEN_CLASS);
    });
  });
};

export const closeOverlays = () => {
  const openOverlays = [...getOverlays()].filter(
    (overlay) => !overlay.hidden || overlay.classList.contains(OVERLAY_OPEN_CLASS)
  );

  document.querySelector("[data-trailer-container]")?.replaceChildren();

  Promise.all(openOverlays.map((overlay) => closeOverlay(overlay))).then(() => {
    document.body.classList.remove("is-overlay-open");
    setHeaderOverlayMode(false);
  });
};

export const initOverlays = () => {
  if (overlayListenersBound) return;
  overlayListenersBound = true;

  document.addEventListener("click", (event) => {
    if (event.target.closest("[data-overlay-close]")) {
      closeOverlays();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeOverlays();
    }
  });
};
