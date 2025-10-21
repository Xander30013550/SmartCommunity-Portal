//  This self-invoking function sets up a fully accessible, keyboard-navigable, and optionally autoplaying slider component. 
//  It initializes slide navigation buttons with proper ARIA labels, manages slide transitions including next/previous controls
//  and keyboard input, updates visual and screen reader indicators, and controls autoplay behavior with pause/resume triggered 
// by user interaction like mouse hover or keyboard focus.
(() => {
  const slider = document.querySelector(".slider");
  if (!slider) return; // nothing to do when no items

  const track = document.getElementById("slider-track");
  const dotsEl = document.getElementById("slider-dots");
  const sr = document.getElementById("sr-status");
  const autoplay = slider.dataset.autoplay === "true";
  const interval = Number(slider.dataset.interval || 2000);

  const slides = [...track.children];

  //  This creates a navigation button for each slide, setting an accessible label 
  //  and attaching a click event that triggers a `goTo` function to move to the 
  //  corresponding slide. Each button is then added to the `dotsEl` container, likely 
  //  forming a dot-based slide navigation interface.
  slides.forEach((_, i) => {
    const b = document.createElement("button");
    b.type = "button";
    b.setAttribute("aria-label", `Go to ${i + 1}`);
    b.addEventListener("click", () => goTo(i, true));
    dotsEl.appendChild(b);
  });

  //  This initializes two variables: `i` to track the current slide index starting at 0, 
  //  and `t` to hold the timer ID for autoplay, initially set to `null`.
  let i = 0, t = null;

  //  This function updates the slide display by shifting the track to show the current slide, 
  //  highlights the active navigation dot using the `aria-current` attribute, and updates screen
  //  reader text to indicate the current slide number out of the total.
  function ui() {
    track.style.transform = `translateX(-${i * 100}%)`;
    dotsEl
      .querySelectorAll("button")
      .forEach((b, k) =>
        b.setAttribute("aria-current", k === i ? "true" : "false")
      );
    sr.textContent = `Showing announcement ${i + 1} of ${slides.length}`;
  }

  //  This function navigates to the slide at index `n`, wrapping around if the index is out of bounds, 
  //  updates the UI accordingly, and restarts any slide-related timing or autoplay if the navigation was 
  //  triggered by the user.
  function goTo(n, user = false) {
    i = (n + slides.length) % slides.length;
    ui();
    if (user) restart();
  }

  //  This function advances to the next slide by calling `goTo` with the current index incremented 
  //  by one, relying on `goTo` to handle wrapping and UI updates.
  function next() {
    goTo(i + 1);
  }

  //  This function moves to the previous slide by calling `goTo` with the current index decremented by 
  // one, allowing wrap-around and triggering the necessary UI updates.
  function prev() {
    goTo(i - 1);
  }

  //  This line adds a click event listener to the element with the class `"prev"`, so when it’s clicked, the
  //  `prev()` function is called to move to the previous slide.
  document.querySelector(".prev").addEventListener("click", () => prev());

  //  This code attaches a click event listener to the element with the class `"next"`, triggering the `next()`
  //  function to advance to the next slide when clicked.
  document.querySelector(".next").addEventListener("click", () => next());

  // keyboard + focus pausing
  slider.tabIndex = 0;

  //  This code listens for keyboard arrow key presses on the slider element, and when the right or left arrow 
  //  keys are pressed, it prevents the default browser action and moves the slider to the next or previous slide
  //  respectively.
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

  //  This function initiates an automatic slide advance using `setInterval` only if autoplay
  //  is enabled and the user hasn’t requested reduced motion in their system preferences, 
  //  first clearing any existing interval to avoid duplicates.
  function start() {
    if (!autoplay || matchMedia("(prefers-reduced-motion: reduce)").matches)
      return;
    stop();
    t = setInterval(next, interval);
  }

  //  This function clears the ongoing slide autoplay interval if it exists and resets the 
  //  timer variable to `null`, effectively pausing the automatic slide advancement.
  function stop() {
    if (t) clearInterval(t);
    t = null;
  }

  //  This function stops any current slide autoplay and immediately starts it again, effectively
  //  resetting the autoplay timer.
  function restart() {
    stop();
    start();
  }

  //  This sets up an event listener on the slider to pause the autoplay by 
  //  calling `stop` whenever the mouse pointer enters the slider area.
  slider.addEventListener("mouseenter", stop);

  //  This adds an event listener to the slider that resumes autoplay by calling 
  //  `start` when the mouse pointer leaves the slider area.
  slider.addEventListener("mouseleave", start);

  //  This adds an event listener to pause the autoplay by calling `stop` whenever 
  // the slider gains keyboard focus, helping improve accessibility and user control.
  slider.addEventListener("focusin", stop);

  //  This resumes autoplay by calling `start` when the slider loses keyboard focus, 
  //  allowing the slide show to continue once the user moves away.
  slider.addEventListener("focusout", start);

  //  This runs the function that updates the slide display, sets the active navigation dot, 
  //  and updates screen reader text—essentially refreshing the slider’s visual and accessibility 
  //  state.
  ui();

  //  This begins the automatic slide advancement if autoplay is enabled and the user hasn’t requested 
  // reduced motion, kicking off the slideshow timer.
  start();
})();