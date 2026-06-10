const HOVER_MEDIA = "(hover: hover)";
const OFFSET_X = 12;
const OFFSET_Y = 12;
const PORTRAIT_FADE_MS = 240;
const HIDE_DELAY_MS = 120;
const LERP = 0.18;
const PORTRAIT_SELECTOR = ".c-creators-list__name[data-portrait-src]";

let teardown = null;
let preview = null;

const destroyCreatorsListPortrait = () => {
  teardown?.();
  teardown = null;
};

const createPortraitImage = () => {
  const img = document.createElement("img");
  img.className = "c-creators-portrait__img";
  img.alt = "";
  img.decoding = "async";
  return img;
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

  const frame = document.createElement("div");
  frame.className = "c-creators-portrait__frame";

  const imgA = createPortraitImage();
  const imgB = createPortraitImage();
  imgA.classList.add("is-active");
  frame.append(imgA, imgB);
  preview.appendChild(frame);
  document.body.appendChild(preview);

  let activeButton = null;
  let activeImg = imgA;
  let inactiveImg = imgB;
  let hideTimer = null;
  let swapToken = 0;
  let rafId = null;
  let targetX = 0;
  let targetY = 0;
  let currentX = 0;
  let currentY = 0;

  const cancelHide = () => {
    if (hideTimer === null) return;
    clearTimeout(hideTimer);
    hideTimer = null;
  };

  const applyTransform = () => {
    preview.style.transform = `translate3d(${currentX}px, ${currentY}px, 0)`;
  };

  const stopMotion = () => {
    if (rafId === null) return;
    cancelAnimationFrame(rafId);
    rafId = null;
  };

  const tick = () => {
    const dx = targetX - currentX;
    const dy = targetY - currentY;

    if (Math.abs(dx) < 0.5 && Math.abs(dy) < 0.5) {
      currentX = targetX;
      currentY = targetY;
      applyTransform();
      stopMotion();
      return;
    }

    currentX += dx * LERP;
    currentY += dy * LERP;
    applyTransform();
    rafId = requestAnimationFrame(tick);
  };

  const snapPosition = (clientX, clientY) => {
    targetX = clientX + OFFSET_X;
    targetY = clientY + OFFSET_Y;
    currentX = targetX;
    currentY = targetY;
    applyTransform();
    stopMotion();
  };

  const setTargetPosition = (clientX, clientY) => {
    targetX = clientX + OFFSET_X;
    targetY = clientY + OFFSET_Y;

    if (rafId === null) {
      rafId = requestAnimationFrame(tick);
    }
  };

  const setImageAttributes = (img, url, srcset, sizes) => {
    img.src = url;
    if (srcset) {
      img.srcset = srcset;
    } else {
      img.removeAttribute("srcset");
    }
    if (sizes) {
      img.sizes = sizes;
    } else {
      img.removeAttribute("sizes");
    }
  };

  const waitForImage = (img) =>
    new Promise((resolve) => {
      if (img.complete && img.naturalWidth > 0) {
        resolve();
        return;
      }

      const onDone = () => {
        img.removeEventListener("load", onDone);
        img.removeEventListener("error", onDone);
        resolve();
      };

      img.addEventListener("load", onDone, { once: true });
      img.addEventListener("error", onDone, { once: true });
    });

  const finalizeHide = () => {
    hideTimer = null;
    if (preview.classList.contains("is-visible")) return;
    stopMotion();
    preview.hidden = true;
    activeImg.classList.remove("is-active");
    inactiveImg.classList.remove("is-active");
    activeImg.removeAttribute("src");
    activeImg.removeAttribute("srcset");
    activeImg.removeAttribute("sizes");
    inactiveImg.removeAttribute("src");
    inactiveImg.removeAttribute("srcset");
    inactiveImg.removeAttribute("sizes");
    imgA.classList.add("is-active");
    activeImg = imgA;
    inactiveImg = imgB;
  };

  const hide = () => {
    activeButton = null;
    preview.classList.remove("is-visible");
    cancelHide();
    hideTimer = setTimeout(finalizeHide, PORTRAIT_FADE_MS);
  };

  const scheduleHide = () => {
    cancelHide();
    hideTimer = setTimeout(() => {
      hideTimer = null;
      const hovered = list.querySelector(`${PORTRAIT_SELECTOR}:hover`);
      if (hovered) {
        showButton(hovered);
        return;
      }
      hide();
    }, HIDE_DELAY_MS);
  };

  const showPreview = () => {
    cancelHide();
    preview.hidden = false;

    if (!preview.classList.contains("is-visible")) {
      requestAnimationFrame(() => {
        preview.classList.add("is-visible");
      });
    }
  };

  const swapPortrait = async (url, srcset, sizes) => {
    if (activeImg.getAttribute("src") === url) return;

    const token = ++swapToken;
    setImageAttributes(inactiveImg, url, srcset, sizes);
    await waitForImage(inactiveImg);

    if (token !== swapToken || !activeButton) return;

    inactiveImg.classList.add("is-active");
    activeImg.classList.remove("is-active");

    const previousActive = activeImg;
    activeImg = inactiveImg;
    inactiveImg = previousActive;
  };

  const showButton = (button) => {
    const { portraitSrc: url, portraitSrcset: srcset, portraitSizes: sizes } =
      button.dataset;
    if (!url) return;

    cancelHide();
    activeButton = button;
    showPreview();

    if (activeImg.getAttribute("src") !== url) {
      swapPortrait(url, srcset, sizes);
    }
  };

  const resolveButton = (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return null;
    return target.closest(PORTRAIT_SELECTOR);
  };

  const onPointerMove = (event) => {
    const button = resolveButton(event);
    const isFirstShow = button && !preview.classList.contains("is-visible");

    if (isFirstShow) {
      snapPosition(event.clientX, event.clientY);
    } else {
      setTargetPosition(event.clientX, event.clientY);
    }

    if (button) {
      if (button !== activeButton) {
        showButton(button);
      }
      return;
    }

    if (activeButton && hideTimer === null) {
      scheduleHide();
    }
  };

  const onPointerLeave = () => {
    scheduleHide();
  };

  list.addEventListener("pointermove", onPointerMove);
  list.addEventListener("pointerleave", onPointerLeave);

  teardown = () => {
    list.removeEventListener("pointermove", onPointerMove);
    list.removeEventListener("pointerleave", onPointerLeave);
    cancelHide();
    swapToken += 1;
    activeButton = null;
    preview.classList.remove("is-visible");
    stopMotion();
    preview?.remove();
    preview = null;
  };
};
