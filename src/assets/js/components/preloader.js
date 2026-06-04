const INTRO_ACTIVE_CLASS = "is-home-intro-active";

const prefersReducedMotion = () =>
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const parseConfig = (root) => {
  try {
    return JSON.parse(root.dataset.preloaderConfig || "{}");
  } catch {
    return {};
  }
};

const isHomePage = () =>
  Boolean(document.querySelector('#main [data-page-meta][data-page-home="true"]'));

const setIntroActive = (active) => {
  document.documentElement.classList.toggle(INTRO_ACTIVE_CLASS, active);
};

export const prepareHomeIntro = () => {
  if (!isHomePage()) return;

  const root = document.querySelector("[data-preloader]");
  if (!root) return;

  const config = parseConfig(root);
  if (!config.enabled) return;
  if (prefersReducedMotion() && config.reducedMotion === "skip") return;

  setIntroActive(true);
};

export const clearHomeIntro = () => {
  setIntroActive(false);
};

const waitForVideoEnd = (video, timeoutMs) =>
  new Promise((resolve) => {
    if (!video) {
      resolve();
      return;
    }

    let settled = false;
    const finish = () => {
      if (settled) return;
      settled = true;
      video.removeEventListener("ended", finish);
      video.removeEventListener("error", finish);
      window.clearTimeout(timer);
      resolve();
    };

    if (video.ended) {
      finish();
      return;
    }

    video.addEventListener("ended", finish, { once: true });
    video.addEventListener("error", finish, { once: true });

    const timer = window.setTimeout(finish, timeoutMs);
  });

const dismissIntro = (root, video, fadeOutMs) => {
  root.classList.add("is-hiding");
  window.setTimeout(() => {
    root.classList.add("is-dismissed");
    root.setAttribute("aria-hidden", "true");
    setIntroActive(false);
    video?.pause();
  }, fadeOutMs);
};

export const initPreloader = () => {
  if (!isHomePage()) {
    clearHomeIntro();
    return;
  }

  const root = document.querySelector("[data-preloader]");
  if (!root || root.dataset.introRunning === "true") return;

  const config = parseConfig(root);
  if (!config.enabled) {
    clearHomeIntro();
    return;
  }

  const fadeOutMs = config.fadeOutMs ?? 500;
  const maxDurationMs = config.maxDurationMs ?? 12000;
  const video = root.querySelector("[data-preloader-video]");
  const poster = root.querySelector("[data-preloader-poster]");
  const skipHint = root.querySelector("[data-preloader-skip-hint]");
  const allowSkip = config.allowSkip !== false;
  let finished = false;

  const finish = () => {
    if (finished) return;
    finished = true;
    root.dataset.introRunning = "false";

    dismissIntro(root, video, fadeOutMs);
    document.dispatchEvent(new CustomEvent("dw:intro-complete"));
  };

  root.dataset.introRunning = "true";
  root.classList.remove("is-hiding", "is-dismissed");
  root.setAttribute("aria-hidden", "false");
  root.setAttribute("role", allowSkip ? "button" : "status");

  if (allowSkip && skipHint) {
    root.setAttribute("aria-label", skipHint.textContent.trim());
  } else {
    root.removeAttribute("aria-label");
    root.setAttribute("aria-live", "polite");
  }
  setIntroActive(true);

  if (allowSkip) {
    root.addEventListener("click", finish, { once: true });

    const onSkipKeydown = (event) => {
      if (event.key !== "Escape" && event.key !== "Enter" && event.key !== " ") {
        return;
      }

      event.preventDefault();
      root.removeEventListener("keydown", onSkipKeydown);
      finish();
    };

    root.addEventListener("keydown", onSkipKeydown);
  }

  if (prefersReducedMotion()) {
    if (config.reducedMotion === "poster" && poster) {
      poster.hidden = false;
      video?.setAttribute("hidden", "");
      window.setTimeout(finish, maxDurationMs);
      return;
    }

    finish();
    return;
  }

  if (!video) {
    finish();
    return;
  }

  video.muted = true;
  video.playsInline = true;
  video.currentTime = 0;

  const playPromise = video.play();
  if (playPromise?.catch) {
    playPromise.catch(() => finish());
  }

  const maxTimer = window.setTimeout(finish, maxDurationMs);

  waitForVideoEnd(video, maxDurationMs).then(() => {
    window.clearTimeout(maxTimer);
    finish();
  });
};
