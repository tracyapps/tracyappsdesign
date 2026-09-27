import { initMotion } from "./modules/motion.js";
import { initNav } from "./modules/nav.js";
import { initReveal } from "./modules/reveal.js";
import { initPointer } from "./modules/pointer.js";
import { initMarquee } from "./modules/marquee.js";
import { initAccent } from "./modules/accent.js";
import { initForm } from "./modules/form.js";
import { initMasonry } from "./modules/masonry.js";
import { initTilt } from "./modules/tilt.js";

function ready(fn) {
  if (document.readyState !== "loading") fn();
  else document.addEventListener("DOMContentLoaded", fn);
}

ready(() => {
  initMotion();
  initNav();
  initReveal();
  initPointer();
  initMarquee();
  initAccent();
  initForm();
  initMasonry();
  initTilt();

  document.querySelectorAll("[data-year]").forEach((el) => {
    el.textContent = String(new Date().getFullYear());
  });
});
