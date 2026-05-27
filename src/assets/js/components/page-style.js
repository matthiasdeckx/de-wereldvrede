const syncPageThemeClasses = () => {
  const meta = document.querySelector("#main [data-page-meta]");
  if (!meta) return;

  const isLight = meta.dataset.pageTheme === "light";
  const isHome = meta.dataset.pageHome === "true";

  document.body.classList.toggle("is-light", isLight);
  document.body.classList.toggle("site-page-home", isHome);
};

const syncPageBrowserChrome = () => {
  const meta = document.querySelector("#main [data-page-meta]");
  if (!meta) return;

  const isLight = meta.dataset.pageTheme === "light";

  const themeColor = document.querySelector('meta[name="theme-color"]');
  if (themeColor) {
    themeColor.setAttribute("content", isLight ? "#ffffff" : "#000000");
  }

  document.documentElement.style.colorScheme = isLight ? "light" : "dark";
};

const syncPageStyle = ({ syncBrowserChrome = true } = {}) => {
  syncPageThemeClasses();
  if (syncBrowserChrome) {
    syncPageBrowserChrome();
  }
};

export { syncPageStyle, syncPageThemeClasses, syncPageBrowserChrome };
