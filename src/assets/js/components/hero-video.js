import "@mux/mux-background-video/html";

const INTERACTIVE_SELECTOR =
  "a, button, input, select, textarea, label, [role=\"button\"], [data-no-trailer]";

const HERO_REVEAL_CLASSES = ["is-video-ready", "is-hero-revealed"];
const MOBILE_MQ = "(max-width: 768px)";

const FADE_MS = 350;

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

const isMobileHero = () => window.matchMedia(MOBILE_MQ).matches;

const cancelVolumeFade = () => {
  if (volumeFadeFrame !== null) {
    cancelAnimationFrame(volumeFadeFrame);
    volumeFadeFrame = null;
  }
};

const activateMuxSource = (active, all) => {
  for (const el of all) {
    const src = el.dataset.muxSrc || "";
    if (el === active) {
      // Set preload before src so the first Mux load() isn't stuck on preload=none.
      el.setAttribute("preload", "auto");
      if (src && el.getAttribute("src") !== src) {
        el.setAttribute("src", src);
      }
      continue;
    }

    if (el.hasAttribute("src")) {
      el.removeAttribute("src");
    }
    el.setAttribute("preload", "none");
    el.video?.pause();
  }
};

const resolveHeroVideo = (section) => {
  const muxes = [...section.querySelectorAll("mux-background-video")];
  if (muxes.length) {
    const preferred = isMobileHero() ? "mobile" : "desktop";
    const mux =
      muxes.find((el) => el.dataset.heroMux === preferred) ||
      muxes.find((el) => el.dataset.heroMux === "desktop") ||
      muxes[0];

    activateMuxSource(mux, muxes);

    if (mux?.video) return { video: mux.video, mux, isMux: true };
    return null;
  }

  const video = section.querySelector("video");
  return video ? { video, mux: null, isMux: false } : null;
};

export const destroyHeroVideo = () => {
  teardown?.();
  teardown = null;
};

export const initHeroVideo = () => {
  destroyHeroVideo();

  const section = document.querySelector("[data-has-video]");
  if (!section) return;

  const start = (media) => {
    if (!media) return;
    const { video, mux, isMux } = media;
    const soundBtn = document.querySelector("[data-hero-sound]");
    const label = section.querySelector("[data-hero-video-label]");
    const content = section.querySelector(".c-home-hero__content");
    const mobileQuery = window.matchMedia(MOBILE_MQ);
    let activeVariant = mux?.dataset.heroMux || null;

    let canPlayHandler = null;
    let revealPlayingHandler = null;
    let revealCanPlayHandler = null;
    let revealed = false;
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
        video.defaultMuted = true;
      }
      video.volume = 1;
      video.playsInline = true;
      const playPromise = video.play();
      if (playPromise?.catch) {
        playPromise.catch(() => {});
      }
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
      // Native file: Safari needs load() so muted autoplay can start.
      // Mux HLS manages its own load — calling video.load() breaks MSE.
      if (!isMux) {
        video.load();
      } else {
        ensurePlaying();
      }
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
      if (mux) {
        mux.toggleAttribute("audio", true);
      }
    };

    const unmuteFromUserGesture = () => {
      userWantsSound = true;
      cancelVolumeFade();
      if (mux) {
        mux.toggleAttribute("audio", true);
      }
      video.muted = false;
      video.volume = 1;
      updateSoundButton();
      video.play().catch(() => {
        updateSoundButton();
      });
    };

    const fadeVolumeTo = (from, to, onComplete) => {
      cancelVolumeFade();
      const startTime = performance.now();

      const step = (now) => {
        const progress = Math.min((now - startTime) / FADE_MS, 1);
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

    const onViewportChange = () => {
      if (!mux) return;
      const nextVariant = isMobileHero() ? "mobile" : "desktop";
      if (nextVariant === activeVariant) return;
      // Re-init so the other Mux source becomes the controlled player.
      initHeroVideo();
    };

    section.addEventListener("mousemove", onMove);
    section.addEventListener("mouseleave", onLeave);
    section.addEventListener("click", onSectionClick);
    const onVolumeChange = () => updateSoundButton();

    video.addEventListener("play", updateLabelText);
    video.addEventListener("pause", updateLabelText);
    video.addEventListener("volumechange", onVolumeChange);
    soundBtn?.addEventListener("pointerdown", onSoundPointerDown);
    soundBtn?.addEventListener("click", onSoundClick);
    if (typeof mobileQuery.addEventListener === "function") {
      mobileQuery.addEventListener("change", onViewportChange);
    } else {
      mobileQuery.addListener(onViewportChange);
    }

    updateLabelText();
    updateSoundButton();
    hideLabel();
    schedulePlayback();

    const onIntroComplete = () => {
      ensurePlaying();
      bindReveal();
    };
    document.addEventListener("dw:intro-complete", onIntroComplete);

    teardown = () => {
      cancelVolumeFade();
      document.removeEventListener("dw:intro-complete", onIntroComplete);
      if (typeof mobileQuery.removeEventListener === "function") {
        mobileQuery.removeEventListener("change", onViewportChange);
      } else {
        mobileQuery.removeListener(onViewportChange);
      }

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

  const muxHost = section.querySelector("mux-background-video");
  if (muxHost) {
    customElements.whenDefined("mux-background-video").then(() => {
      // Custom element upgrade can leave .video briefly unavailable.
      const tryStart = (attempt = 0) => {
        const media = resolveHeroVideo(section);
        if (media) {
          start(media);
          return;
        }
        if (attempt < 10) {
          requestAnimationFrame(() => tryStart(attempt + 1));
        }
      };
      tryStart();
    });
    return;
  }

  start(resolveHeroVideo(section));
};
