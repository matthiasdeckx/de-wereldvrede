const postcssPresetEnv = require("postcss-preset-env");
const purgecss = require("@fullhuman/postcss-purgecss");
const cssnano = require("cssnano");

module.exports = {
  plugins: [
    require("postcss-mixins"),
    require("postcss-simple-vars"),
    require("postcss-nested"),
    postcssPresetEnv({
      importFrom: ["./src/assets/css/settings.css"],
    }),
    process.env.NODE_ENV === "production" ? require("autoprefixer") : null,
    process.env.NODE_ENV === "production"
      ? cssnano({ preset: "default" })
      : null,
    // process.env.NODE_ENV === "production"
    //   ? purgecss({
    //       content: ["./**/*.php"],
    //       defaultExtractor: (content) => content.match(/[\w-/:]+(?<!:)/g) || [],
    //       safelist: {
    //         standard: [/^swiper-/, /^is-/, /^has-/, /^js-/, /^lazyload/, /^lazyloaded/, /^lazyloading/, /^data-/, /^aria-/, /^transition-/],
    //         deep:  [/^swiper-/, /^is-/, /^has-/, /^js-/, /^lazyload/, /^lazyloaded/, /^lazyloading/, /^data-/, /^aria-/, /^transition-/],
    //         greedy:  [/^swiper-/, /^is-/, /^has-/, /^js-/, /^lazyload/, /^lazyloaded/, /^lazyloading/, /^data-/, /^aria-/, /^transition-/],
    //       },
    //     })
    //   : undefined,
  ],
};
