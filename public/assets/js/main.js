(function () {
  "use strict";

  /* Sticky header background on scroll */
  var header = document.querySelector(".site-header");
  function onScroll() {
    if (!header) return;
    if (window.scrollY > 12) header.classList.add("is-scrolled");
    else header.classList.remove("is-scrolled");
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* Mobile nav toggle */
  var toggler = document.querySelector(".navbar-toggler-custom");
  var panel = document.querySelector(".mobile-nav-panel");
  if (toggler && panel) {
    toggler.addEventListener("click", function () {
      panel.classList.toggle("is-open");
      toggler.setAttribute("aria-expanded", panel.classList.contains("is-open") ? "true" : "false");
      document.body.style.overflow = panel.classList.contains("is-open") ? "hidden" : "";
    });
    panel.querySelectorAll("a").forEach(function (a) {
      a.addEventListener("click", function () {
        panel.classList.remove("is-open");
        toggler.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
      });
    });
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && panel.classList.contains("is-open")) {
        panel.classList.remove("is-open");
        toggler.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
        toggler.focus();
      }
    });
  }

  /* Reveal-on-scroll */
  var revealEls = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window && revealEls.length) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 }
    );
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add("is-visible"); });
  }

  /* Animated counters */
  var counters = document.querySelectorAll("[data-count-to]");
  if (counters.length) {
    var animateCounter = function (el) {
      var to = parseInt(el.getAttribute("data-count-to"), 10) || 0;
      var duration = 1400;
      var start = null;
      function step(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.floor(eased * to).toLocaleString("id-ID");
        if (progress < 1) window.requestAnimationFrame(step);
        else el.textContent = to.toLocaleString("id-ID");
      }
      window.requestAnimationFrame(step);
    };
    if ("IntersectionObserver" in window) {
      var cio = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              animateCounter(entry.target);
              cio.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.5 }
      );
      counters.forEach(function (el) { cio.observe(el); });
    } else {
      counters.forEach(animateCounter);
    }
  }

  /* Marquee: duplicate track content once so the CSS loop is seamless */
  document.querySelectorAll(".marquee-track[data-duplicate]").forEach(function (track) {
    track.innerHTML += track.innerHTML;
  });

  /* Hero slide rotation (data-driven, falls back gracefully if only one slide) */
  var slides = document.querySelectorAll("[data-hero-slide]");
  if (slides.length > 1) {
    var current = 0;
    slides.forEach(function (s, i) { s.style.display = i === 0 ? "block" : "none"; });
    var dots = document.querySelectorAll("[data-hero-dot]");
    var heroImage = document.querySelector(".hero-photo-frame img");
    function show(i) {
      slides.forEach(function (s, idx) { s.style.display = idx === i ? "block" : "none"; });
      dots.forEach(function (d, idx) { d.classList.toggle("active", idx === i); });
      if (heroImage) {
        var imgSrc = slides[i].getAttribute("data-hero-image");
        if (imgSrc) heroImage.src = imgSrc;
      }
      current = i;
    }
    dots.forEach(function (d, i) {
      d.addEventListener("click", function () { show(i); restart(); });
    });
    var timer;
    function restart() {
      clearTimeout(timer);
      timer = setTimeout(function () { show((current + 1) % slides.length); restart(); }, 6000);
    }
    restart();
  }

  /* Login page: fetch a fresh math captcha */
  var captchaEl = document.getElementById("captchaQuestion");
  if (captchaEl && window.NFG_CAPTCHA_URL) {
    fetch(window.NFG_CAPTCHA_URL, { credentials: "same-origin" })
      .then(function (r) { return r.json(); })
      .then(function (data) { captchaEl.textContent = data.question || "?"; })
      .catch(function () { captchaEl.textContent = "?"; });
  }
})();
