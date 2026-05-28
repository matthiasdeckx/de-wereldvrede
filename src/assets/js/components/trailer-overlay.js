import { openOverlay, closeOverlays } from "./overlay";

const wrapTrailerMedia = (html) =>
  html ? `<div class="c-trailer-overlay__embed">${html}</div>` : "";

const buildTrailerHtml = (vimeo, file) => {
  if (vimeo) {
    const id = vimeo.match(/vimeo\.com\/(?:video\/)?(\d+)/)?.[1];
    if (id) {
      const params = new URLSearchParams({
        autoplay: "1",
        transparent: "1",
        title: "0",
        byline: "0",
        portrait: "0",
      });
      const iframe = `<iframe src="https://player.vimeo.com/video/${id}?${params}" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
      return wrapTrailerMedia(iframe);
    }
  }
  if (file) {
    return wrapTrailerMedia(
      `<video src="${file}" autoplay controls playsinline></video>`
    );
  }
  return "";
};

export const openTrailerFromHost = (host) => {
  const container = document.querySelector("[data-trailer-container]");
  if (!container || !host) return;

  const vimeo = (host.dataset.trailerVimeo || "").trim();
  const file = (host.dataset.trailerFile || "").trim();
  const html = buildTrailerHtml(vimeo, file);
  if (!html) return;

  container.innerHTML = html;
  openOverlay("trailer");
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

  document.querySelector("[data-overlay=\"trailer\"]")?.addEventListener("click", (e) => {
    if (e.target.matches("[data-overlay-close]")) {
      container.innerHTML = "";
    }
  });
};
