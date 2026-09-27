/* Pointer-driven details: hero cluster parallax and cursor spotlight. (Project card tilt/glow: tilt.js) */
import { motionReduced } from "./motion.js";

export function initPointer() {
  const fine = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
  if (!fine) return;

  const root = document.documentElement;
  const nodes = Array.from(document.querySelectorAll("[data-parallax]"));
  const spotlight = document.querySelector(".spotlight");
  let targetX = 0;
  let targetY = 0;
  let currentX = 0;
  let currentY = 0;
  let raf = null;

  function frame() {
    currentX += (targetX - currentX) * 0.12;
    currentY += (targetY - currentY) * 0.12;
    nodes.forEach((node) => {
      const depth = parseFloat(node.getAttribute("data-parallax")) || 12;
      const base = parseFloat(node.getAttribute("data-base-rotate")) || 0;
      const dx = -currentX * depth;
      const dy = -currentY * depth;
      node.style.transform =
        `translate3d(${dx}px,${dy}px,0)` + (base ? ` rotate(${base}deg)` : "");
    });
    raf =
      Math.abs(targetX - currentX) > 0.001 || Math.abs(targetY - currentY) > 0.001
        ? requestAnimationFrame(frame)
        : null;
  }

  window.addEventListener(
    "pointermove",
    (e) => {
      if (motionReduced()) return;
      if (spotlight) {
        root.style.setProperty("--mx", `${e.clientX}px`);
        root.style.setProperty("--my", `${e.clientY}px`);
      }
      if (nodes.length) {
        targetX = e.clientX / window.innerWidth - 0.5;
        targetY = e.clientY / window.innerHeight - 0.5;
        if (!raf) raf = requestAnimationFrame(frame);
      }
    },
    { passive: true }
  );
}
