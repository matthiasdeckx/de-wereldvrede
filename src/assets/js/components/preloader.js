import lottie from "lottie-web";

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

/** True only on the first full-page load when the URL is the homepage. */
let shouldPlayHomeIntro = isHomePage();

const setIntroActive = (active) => {
  document.documentElement.classList.toggle(INTRO_ACTIVE_CLASS, active);
};

export const prepareHomeIntro = () => {
  if (!shouldPlayHomeIntro) {
    clearHomeIntro();
    return;
  }

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

const waitForLottieEnd = (animation, timeoutMs) =>
  new Promise((resolve, reject) => {
    if (!animation) {
      reject(new Error("no lottie animation"));
      return;
    }

    let settled = false;
    const finish = () => {
      if (settled) return;
      settled = true;
      animation.removeEventListener("complete", finish);
      animation.removeEventListener("data_failed", onError);
      animation.removeEventListener("error", onError);
      window.clearTimeout(timer);
      resolve();
    };

    const onError = () => {
      if (settled) return;
      settled = true;
      animation.removeEventListener("complete", finish);
      animation.removeEventListener("data_failed", onError);
      animation.removeEventListener("error", onError);
      window.clearTimeout(timer);
      reject(new Error("lottie failed"));
    };

    animation.addEventListener("complete", finish);
    animation.addEventListener("data_failed", onError);
    animation.addEventListener("error", onError);

    const timer = window.setTimeout(finish, timeoutMs);
  });

const dismissIntro = (root, video, fadeOutMs, animation, { immediate = false } = {}) => {
  animation?.destroy();
  root.classList.add("is-hiding");
  video?.pause();

  const complete = () => {
    root.classList.add("is-dismissed");
    root.setAttribute("aria-hidden", "true");
    root.dataset.introRunning = "false";
    setIntroActive(false);
  };

  if (immediate || fadeOutMs <= 0) {
    complete();
    return;
  }

  window.setTimeout(complete, fadeOutMs);
};

const skipIntro = (root) => {
  if (!root) return;

  root.dataset.introRunning = "false";
  dismissIntro(root, root.querySelector("[data-preloader-video]"), 0, null, {
    immediate: true,
  });
  document.dispatchEvent(new CustomEvent("dw:intro-complete"));
};

const playVideoIntro = (video, maxDurationMs) =>
  new Promise((resolve) => {
    if (!video) {
      resolve();
      return;
    }

    video.removeAttribute("hidden");
    video.muted = true;
    video.playsInline = true;
    video.currentTime = 0;

    const playPromise = video.play();
    if (playPromise?.catch) {
      playPromise.catch(() => resolve());
    }

    waitForVideoEnd(video, maxDurationMs).then(resolve);
  });

const playLottieIntro = (container, path) => {
  const animation = lottie.loadAnimation({
    container,
    renderer: "svg",
    loop: false,
    autoplay: true,
    path,
  });

  return animation;
};

const switchToVideoFallback = (root) => {
  const lottieEl = root.querySelector("[data-preloader-lottie]");
  const video = root.querySelector("[data-preloader-video]");

  lottieEl?.setAttribute("hidden", "");
  video?.removeAttribute("hidden");

  return video;
};

export const initPreloader = () => {
  if (!isHomePage()) {
    clearHomeIntro();
    return;
  }

  const root = document.querySelector("[data-preloader]");
  if (!root) {
    clearHomeIntro();
    return;
  }

  if (!shouldPlayHomeIntro) {
    skipIntro(root);
    return;
  }

  if (root.dataset.introRunning === "true") return;

  shouldPlayHomeIntro = false;

  const config = parseConfig(root);
  if (!config.enabled) {
    clearHomeIntro();
    return;
  }

  const fadeOutMs = config.fadeOutMs ?? 500;
  const maxDurationMs = config.maxDurationMs ?? 12000;
  const video = root.querySelector("[data-preloader-video]");
  const lottieContainer = root.querySelector("[data-preloader-lottie]");
  const poster = root.querySelector("[data-preloader-poster]");
  const skipHint = root.querySelector("[data-preloader-skip-hint]");
  const allowSkip = config.allowSkip !== false;
  const introType = config.type ?? "video";
  const lottiePath = config.lottie?.path;
  let finished = false;
  let animation = null;
  let maxTimer = null;

  const finish = () => {
    if (finished) return;
    finished = true;
    root.dataset.introRunning = "false";
    if (maxTimer) window.clearTimeout(maxTimer);

    dismissIntro(root, video, fadeOutMs, animation);
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

  maxTimer = window.setTimeout(finish, maxDurationMs);

  const runVideoIntro = async () => {
    const activeVideo = switchToVideoFallback(root) ?? video;
    await playVideoIntro(activeVideo, maxDurationMs);
    finish();
  };

  if (prefersReducedMotion()) {
    if (config.reducedMotion === "poster" && poster) {
      poster.hidden = false;
      lottieContainer?.setAttribute("hidden", "");
      video?.setAttribute("hidden", "");
      return;
    }

    finish();
    return;
  }

  if (introType === "lottie" && lottieContainer && lottiePath) {
    try {
      animation = playLottieIntro(lottieContainer, lottiePath);
      waitForLottieEnd(animation, maxDurationMs)
        .then(finish)
        .catch(() => {
          if (config.fallback === "video" && video) {
            animation?.destroy();
            animation = null;
            runVideoIntro();
            return;
          }

          finish();
        });
      return;
    } catch {
      if (config.fallback === "video" && video) {
        runVideoIntro();
        return;
      }

      finish();
      return;
    }
  }

  if (video) {
    playVideoIntro(video, maxDurationMs).then(finish);
    return;
  }

  finish();
};
