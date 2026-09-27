export default {
  plugins: {
    "postcss-import": {},
    "postcss-preset-env": {
      stage: 2,
      features: {
        "nesting-rules": true,
        "color-mix": true
      }
    },
    cssnano: {
      preset: "default"
    }
  }
};
