(function () {
  "use strict";
  var sidebar = document.getElementById("appSidebar");
  var overlay = document.getElementById("sidebarOverlay");
  var openBtn = document.getElementById("sidebarOpen");
  var closeBtn = document.getElementById("sidebarClose");

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add("is-open");
    if (overlay) overlay.classList.add("is-visible");
    document.body.style.overflow = "hidden";
  }
  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove("is-open");
    if (overlay) overlay.classList.remove("is-visible");
    document.body.style.overflow = "";
  }
  if (openBtn) openBtn.addEventListener("click", openSidebar);
  if (closeBtn) closeBtn.addEventListener("click", closeSidebar);
  if (overlay) overlay.addEventListener("click", closeSidebar);
  document.querySelectorAll("[data-mobile-sidebar-toggle]").forEach(function (button) {
    button.addEventListener("click", openSidebar);
  });

  /* Close sidebar automatically when a nav link is tapped on mobile */
  if (sidebar) {
    sidebar.querySelectorAll(".nav-link").forEach(function (a) {
      a.addEventListener("click", function () {
        if (window.innerWidth < 992) closeSidebar();
      });
    });
  }
  /* Mood tracker emoji picker (mood/index.php, wellness/index.php).
     Both pages call onclick="selectMoodEmoji(this, val)" but only ever
     declared the function inside a DOMContentLoaded closure, so it was
     never actually reachable from an inline onclick. Defining it here
     on window fixes the numeric mood_scale value and the "selected" ring. */
  window.selectMoodEmoji = function (btn, val) {
    document.querySelectorAll(".mood-emoji-btn").forEach(function (b) { b.classList.remove("selected"); });
    btn.classList.add("selected");
    var scaleInput = document.getElementById("mood_scale_input");
    var emojiInput = document.getElementById("emoji_input");
    if (scaleInput) scaleInput.value = val;
    if (emojiInput) emojiInput.value = btn.textContent.trim();
  };

  /* Global delete confirmation used by inline buttons in admin views.
     Example usage: onclick="confirmDelete('delUser123')" */
  window.confirmDelete = function (formId) {
    var form = document.getElementById(formId);
    if (!form) return;
    if (window.Swal && typeof Swal.fire === 'function') {
      Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
      }).then(function (res) {
        if (res.isConfirmed) form.submit();
      });
    } else {
      if (confirm('Yakin ingin menghapus?\nTindakan ini tidak dapat dibatalkan.')) form.submit();
    }
  };

  /* Small helper to show skeleton loaders for a selector. Usage:
     call window.showSkeleton('#userTable', 3) to replace table with 3 skeleton cards. */
  window.showSkeleton = function (selector, count) {
    var el = document.querySelector(selector);
    if (!el) return;
    var container = document.createElement('div');
    for (var i=0;i<(count||3);i++){
      var card = document.createElement('div'); card.className = 'skeleton-card';
      var av = document.createElement('div'); av.className = 'skeleton skeleton-avatar';
      var body = document.createElement('div'); body.style.flex='1';
      var l1 = document.createElement('div'); l1.className='skeleton skeleton-line'; l1.style.width='60%';
      var l2 = document.createElement('div'); l2.className='skeleton skeleton-line'; l2.style.width='40%';
      body.appendChild(l1); body.appendChild(l2);
      card.appendChild(av); card.appendChild(body);
      container.appendChild(card);
    }
    el.parentNode.insertBefore(container, el);
    el.style.display='none';
    return function restore(){ container.remove(); el.style.display=''; };
  };
})();
