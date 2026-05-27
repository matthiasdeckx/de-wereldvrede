import { openOverlay } from "./overlay";

let contactListenersBound = false;

export const initContactOverlay = () => {
  if (contactListenersBound) return;
  contactListenersBound = true;

  document.querySelectorAll("[data-contact-open]").forEach((btn) => {
    btn.addEventListener("click", () => openOverlay("contact"));
  });
};
