import Player from "@vimeo/player";
import { openOverlay } from "./overlay";

let activePlayer = null;
let activeVideo = null;
let controlTeardown = null;

const formatTime = (seconds) => {
  if (!Number.isFinite(seconds) || seconds < 0) return "0:00";
  const minutes = Math.floor(seconds / 60);
  const secs = Math.floor(seconds % 60);
  return `${minutes}:${secs.toString().padStart(2, "0")}`;
};

const getLabels = () => {
  const overlay = document.querySelector('[data-overlay="trailer"]');
  return {
    play: overlay?.dataset.labelPlay || "PLAY",
    pause: overlay?.dataset.labelPause || "PAUSE",
    soundOn: overlay?.dataset.labelSoundOn || "UNMUTE",
    soundOff: overlay?.dataset.labelSoundOff || "MUTE",
  };
};

const buildControlsHtml = () => `
  <div class="c-trailer-overlay__controls t-mono t-uppercase">
    <button type="button" class="c-trailer-overlay__control" data-trailer-play-pause aria-pressed="false"></button>
    <div class="c-trailer-overlay__controls-center">
      <div class="c-trailer-overlay__progress" data-trailer-progress-track aria-hidden="true">
        <div class="c-trailer-overlay__progress-bar" data-trailer-progress></div>
      </div>
      <span class="c-trailer-overlay__time" data-trailer-time>0:00</span>
    </div>
    <button type="button" class="c-trailer-overlay__control" data-trailer-mute aria-pressed="false"></button>
  </div>
`;

const wrapTrailerStage = (mediaHtml) => {
  if (!mediaHtml) return "";
  return `
    <div class="c-trailer-overlay__stage">
      ${mediaHtml}
      ${buildControlsHtml()}
    </div>
  `;
};

const wrapTrailerMedia = (html) =>
  html ? `<div class="c-trailer-overlay__embed">${html}</div>` : "";

const buildTrailerHtml = (vimeo, file) => {
  if (vimeo) {
    const id = vimeo.match(/vimeo\.com\/(?:video\/)?(\d+)/)?.[1];
    if (id) {
      const params = new URLSearchParams({
        autoplay: "1",
        background: "0",
        controls: "0",
        title: "0",
        byline: "0",
        portrait: "0",
        pip: "0",
        dnt: "1",
      });
      const iframe = `<iframe src="https://player.vimeo.com/video/${id}?${params}" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="Trailer"></iframe>`;
      return wrapTrailerStage(wrapTrailerMedia(iframe));
    }
  }
  if (file) {
    return wrapTrailerStage(
      wrapTrailerMedia(
        `<video src="${file}" autoplay muted playsinline></video>`
      )
    );
  }
  return "";
};

const getControlElements = (container) => ({
  playBtn: container.querySelector("[data-trailer-play-pause]"),
  muteBtn: container.querySelector("[data-trailer-mute]"),
  progressTrack: container.querySelector("[data-trailer-progress-track]"),
  progressBar: container.querySelector("[data-trailer-progress]"),
  timeEl: container.querySelector("[data-trailer-time]"),
});

const setProgressFill = (progressBar, seconds, duration) => {
  if (progressBar && duration > 0) {
    progressBar.style.setProperty("--progress", `${(seconds / duration) * 100}%`);
  }
};

const bindProgressSeek = (container, handlers) => {
  const { progressTrack, progressBar } = getControlElements(container);
  if (!progressTrack || !handlers.seek || !handlers.getDuration) return () => {};

  let dragging = false;

  const seekFromClientX = (clientX) => {
    handlers.getDuration().then((duration) => {
      if (!duration || duration <= 0) return;
      const rect = progressTrack.getBoundingClientRect();
      const ratio = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
      const time = ratio * duration;
      Promise.resolve(handlers.seek(time)).then(() => {
        setProgressFill(progressBar, time, duration);
      });
    });
  };

  const onPointerDown = (e) => {
    dragging = true;
    progressTrack.setPointerCapture(e.pointerId);
    seekFromClientX(e.clientX);
  };

  const onPointerMove = (e) => {
    if (!dragging) return;
    seekFromClientX(e.clientX);
  };

  const endDrag = (e) => {
    if (!dragging) return;
    dragging = false;
    progressTrack.releasePointerCapture(e.pointerId);
  };

  progressTrack.addEventListener("pointerdown", onPointerDown);
  progressTrack.addEventListener("pointermove", onPointerMove);
  progressTrack.addEventListener("pointerup", endDrag);
  progressTrack.addEventListener("pointercancel", endDrag);

  return () => {
    progressTrack.removeEventListener("pointerdown", onPointerDown);
    progressTrack.removeEventListener("pointermove", onPointerMove);
    progressTrack.removeEventListener("pointerup", endDrag);
    progressTrack.removeEventListener("pointercancel", endDrag);
  };
};

const bindMediaControls = (container, labels, handlers) => {
  const { playBtn, muteBtn } = getControlElements(container);
  if (!playBtn || !muteBtn) return () => {};

  const { getPaused, getMuted, togglePlay, toggleMute, onTimeUpdate, onPlay, onPause, onVolumeChange } =
    handlers;

  const setPlayLabel = (paused) => {
    playBtn.textContent = paused ? labels.play : labels.pause;
    playBtn.setAttribute("aria-pressed", paused ? "false" : "true");
  };

  const setMuteLabel = (muted) => {
    muteBtn.textContent = muted ? labels.soundOn : labels.soundOff;
    muteBtn.setAttribute("aria-pressed", muted ? "false" : "true");
  };

  const onPlayClick = () => {
    togglePlay();
  };

  const onMuteClick = () => {
    toggleMute();
  };

  playBtn.addEventListener("click", onPlayClick);
  muteBtn.addEventListener("click", onMuteClick);

  const unbindProgressSeek = bindProgressSeek(container, handlers);

  getPaused().then(setPlayLabel);
  getMuted().then(setMuteLabel);

  const unsubscribers = [
    onTimeUpdate?.((seconds) => {
      const { progressBar, timeEl } = getControlElements(container);
      timeEl && (timeEl.textContent = formatTime(seconds));
      if (progressBar && handlers.getDuration) {
        handlers.getDuration().then((duration) => {
          setProgressFill(progressBar, seconds, duration);
        });
      }
    }),
    onPlay?.(() => setPlayLabel(false)),
    onPause?.(() => setPlayLabel(true)),
    onVolumeChange?.((muted) => setMuteLabel(muted)),
  ].filter(Boolean);

  return () => {
    playBtn.removeEventListener("click", onPlayClick);
    muteBtn.removeEventListener("click", onMuteClick);
    unbindProgressSeek();
    unsubscribers.forEach((unsubscribe) => unsubscribe?.());
  };
};

const initVimeoControls = (container, iframe, labels) => {
  const player = new Player(iframe);
  activePlayer = player;
  let duration = 0;

  player.getDuration().then((value) => {
    duration = value;
  });

  controlTeardown = bindMediaControls(container, labels, {
    getPaused: () => player.getPaused(),
    getMuted: () => player.getMuted(),
    getDuration: () => Promise.resolve(duration),
    togglePlay: () =>
      player.getPaused().then((paused) => (paused ? player.play() : player.pause())),
    toggleMute: () =>
      player.getMuted().then((muted) => {
        if (muted) {
          return player.setMuted(false).then(() => player.play().catch(() => {}));
        }
        return player.setMuted(true);
      }),
    onTimeUpdate: (callback) => {
      player.on("timeupdate", (data) => callback(data.seconds));
      return () => player.off("timeupdate");
    },
    onPlay: (callback) => {
      player.on("play", callback);
      return () => player.off("play");
    },
    onPause: (callback) => {
      player.on("pause", callback);
      return () => player.off("pause");
    },
    onVolumeChange: (callback) => {
      player.on("volumechange", (data) => callback(Boolean(data.muted)));
      return () => player.off("volumechange");
    },
    seek: (seconds) => player.setCurrentTime(seconds),
  });
};

const initVideoControls = (container, video, labels) => {
  activeVideo = video;
  video.controls = false;

  controlTeardown = bindMediaControls(container, labels, {
    getPaused: () => Promise.resolve(video.paused),
    getMuted: () => Promise.resolve(video.muted),
    getDuration: () => Promise.resolve(video.duration || 0),
    togglePlay: () => {
      if (video.paused) video.play();
      else video.pause();
    },
    toggleMute: () => {
      if (video.muted) {
        video.muted = false;
        video.volume = 1;
        video.play().catch(() => {});
        return;
      }
      video.muted = true;
    },
    onTimeUpdate: (callback) => {
      const handler = () => callback(video.currentTime);
      video.addEventListener("timeupdate", handler);
      return () => video.removeEventListener("timeupdate", handler);
    },
    onPlay: (callback) => {
      video.addEventListener("play", callback);
      return () => video.removeEventListener("play", callback);
    },
    onPause: (callback) => {
      video.addEventListener("pause", callback);
      return () => video.removeEventListener("pause", callback);
    },
    onVolumeChange: (callback) => {
      const handler = () => callback(video.muted);
      video.addEventListener("volumechange", handler);
      return () => video.removeEventListener("volumechange", handler);
    },
    seek: (seconds) => {
      video.currentTime = seconds;
    },
  });
};

export const destroyTrailerMedia = () => {
  controlTeardown?.();
  controlTeardown = null;

  if (activePlayer) {
    activePlayer.destroy().catch(() => {});
    activePlayer = null;
  }

  if (activeVideo) {
    activeVideo.pause();
    activeVideo.removeAttribute("src");
    activeVideo.load();
    activeVideo = null;
  }
};

const initTrailerControls = (container) => {
  const labels = getLabels();
  const iframe = container.querySelector("iframe");
  const video = container.querySelector("video");

  if (iframe) {
    initVimeoControls(container, iframe, labels);
    return;
  }

  if (video) {
    initVideoControls(container, video, labels);
  }
};

export const openTrailerFromHost = (host) => {
  const container = document.querySelector("[data-trailer-container]");
  if (!container || !host) return;

  destroyTrailerMedia();

  const vimeo = (host.dataset.trailerVimeo || "").trim();
  const file = (host.dataset.trailerFile || "").trim();
  const html = buildTrailerHtml(vimeo, file);
  if (!html) return;

  container.innerHTML = html;
  openOverlay("trailer");
  initTrailerControls(container);
};

export const initTrailerOverlay = () => {
  const container = document.querySelector("[data-trailer-container]");
  if (!container) return;

  document.querySelectorAll("[data-trailer-trigger]").forEach((trigger) => {
    trigger.addEventListener("click", (e) => {
      e.stopPropagation();
      const host = trigger.closest("[data-trailer-vimeo], [data-trailer-file]") || trigger;
      openTrailerFromHost(host);
    });
  });

  document.querySelector('[data-overlay="trailer"]')?.addEventListener("click", (e) => {
    if (e.target.matches("[data-overlay-close]")) {
      destroyTrailerMedia();
      container.replaceChildren();
    }
  });
};
