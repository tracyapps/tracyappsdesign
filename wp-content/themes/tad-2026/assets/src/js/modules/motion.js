/* Site-wide "pause motion" control (WCAG 2.2.2). The pre-paint state is set by a
   tiny inline script in header.php; this wires up the buttons. */
const KEY = "tad-motion";
const root = document.documentElement;

export function motionReduced() {
  return (
    root.dataset.motion === "paused" ||
    window.matchMedia("(prefers-reduced-motion: reduce)").matches
  );
}

export function initMotion() {
  const buttons = document.querySelectorAll("[data-motion-toggle]");

  const sync = () => {
    const paused = root.dataset.motion === "paused";
    buttons.forEach((btn) => {
      btn.setAttribute("aria-pressed", String(paused));
      const label = btn.querySelector("[data-motion-label]");
      if (label) label.textContent = paused ? btn.dataset.labelOff : btn.dataset.labelOn;
    });
    document.dispatchEvent(new CustomEvent("tad:motion", { detail: { paused } }));
  };

  buttons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const paused = root.dataset.motion !== "paused";
      if (paused) root.dataset.motion = "paused";
      else delete root.dataset.motion;
      try {
        if (paused) window.localStorage.setItem(KEY, "paused");
        else window.localStorage.removeItem(KEY);
      } catch (e) {
        /* storage blocked: the toggle still works for this page view */
      }
      sync();
    });
  });

  sync();
}
