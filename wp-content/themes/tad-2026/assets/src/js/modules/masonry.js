/* Masonry for [data-masonry] grids.
   CSS makes a normal grid; here every card gets a row-span from its real height
   (rows are 2px tall), so cards of different heights pack into columns. DOM order is
   left alone, so keyboard / screen-reader order = reading order. */
const UNIT = 2;

export function initMasonry() {
  const grids = document.querySelectorAll("[data-masonry]");
  if (!grids.length || !window.CSS || !CSS.supports("display", "grid")) return;

  grids.forEach((grid) => {
    let frame = 0;

    const layout = () => {
      frame = 0;
      const items = Array.from(grid.children);
      const gap = parseFloat(getComputedStyle(grid).columnGap) || 0;

      // measure everything first, then write (no layout thrashing)
      items.forEach((item) => (item.style.gridRowEnd = ""));
      const heights = items.map((item) => item.offsetHeight);
      items.forEach((item, i) => {
        item.style.gridRowEnd = `span ${Math.max(1, Math.ceil((heights[i] + gap) / UNIT))}`;
      });
    };

    const schedule = () => {
      if (!frame) frame = requestAnimationFrame(layout);
    };

    grid.classList.add("is-masonry");
    layout();

    if ("ResizeObserver" in window) {
      const ro = new ResizeObserver(schedule);
      Array.from(grid.children).forEach((item) => ro.observe(item));
    } else {
      window.addEventListener("resize", schedule);
    }

    grid.querySelectorAll("img").forEach((img) => {
      if (!img.complete) img.addEventListener("load", schedule, { once: true });
    });

    if (document.fonts && document.fonts.ready) document.fonts.ready.then(schedule);
    window.addEventListener("load", schedule);
  });
}
