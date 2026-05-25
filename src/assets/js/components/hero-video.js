export const initHeroVideo = () => {
  const section = document.querySelector("[data-has-video]");
  const video = section?.querySelector("video");
  const soundBtn = document.querySelector("[data-hero-sound]");
  if (!section || !video) return;

  section.addEventListener("click", (e) => {
    if (e.target.closest("[data-hero-sound]")) return;
    if (video.paused) {
      video.play();
      section.classList.remove("is-paused");
    } else {
      video.pause();
      section.classList.add("is-paused");
    }
  });

  soundBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    video.muted = !video.muted;
    soundBtn.setAttribute("aria-pressed", String(!video.muted));
    soundBtn.textContent = video.muted ? "Unmute" : "Mute";
  });
};
