import { openOverlay } from "./overlay";

export const initContactOverlay = () => {
  document.querySelectorAll("[data-contact-open]").forEach((btn) => {
    btn.addEventListener("click", () => openOverlay("contact"));
  });
};
