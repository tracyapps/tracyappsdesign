/* Seamless marquee: clone the track once (clone is hidden from assistive tech). */
export function initMarquee() {
  document.querySelectorAll(".marquee").forEach((marquee) => {
    const track = marquee.querySelector(".marquee__track");
    if (!track || track.dataset.cloned === "true") return;
    const clone = track.cloneNode(true);
    clone.setAttribute("aria-hidden", "true");
    clone.dataset.cloned = "true";
    clone.querySelectorAll("a, button, [tabindex]").forEach((el) => el.setAttribute("tabindex", "-1"));
    track.dataset.cloned = "true";
    marquee.appendChild(clone);
  });
}
