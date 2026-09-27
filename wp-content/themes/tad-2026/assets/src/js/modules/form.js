/* Contact form: inline validation + fetch submit. Works without JS too
   (the form posts to admin-post.php and redirects back with a status). */
export function initForm() {
  const form = document.querySelector("[data-contact-form]");
  if (!form) return;

  const wrap = form.closest("[data-contact]") || form.parentElement;
  const success = wrap.querySelector("[data-form-success]");
  const errorBox = wrap.querySelector("[data-form-error]");
  const live = form.querySelector("[data-form-live]");
  const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const fieldOf = (input) => input.closest(".field");

  function setError(input, message) {
    const field = fieldOf(input);
    const errorEl = field ? field.querySelector(".field__error") : null;
    if (field) field.dataset.invalid = message ? "true" : "false";
    if (errorEl) errorEl.textContent = message || "";
    input.setAttribute("aria-invalid", message ? "true" : "false");
  }

  function validate(input) {
    const value = (input.value || "").trim();
    const labelText = input.getAttribute("data-label") || "this field";
    if (input.hasAttribute("data-required") && !value) {
      setError(input, `Please enter ${labelText}.`);
      return false;
    }
    if (input.type === "email" && value && !emailRe.test(value)) {
      setError(input, "That email doesn't look right yet.");
      return false;
    }
    setError(input, "");
    return true;
  }

  const inputs = () => Array.from(form.querySelectorAll("input:not([type=hidden]), textarea, select"));

  inputs().forEach((input) => {
    input.addEventListener("blur", () => validate(input));
    input.addEventListener("input", () => {
      if (fieldOf(input) && fieldOf(input).dataset.invalid === "true") validate(input);
    });
  });

  function showError(message) {
    if (errorBox) {
      errorBox.textContent = message;
      errorBox.hidden = false;
    }
    if (live) live.textContent = message;
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (errorBox) errorBox.hidden = true;

    let firstInvalid = null;
    inputs().forEach((input) => {
      if (!validate(input) && !firstInvalid) firstInvalid = input;
    });
    if (firstInvalid) {
      if (live) live.textContent = "Please fix the highlighted fields.";
      firstInvalid.focus();
      return;
    }

    const submit = form.querySelector("[type=submit]");
    if (submit) submit.disabled = true;

    try {
      const body = new FormData(form);
      body.set("ajax", "1");
      const res = await fetch(form.action, {
        method: "POST",
        body,
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      });
      const data = await res.json();
      if (data && data.success) {
        form.hidden = true;
        if (success) {
          success.hidden = false;
          success.setAttribute("tabindex", "-1");
          success.focus();
        }
      } else {
        const message =
          (data && data.data && data.data.message) || "Something went wrong. Please try again.";
        showError(message);
        if (submit) submit.disabled = false;
      }
    } catch (err) {
      // Network or server hiccup: fall back to a normal form post.
      form.submit();
    }
  });
}
