import { openOverlay } from "./overlay";

export const initCreatorOverlay = () => {
  const template = document.getElementById("creators-data");
  const panel = document.querySelector("[data-creator-panel]");
  if (!template || !panel) return;

  let creators = [];
  try {
    creators = JSON.parse(template.textContent.trim());
  } catch {
    return;
  }

  document.querySelectorAll("[data-creator-open]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const creator = creators[Number(btn.dataset.creatorIndex)];
      if (!creator) return;
      panel.innerHTML = `
        <div class="c-creator-overlay__inner g-container">
          <h2 class="t-display t-uppercase">${creator.name}</h2>
          <p class="t-mono t-uppercase">${creator.role || ""}</p>
          ${creator.portrait ? `<img src="${creator.portrait}" alt="" class="c-creator-overlay__portrait">` : ""}
          <div class="c-creator-overlay__bio">${creator.bio || ""}</div>
          ${creator.productions?.length ? `<ul class="c-creator-overlay__productions t-mono t-uppercase">${creator.productions.map((p) => `<li><a href="${p.url}">${p.title}</a></li>`).join("")}</ul>` : ""}
        </div>`;
      openOverlay("creator");
    });
  });
};
