import { openOverlay } from "./overlay";

const escapeHtml = (value = "") =>
  value
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");

const formatBio = (bio = "") => {
  const paragraphs = bio
    .split(/\n{2,}/)
    .map((part) => part.trim())
    .filter(Boolean);

  if (!paragraphs.length) return "";

  return paragraphs
    .map(
      (part) =>
        `<p>${escapeHtml(part).replace(/\n/g, "<br>")}</p>`
    )
    .join("");
};

const buildPortraitHtml = (portrait) => {
  if (!portrait?.src) return "";

  const src = escapeHtml(portrait.src);
  const srcset = portrait.srcset ? ` srcset="${escapeHtml(portrait.srcset)}"` : "";
  const sizes = portrait.sizes ? ` sizes="${escapeHtml(portrait.sizes)}"` : "";

  return `
    <div class="c-creator-overlay__portrait-col">
      <figure class="c-creator-overlay__portrait-wrap">
        <img
          src="${src}"${srcset}${sizes}
          alt=""
          class="c-creator-overlay__portrait"
          loading="lazy"
          decoding="async"
        >
      </figure>
    </div>`;
};

const buildCreatorHtml = (creator) => `
  <div class="c-creator-overlay__inner g-container">
    <div class="c-creator-overlay__content">
      <h2 class="c-creator-overlay__title t-display t-xxlarge t-uppercase" id="creator-overlay-title">${escapeHtml(creator.name)}</h2>
      ${creator.bio ? `<div class="c-creator-overlay__bio t-body-lg">${formatBio(creator.bio)}</div>` : ""}
    </div>
    ${buildPortraitHtml(creator.portrait)}
  </div>`;

export const initCreatorOverlay = () => {
  const template = document.getElementById("creators-data");
  const panel = document.querySelector("[data-creator-panel]");
  const overlay = document.querySelector('[data-overlay="creator"]');
  if (!template || !panel) return;

  let creators = [];
  try {
    // Template contents live in .content, not as direct child text nodes
    creators = JSON.parse(template.content.textContent.trim());
  } catch {
    return;
  }

  document.querySelectorAll("[data-creator-open]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const creator = creators[Number(btn.dataset.creatorIndex)];
      if (!creator) return;

      panel.innerHTML = buildCreatorHtml(creator);
      panel.setAttribute("aria-labelledby", "creator-overlay-title");
      openOverlay("creator");
    });
  });

  overlay?.addEventListener("click", (event) => {
    if (event.target.closest("[data-overlay-close]")) {
      panel.innerHTML = "";
      panel.removeAttribute("aria-labelledby");
    }
  });
};
