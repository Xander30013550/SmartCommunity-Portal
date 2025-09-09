(() => {
  const slider = document.querySelector(".slider");
  if (!slider) return; // nothing to do when no items

  const track = document.getElementById("slider-track");
  const dotsEl = document.getElementById("slider-dots");
  const sr = document.getElementById("sr-status");
  const autoplay = slider.dataset.autoplay === "true";
  const interval = Number(slider.dataset.interval || 2000);

  const slides = [...track.children];

  // build dots
  slides.forEach((_, i) => {
    const b = document.createElement("button");
    b.type = "button";
    b.setAttribute("aria-label", `Go to ${i + 1}`);
    b.addEventListener("click", () => goTo(i, true));
    dotsEl.appendChild(b);
  });

  let i = 0,
    t = null;

  function ui() {
    track.style.transform = `translateX(-${i * 100}%)`;
    dotsEl
      .querySelectorAll("button")
      .forEach((b, k) =>
        b.setAttribute("aria-current", k === i ? "true" : "false")
      );
    sr.textContent = `Showing announcement ${i + 1} of ${slides.length}`;
  }

  function goTo(n, user = false) {
    i = (n + slides.length) % slides.length;
    ui();
    if (user) restart();
  }

  function next() {
    goTo(i + 1);
  }
  function prev() {
    goTo(i - 1);
  }

  document.querySelector(".prev").addEventListener("click", () => prev());
  document.querySelector(".next").addEventListener("click", () => next());

  // keyboard + focus pausing
  slider.tabIndex = 0;
  slider.addEventListener("keydown", (e) => {
    if (e.key === "ArrowRight") {
      e.preventDefault();
      next();
    }
    if (e.key === "ArrowLeft") {
      e.preventDefault();
      prev();
    }
  });

  function start() {
    if (!autoplay || matchMedia("(prefers-reduced-motion: reduce)").matches)
      return;
    stop();
    t = setInterval(next, interval);
  }
  function stop() {
    if (t) clearInterval(t);
    t = null;
  }
  function restart() {
    stop();
    start();
  }

  slider.addEventListener("mouseenter", stop);
  slider.addEventListener("mouseleave", start);
  slider.addEventListener("focusin", stop);
  slider.addEventListener("focusout", start);

  ui();
  start();
})();
