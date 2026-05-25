const mix = require("laravel-mix");

const domain = "de-wereldvrede.ddev.site";

mix.browserSync({
  proxy: "https://" + domain,
  host: domain,
  open: "external",
  https: true,
});

mix.setPublicPath("assets");
mix
  .sourceMaps()
  .js("src/assets/js/main.js", "assets/js")
  .postCss("src/assets/css/main.css", "assets/css")
  .options({
    processCssUrls: false,
  })
  .copyDirectory("src/assets/images", "assets/images")
  .copyDirectory("src/assets/fonts", "assets/fonts")
  .version();
