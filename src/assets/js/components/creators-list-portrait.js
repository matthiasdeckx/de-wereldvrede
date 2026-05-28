const HOVER_MEDIA = "(hover: hover)";
const OFFSET_X = 12;
const OFFSET_Y = 12;
const PORTRAIT_FADE_MS = 240;
const PORTRAIT_SELECTOR = ".c-creators-list__name[data-portrait]";

let teardown = null;
let preview = null;

const destroyCreatorsListPortrait = () => {
  teardown?.();
  teardown = null;
};

export const initCreatorsListPortrait = () => {
  destroyCreatorsListPortrait();

  if (!window.matchMedia(HOVER_MEDIA).matches) return;

  const list = document.querySelector(".c-creators-list");
  if (!list) return;

  preview = document.createElement("div");
  preview.className = "c-creators-portrait";
  preview.hidden = true;
  preview.setAttribute("aria-hidden", "true");

  const img = document.createElement("img");
  img.alt = "";
  preview.appendChild(img);
  document.body.appendChild(preview);

  let activeButton = null;
  let hideTimer = null;

  const cancelHide = () => {
    if (hideTimer === null) return;
    clearTimeout(hideTimer);
    hideTimer = null;
  };

  const finalizeHide = () => {
    hideTimer = null;
    if (preview.classList.contains("is-visible")) return;
    preview.hidden = true;
    img.removeAttribute("src");
  };

  const hide = (immediate = false) => {
    activeButton = null;
    preview.classList.remove("is-visible");
    cancelHide();

    if (immediate) {
      finalizeHide();
      return;
    }

    hideTimer = setTimeout(finalizeHide, PORTRAIT_FADE_MS);
  };

  const isPortraitTarget = (node) =>
    node instanceof Element && node.matches(PORTRAIT_SELECTOR) && list.contains(node);

  const position = (event) => {
    preview.style.left = `${event.clientX + OFFSET_X}px`;
    preview.style.top = `${event.clientY + OFFSET_Y}px`;
  };

  const onEnter = (event) => {
    const button = event.currentTarget;
    const url = button.dataset.portrait;
    if (!url) return;

    cancelHide();
    activeButton = button;

    if (img.getAttribute("src") !== url) {
      img.src = url;
    }

    preview.hidden = false;

    if (!preview.classList.contains("is-visible")) {
      requestAnimationFrame(() => {
        preview.classList.add("is-visible");
      });
    }

    position(event);
  };

  const onMove = (event) => {
    if (event.currentTarget !== activeButton) return;
    position(event);
  };

  const onLeave = (event) => {
    if (isPortraitTarget(event.relatedTarget)) return;
    hide();
  };

  const names = list.querySelectorAll(PORTRAIT_SELECTOR);

  names.forEach((button) => {
    button.addEventListener("mouseenter", onEnter);
    button.addEventListener("mousemove", onMove);
    button.addEventListener("mouseleave", onLeave);
  });

  teardown = () => {
    names.forEach((button) => {
      button.removeEventListener("mouseenter", onEnter);
      button.removeEventListener("mousemove", onMove);
      button.removeEventListener("mouseleave", onLeave);
    });
    hide(true);
    preview?.remove();
    preview = null;
  };
};
