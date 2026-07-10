const INTERACTIVE_SELECTOR =
  "a, button, input, select, textarea, label, [role=\"button\"], [data-no-trailer]";

const HERO_REVEAL_CLASSES = ["is-video-ready", "is-hero-revealed"];

const FADE_MS = 350;
const HERO_VISIBILITY_RATIO = 0.5;

let teardown = null;
let volumeFadeFrame = null;

const volumeControllable = (() => {
  try {
    const probe = document.createElement("video");
    const testVolume = 0.5;
    probe.volume = testVolume;
    return Math.abs(probe.volume - testVolume) < 0.01;
  } catch {
    return false;
  }
})();

const prefersReducedMotion = () =>
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const cancelVolumeFade = () => {
  if (volumeFadeFrame !== null) {
    cancelAnimationFrame(volumeFadeFrame);
    volumeFadeFrame = null;
  }
};

export const destroyHeroVideo = () => {
  teardown?.();
  teardown = null;
};

export const initHeroVideo = () => {
  destroyHeroVideo();

  const section = document.querySelector("[data-has-video]");
  const video = section?.querySelector("video");
  const soundBtn = document.querySelector("[data-hero-sound]");
  const label = section?.querySelector("[data-hero-video-label]");
  const content = section?.querySelector(".c-home-hero__content");
  if (!section || !video) return;

  let canPlayHandler = null;
  let revealPlayingHandler = null;
  let revealCanPlayHandler = null;
  let revealed = false;
  let heroVisibilityObserver = null;
  let scrolledAway = false;
  let wasPlaying = false;
  let wasUnmuted = false;
  let userWantsSound = false;

  const resetHeroReveal = () => {
    revealed = false;
    section.classList.remove(...HERO_REVEAL_CLASSES);
  };

  const revealHero = () => {
    if (revealed) return;
    revealed = true;
    section.classList.add("is-video-ready");

    if (prefersReducedMotion()) {
      section.classList.add("is-hero-revealed");
      return;
    }

    requestAnimationFrame(() => {
      section.classList.add("is-hero-revealed");
    });
  };

  const clearRevealListeners = () => {
    if (revealPlayingHandler) {
      video.removeEventListener("playing", revealPlayingHandler);
      revealPlayingHandler = null;
    }
    if (revealCanPlayHandler) {
      video.removeEventListener("canplay", revealCanPlayHandler);
      revealCanPlayHandler = null;
    }
  };

  const bindReveal = () => {
    clearRevealListeners();

    const tryReveal = () => {
      if (video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) return;
      revealHero();
    };

    revealPlayingHandler = () => tryReveal();
    revealCanPlayHandler = () => {
      ensurePlaying();
      tryReveal();
    };

    video.addEventListener("playing", revealPlayingHandler, { once: true });
    video.addEventListener("canplay", revealCanPlayHandler, { once: true });

    if (
      video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA &&
      !video.paused
    ) {
      tryReveal();
    }
  };

  const ensurePlaying = () => {
    if (!userWantsSound) {
      video.muted = true;
    }
    video.volume = 1;
    video.playsInline = true;
    video.play().catch(() => {});
  };

  const schedulePlayback = () => {
    resetHeroReveal();
    bindReveal();

    if (!video.paused) {
      ensurePlaying();
      return;
    }

    if (video.readyState >= HTMLMediaElement.HAVE_FUTURE_DATA) {
      ensurePlaying();
      return;
    }

    canPlayHandler = () => ensurePlaying();
    video.addEventListener("canplay", canPlayHandler, { once: true });
    video.load();
  };

  const playText = label?.dataset.labelPlay || "Play";
  const pauseText = label?.dataset.labelPause || "Pause";
  const soundOnText = soundBtn?.dataset.labelSoundOn || "Unmute";
  const soundOffText = soundBtn?.dataset.labelSoundOff || "Mute";

  const isOverContent = (target) =>
    content && content.contains(target) && target !== content;

  const updateLabelText = () => {
    if (!label) return;
    label.textContent = video.paused ? playText : pauseText;
    section.classList.toggle("is-paused", video.paused);
  };

  const updateSoundButton = () => {
    if (!soundBtn) return;
    soundBtn.setAttribute("aria-pressed", String(!video.muted));
    soundBtn.textContent = video.muted ? soundOnText : soundOffText;
  };

  const setMutedInstant = (muted) => {
    cancelVolumeFade();
    userWantsSound = !muted;
    video.muted = muted;
    video.volume = 1;
  };

  const unmuteFromUserGesture = () => {
    userWantsSound = true;
    cancelVolumeFade();
    video.muted = false;
    video.volume = 1;
    updateSoundButton();
    video.play().catch(() => {
      updateSoundButton();
    });
  };

  const fadeVolumeTo = (from, to, onComplete) => {
    cancelVolumeFade();
    const start = performance.now();

    const step = (now) => {
      const progress = Math.min((now - start) / FADE_MS, 1);
      video.volume = from + (to - from) * progress;

      if (progress < 1) {
        volumeFadeFrame = requestAnimationFrame(step);
        return;
      }

      volumeFadeFrame = null;
      video.volume = to;
      onComplete?.();
    };

    volumeFadeFrame = requestAnimationFrame(step);
  };

  const setMutedWithFade = (muted, { instant = false } = {}) => {
    cancelVolumeFade();

    if (muted && video.muted) {
      updateSoundButton();
      return;
    }
    if (!muted) {
      return;
    }

    userWantsSound = false;

    if (instant || prefersReducedMotion() || !volumeControllable) {
      setMutedInstant(true);
      updateSoundButton();
      return;
    }

    const from = video.volume > 0 ? video.volume : 1;
    fadeVolumeTo(from, 0, () => {
      video.muted = true;
      video.volume = 1;
      updateSoundButton();
    });
  };

  const showLabel = () => {
    if (!label) return;
    label.hidden = false;
    label.setAttribute("aria-hidden", "false");
    label.classList.add("is-visible");
  };

  const hideLabel = () => {
    if (!label) return;
    label.classList.remove("is-visible", "is-over-content");
    label.hidden = true;
    label.setAttribute("aria-hidden", "true");
  };

  const onMove = (event) => {
    if (!label) return;
    if (event.target.closest(INTERACTIVE_SELECTOR)) {
      hideLabel();
      return;
    }
    const rect = section.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    label.style.setProperty("--trailer-cursor-x", `${x}px`);
    label.style.setProperty("--trailer-cursor-y", `${y}px`);
    showLabel();
    label.classList.toggle("is-over-content", isOverContent(event.target));
  };

  const onLeave = () => {
    hideLabel();
  };

  const onSectionClick = (e) => {
    if (e.target.closest("[data-hero-sound]")) return;
    if (e.target.closest(".c-home-hero__title")) return;
    if (e.defaultPrevented) return;
    if (video.paused) {
      video.play();
    } else {
      video.pause();
    }
  };

  const toggleSound = () => {
    if (video.muted || video.volume === 0) {
      unmuteFromUserGesture();
      return;
    }
    setMutedInstant(true);
    updateSoundButton();
  };

  const onSoundPointerDown = (e) => {
    if (e.pointerType === "mouse" && e.button !== 0) return;
    e.stopPropagation();
  };

  const onSoundClick = (e) => {
    e.preventDefault();
    e.stopPropagation();
    toggleSound();
  };

  const pauseForScrollAway = () => {
    if (!video.paused) {
      video.pause();
      updateLabelText();
    }
  };

  const restoreAfterScrollBack = () => {
    if (wasPlaying) {
      if (!userWantsSound) {
        video.muted = true;
      }
      video.volume = 1;
      video.play().catch(() => {});
    }
    if (wasUnmuted) {
      userWantsSound = true;
      video.muted = true;
      updateSoundButton();
    }
  };

  const handleHeroVisibility = (entry) => {
    const inView =
      entry.isIntersecting && entry.intersectionRatio >= HERO_VISIBILITY_RATIO;

    if (!inView) {
      if (scrolledAway || entry.isIntersecting) return;
      scrolledAway = true;
      wasPlaying = !video.paused;
      wasUnmuted = !video.muted;

      if (wasPlaying) pauseForScrollAway();
      if (wasUnmuted) setMutedWithFade(true);
      return;
    }

    if (!scrolledAway) return;
    scrolledAway = false;
    restoreAfterScrollBack();
  };

  const scrollRoot = document.querySelector("[data-home-scroll]");
  if (scrollRoot) {
    heroVisibilityObserver = new IntersectionObserver(
      (entries) => entries.forEach(handleHeroVisibility),
      { root: scrollRoot, threshold: [0, 0.5, 1] }
    );
    heroVisibilityObserver.observe(section);
  }

  section.addEventListener("mousemove", onMove);
  section.addEventListener("mouseleave", onLeave);
  section.addEventListener("click", onSectionClick);
  const onVolumeChange = () => updateSoundButton();

  video.addEventListener("play", updateLabelText);
  video.addEventListener("pause", updateLabelText);
  video.addEventListener("volumechange", onVolumeChange);
  soundBtn?.addEventListener("pointerdown", onSoundPointerDown);
  soundBtn?.addEventListener("click", onSoundClick);

  updateLabelText();
  updateSoundButton();
  hideLabel();
  schedulePlayback();

  teardown = () => {
    heroVisibilityObserver?.disconnect();
    heroVisibilityObserver = null;
    scrolledAway = false;
    cancelVolumeFade();

    if (canPlayHandler) {
      video.removeEventListener("canplay", canPlayHandler);
      canPlayHandler = null;
    }
    clearRevealListeners();
    video.pause();
    resetHeroReveal();
    section.removeEventListener("mousemove", onMove);
    section.removeEventListener("mouseleave", onLeave);
    section.removeEventListener("click", onSectionClick);
    video.removeEventListener("play", updateLabelText);
    video.removeEventListener("pause", updateLabelText);
    video.removeEventListener("volumechange", onVolumeChange);
    soundBtn?.removeEventListener("pointerdown", onSoundPointerDown);
    soundBtn?.removeEventListener("click", onSoundClick);
    hideLabel();
    label?.style.removeProperty("--trailer-cursor-x");
    label?.style.removeProperty("--trailer-cursor-y");
  };
};
