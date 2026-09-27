window.wp?.domReady?.(() => {
  const unregister = window.wp?.blocks?.unregisterBlockStyle;

  if (!unregister) return;

  [
    ["core/image", "rounded"],
    ["core/button", "outline"],
    ["core/separator", "dots"],
    ["core/social-links", "logos-only"],
    ["core/social-links", "pill-shape"],
  ].forEach(([block, style]) => unregister(block, style));
});
