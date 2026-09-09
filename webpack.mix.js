const mix = require("laravel-mix");

const domain = "de-wereldvrede.ddev.site";

mix.browserSync({
  proxy: "https://" + domain,
  host: domain,
  open: "external",
  https: true,
});

mix.setPublicPath("assets");

if (!mix.inProduction()) {
  mix.sourceMaps();
}

mix
  .js("src/assets/js/main.js", "assets/js")
  .postCss("src/assets/css/main.css", "assets/css")
  .options({
    processCssUrls: false,
  })
  .copyDirectory("src/assets/images", "assets/images")
  .copyDirectory("src/assets/fonts", "assets/fonts")
  // Runtime preloader assets only — keep source ProRes/full .mov files out of public/
  .copy("src/assets/preloader/preloader.json", "assets/preloader")
  .copy("src/assets/preloader/leader.json", "assets/preloader")
  .copy("src/assets/preloader/preloader.webm", "assets/preloader")
  .copy("src/assets/preloader/preloader.mov", "assets/preloader")
  .version();
