export function initNav() {
  const toggle = document.querySelector("[data-nav-toggle]");
  const panel = document.querySelector("[data-nav-panel]");
  if (!toggle || !panel) return;

  const label = toggle.querySelector("[data-nav-label]");
  const setOpen = (open) => {
    toggle.setAttribute("aria-expanded", String(open));
    panel.toggleAttribute("data-open", open);
    if (label) label.textContent = open ? toggle.dataset.labelClose : toggle.dataset.labelOpen;
  };
  setOpen(false);

  toggle.addEventListener("click", () => {
    setOpen(toggle.getAttribute("aria-expanded") !== "true");
  });

  panel.addEventListener("click", (e) => {
    if (e.target.closest("a")) setOpen(false);
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && toggle.getAttribute("aria-expanded") === "true") {
      setOpen(false);
      toggle.focus();
    }
  });
}
