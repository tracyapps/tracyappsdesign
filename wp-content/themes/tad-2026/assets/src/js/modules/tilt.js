/* Pointer tilt for project cards. Sets --tx / --ty (-0.5 … 0.5) on the card; CSS tilts
   the card a little and the image frame more (a separate "cut-out" layer), with the
   picture sliding the opposite way inside. Mouse/pen only; off for reduced motion and
   for the site's pause-motion switch. */
import { motionReduced } from "./motion.js";

export function initTilt() {
  if (!window.matchMedia("(hover: hover) and (pointer: fine)").matches) return;

  const cards = Array.from(document.querySelectorAll("[data-tilt]"));
  if (!cards.length) return;

  const reset = (card) => {
    card.classList.remove("is-tilting");
    card.style.removeProperty("--tx");
    card.style.removeProperty("--ty");
  };

  cards.forEach((card) => {
    let frame = 0;
    let x = 0;
    let y = 0;

    const paint = () => {
      frame = 0;
      card.style.setProperty("--tx", x.toFixed(3));
      card.style.setProperty("--ty", y.toFixed(3));
    };

    card.addEventListener(
      "pointermove",
      (e) => {
        if (e.pointerType === "touch") return;
        if (motionReduced()) {
          reset(card);
          return;
        }
        const r = card.getBoundingClientRect();
        x = Math.max(-0.5, Math.min(0.5, (e.clientX - r.left) / r.width - 0.5));
        y = Math.max(-0.5, Math.min(0.5, (e.clientY - r.top) / r.height - 0.5));
        card.style.setProperty("--cell-x", `${e.clientX - r.left}px`);
        card.style.setProperty("--cell-y", `${e.clientY - r.top}px`);
        card.classList.add("is-tilting");
        if (!frame) frame = requestAnimationFrame(paint);
      },
      { passive: true }
    );

    card.addEventListener("pointerleave", () => {
      if (frame) cancelAnimationFrame(frame);
      frame = 0;
      reset(card);
    });
  });

  document.addEventListener("tad:motion", () => {
    if (motionReduced()) cards.forEach(reset);
  });
}
