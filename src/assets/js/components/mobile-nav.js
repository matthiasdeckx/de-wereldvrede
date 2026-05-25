export const initMobileNav = () => {
  const toggle = document.querySelector("[data-mobile-nav-toggle]");
  const nav = document.getElementById("mobile-nav");
  if (!toggle || !nav) return;

  toggle.addEventListener("click", () => {
    const open = nav.hidden;
    nav.hidden = !open;
    nav.setAttribute("aria-hidden", String(!open));
    toggle.setAttribute("aria-expanded", String(open));
    document.body.classList.toggle("is-mobile-nav-open", open);
  });
};

export const closeMobileNav = () => {
  const nav = document.getElementById("mobile-nav");
  const toggle = document.querySelector("[data-mobile-nav-toggle]");
  if (!nav || !toggle) return;
  nav.hidden = true;
  nav.setAttribute("aria-hidden", "true");
  toggle.setAttribute("aria-expanded", "false");
  document.body.classList.remove("is-mobile-nav-open");
};
