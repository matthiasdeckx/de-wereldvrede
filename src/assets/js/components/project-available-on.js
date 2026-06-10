let activeRoot = null;
let listenersBound = false;

const setOpen = (root, open) => {
  const toggle = root.querySelector("[data-project-available-on-toggle]");
  const panel = root.querySelector("[data-project-available-on-panel]");
  if (!toggle || !panel) return;

  const showLabel = root.dataset.availableOnShowLabel || "Available on";
  const hideLabel = root.dataset.availableOnHideLabel || "Close available on";

  root.classList.toggle("is-open", open);
  toggle.setAttribute("aria-expanded", open ? "true" : "false");
  toggle.setAttribute("aria-label", open ? hideLabel : showLabel);
  panel.hidden = !open;
};

export const closeProjectAvailableOn = () => {
  if (!activeRoot?.isConnected) {
    activeRoot = null;
    return;
  }

  if (activeRoot.classList.contains("is-open")) {
    setOpen(activeRoot, false);
  }
};

export const initProjectAvailableOn = () => {
  const root = document.querySelector("[data-project-available-on]");
  activeRoot = root;

  if (!root || root.dataset.projectAvailableOnInit) return;
  root.dataset.projectAvailableOnInit = "true";

  const toggle = root.querySelector("[data-project-available-on-toggle]");
  if (!toggle) return;

  toggle.addEventListener("click", () => {
    setOpen(root, !root.classList.contains("is-open"));
  });

  if (listenersBound) return;
  listenersBound = true;

  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape" || !activeRoot?.isConnected) return;
    if (activeRoot.classList.contains("is-open")) {
      setOpen(activeRoot, false);
    }
  });

  document.addEventListener("click", (event) => {
    if (!activeRoot?.isConnected || !activeRoot.classList.contains("is-open")) return;
    if (activeRoot.contains(event.target)) return;
    setOpen(activeRoot, false);
  });
};
