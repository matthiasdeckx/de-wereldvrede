const syncPageStyle = () => {
  const meta = document.querySelector("#main [data-page-meta]");
  if (!meta) return;

  const isLight = meta.dataset.pageTheme === "light";
  const isHome = meta.dataset.pageHome === "true";

  document.body.classList.toggle("is-light", isLight);
  document.body.classList.toggle("site-page-home", isHome);

  const themeColor = document.querySelector('meta[name="theme-color"]');
  if (themeColor) {
    themeColor.setAttribute("content", isLight ? "#ffffff" : "#000000");
  }

  document.documentElement.style.colorScheme = isLight ? "light" : "dark";
};

export { syncPageStyle };
