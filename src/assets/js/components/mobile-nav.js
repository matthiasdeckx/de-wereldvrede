let mobileNavListenersBound = false;

const getMobileNavEls = () => {
  const header = document.querySelector("[data-floating-header]");
  const nav = document.getElementById("mobile-nav");
  const toggle = document.querySelector("[data-mobile-nav-toggle]");
  const closeBtns = header?.querySelectorAll(".c-floating-header__slot-action--close") ?? [];
  return { header, nav, toggle, closeBtns };
};

export const syncHeaderCloseButton = () => {
  const { header, closeBtns, nav } = getMobileNavEls();
  if (!closeBtns.length || !nav) return;

  const navOpen = !nav.hidden;
  const overlayOpen = document.body.classList.contains("is-overlay-open");
  const showClose = navOpen || overlayOpen;

  closeBtns.forEach((closeBtn) => {
    closeBtn.setAttribute("aria-hidden", showClose ? "false" : "true");
    closeBtn.setAttribute("tabindex", showClose ? "0" : "-1");
  });
};

export const closeMobileNav = () => {
  const { header, nav, toggle } = getMobileNavEls();
  if (!nav || !toggle) return;

  const wasOpen = !nav.hidden;

  nav.hidden = true;
  nav.setAttribute("aria-hidden", "true");
  toggle.setAttribute("aria-expanded", "false");
  header?.classList.remove("is-mobile-nav-open");
  document.body.classList.remove("is-mobile-nav-open");
  syncHeaderCloseButton();

  if (wasOpen) {
    document.dispatchEvent(new CustomEvent("mobile-nav:close"));
  }
};

const openMobileNav = () => {
  const { header, nav, toggle } = getMobileNavEls();
  if (!nav || !toggle) return;

  nav.hidden = false;
  nav.setAttribute("aria-hidden", "false");
  toggle.setAttribute("aria-expanded", "true");
  header?.classList.add("is-mobile-nav-open");
  document.body.classList.add("is-mobile-nav-open");
  syncHeaderCloseButton();
};

const toggleMobileNav = () => {
  const { nav } = getMobileNavEls();
  if (!nav) return;

  if (nav.hidden) {
    openMobileNav();
  } else {
    closeMobileNav();
  }
};

export const initMobileNav = () => {
  if (mobileNavListenersBound) return;
  mobileNavListenersBound = true;

  document.addEventListener("click", (event) => {
    if (event.target.closest("[data-mobile-nav-toggle]")) {
      event.preventDefault();
      toggleMobileNav();
      return;
    }

    const nav = document.getElementById("mobile-nav");
    if (nav && !nav.hidden && event.target.closest("#mobile-nav a[href]")) {
      closeMobileNav();
    }
  });

};
