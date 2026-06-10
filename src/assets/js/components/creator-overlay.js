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

const externalLinkIcon = `<svg class="c-icon-external" width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M4.625 0.25H7.25V2.875M7.25 0.25L2.875 4.625M2.875 0.25H0.25V7.25H7.25V4.625" stroke="currentColor" stroke-width="0.5"/></svg>`;

const buildExternalLinkHtml = (creator) => {
  const url = creator.external_link?.trim();
  if (!url) return "";

  const label = escapeHtml(creator.external_link_label?.trim() || "IMDB");
  const href = escapeHtml(url);

  return `
      <a
        class="c-external-link t-mono t-uppercase"
        href="${href}"
        target="_blank"
        rel="noopener noreferrer"
      >${label} ${externalLinkIcon}</a>`;
};

const buildProductionsHtml = (creator, productionsLabel = "PRODUCTIONS") => {
  const productions = creator.productions?.filter(
    (production) => production?.title && production?.url
  );
  if (!productions?.length) return "";

  const items = productions
    .map((production) => {
      const title = escapeHtml(production.title);
      const url = escapeHtml(production.url);
      const year = production.year ? String(production.year).trim() : "";
      const yearHtml = year
        ? `<span class="c-creator-overlay__production-year">${escapeHtml(year)}</span>`
        : "";

      return `
        <div class="c-creator-overlay__production t-mono t-uppercase" role="listitem">
          <span class="c-creator-overlay__production-title">
            <a href="${url}">${title}</a>
          </span>
          ${yearHtml}
        </div>`;
    })
    .join("");

  return `
    <section class="c-creator-overlay__productions">
      <div class="c-creator-overlay__productions-layout">
        <p class="c-creator-overlay__productions-label t-mono t-uppercase">${escapeHtml(productionsLabel)}</p>
        <div class="c-creator-overlay__productions-list" role="list">
          ${items}
        </div>
      </div>
    </section>`;
};

const buildPortraitHtml = (creator) => {
  const portrait = creator.portrait;
  const externalLinkHtml = buildExternalLinkHtml(creator);
  if (!portrait?.src && !externalLinkHtml) return "";

  const portraitFigure = portrait?.src
    ? (() => {
        const src = escapeHtml(portrait.src);
        const srcset = portrait.srcset ? ` srcset="${escapeHtml(portrait.srcset)}"` : "";
        const sizes = portrait.sizes ? ` sizes="${escapeHtml(portrait.sizes)}"` : "";

        return `
      <figure class="c-creator-overlay__portrait-wrap">
        <img
          src="${src}"${srcset}${sizes}
          alt=""
          class="c-creator-overlay__portrait"
          loading="lazy"
          decoding="async"
        >
      </figure>`;
      })()
    : "";

  return `
    <div class="c-creator-overlay__portrait-col">
      ${portraitFigure}${externalLinkHtml}
    </div>`;
};

const buildCreatorHtml = (creator, productionsLabel = "PRODUCTIONS") => `
  <div class="c-creator-overlay__body g-container">
    <div class="c-creator-overlay__inner">
      <div class="c-creator-overlay__content">
        <h2 class="c-creator-overlay__title t-display t-xxlarge t-uppercase" id="creator-overlay-title">${escapeHtml(creator.name)}</h2>
        ${creator.bio ? `<div class="c-creator-overlay__bio t-body-lg">${formatBio(creator.bio)}</div>` : ""}
      </div>
      ${buildPortraitHtml(creator)}
    </div>
    ${buildProductionsHtml(creator, productionsLabel)}
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

  const productionsLabel = template.dataset.productionsLabel?.trim() || "PRODUCTIONS";

  document.querySelectorAll("[data-creator-open]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const creator = creators[Number(btn.dataset.creatorIndex)];
      if (!creator) return;

      panel.innerHTML = buildCreatorHtml(creator, productionsLabel);
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
