/* Accent section: scroll-linked parallax for the wave layers.
   CSS does the drawing; this only writes one unitless number (--pp) per edge:
     spread   0 → 1     layers fan out as the edge travels up the screen
     collapse 1 → 0     layers converge
     drift   -1 → 1     layers slide past each other at different speeds
   Each layer multiplies --pp by its own --k and the --acc-strength distance. */
import { motionReduced } from "./motion.js";

const clamp = (n, lo, hi) => Math.min(hi, Math.max(lo, n));

export function initAccent() {
  const sections = Array.from(document.querySelectorAll("[data-acc]"));
  if (!sections.length) return;

  const visible = new Set();
  let ticking = false;

  const map = {
    spread: (p) => p,
    collapse: (p) => 1 - p,
    drift: (p) => p * 2 - 1,
  };

  function paint() {
    ticking = false;
    const vh = window.innerHeight;
    const still = motionReduced();

    sections.forEach((section) => {
      if (!visible.has(section)) return;
      const fn = map[section.dataset.accMode];
      section.querySelectorAll(".tad-accent__edge").forEach((edge) => {
        if (!fn || still) {
          edge.style.setProperty("--pp", "0");
          return;
        }
        const r = edge.getBoundingClientRect();
        const p = clamp((vh - r.top) / (vh + r.height), 0, 1);
        edge.style.setProperty("--pp", fn(p).toFixed(4));
      });
    });
  }

  function request() {
    if (!ticking) {
      ticking = true;
      requestAnimationFrame(paint);
    }
  }

  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          const on = entry.isIntersecting;
          entry.target.classList.toggle("is-offscreen", !on);
          if (on) visible.add(entry.target);
          else visible.delete(entry.target);
        });
        request();
      },
      { rootMargin: "240px 0px" }
    );
    sections.forEach((s) => io.observe(s));
  } else {
    sections.forEach((s) => visible.add(s));
  }

  window.addEventListener("scroll", request, { passive: true });
  window.addEventListener("resize", request);
  document.addEventListener("tad:motion", request);
  request();
}
