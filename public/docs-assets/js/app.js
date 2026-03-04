$(function () {
  const header = document.getElementById('docs-header');
  const fixedThreshold = 8;

  function updateHeaderState() {
    if (!header) return;

    const shouldFix = window.scrollY > fixedThreshold;
    header.classList.toggle('is-fixed', shouldFix);
    document.body.classList.toggle('docs-header-fixed', shouldFix);

    if (shouldFix) {
      document.body.style.setProperty('--docs-header-height', `${header.offsetHeight}px`);
    } else {
      document.body.style.removeProperty('--docs-header-height');
    }
  }

  updateHeaderState();
  window.addEventListener('scroll', updateHeaderState, { passive: true });
  window.addEventListener('resize', updateHeaderState);

  const path = window.location.pathname;
  $('.docs-submenu-links a').each(function () {
    const href = this.getAttribute('href');
    if (!href) return;
    try {
      const linkPath = new URL(href, window.location.origin).pathname;
      if (linkPath === path) {
        this.classList.add('active');
      }
    } catch (e) {
      // noop
    }
  });

  $('.list-group-item[data-path]').each(function () {
    const target = $(this).data('path');
    if (path.endsWith(target)) {
      $(this).addClass('active');
    }
  });

  $('#ano-actual').text(new Date().getFullYear());
});
