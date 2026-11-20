/* Small progressive enhancements; all page content is rendered by WordPress. */
(() => {
  const menuButton = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.primary-navigation');
  const searchButton = document.querySelector('.search-toggle');
  const search = document.getElementById('header-search');
  const pinnedSearch = search?.hasAttribute('data-fin-pinned');
  if (pinnedSearch) searchButton?.classList.add('fin-search-pinned-toggle');
  const closeMenus = () => {
    document.querySelectorAll('.submenu-open').forEach(li => {
      li.classList.remove('submenu-open');
      li.querySelector('.submenu-toggle')?.setAttribute('aria-expanded', 'false');
    });
  };
  nav?.querySelectorAll('.menu-item-has-children').forEach(li => {
    if (li.querySelector(':scope > .submenu-toggle')) return;
    const button = document.createElement('button');
    button.type = 'button'; button.className = 'submenu-toggle'; button.textContent = '⌄';
    button.setAttribute('aria-label', 'Open submenu for ' + (li.querySelector('a')?.textContent || ''));
    button.setAttribute('aria-expanded', 'false');
    li.insertBefore(button, li.querySelector('.sub-menu'));
  });
  document.querySelectorAll('.submenu-toggle').forEach(button => button.addEventListener('click', () => {
    const li = button.parentElement;
    const opening = !li.classList.contains('submenu-open');
    closeMenus(); li.classList.toggle('submenu-open', opening);
    button.setAttribute('aria-expanded', String(opening));
  }));
  menuButton?.addEventListener('click', () => {
    const opening = !nav.classList.contains('is-open');
    nav.classList.toggle('is-open', opening);
    menuButton.setAttribute('aria-expanded', String(opening));
    if (opening && search && !pinnedSearch) { search.hidden = true; searchButton?.setAttribute('aria-expanded', 'false'); }
  });
  searchButton?.addEventListener('click', () => {
    const opening = search.hidden;
    search.hidden = !opening; searchButton.setAttribute('aria-expanded', String(opening));
    closeMenus();
    if (opening) { nav?.classList.remove('is-open'); menuButton?.setAttribute('aria-expanded', 'false'); search.querySelector('input')?.focus(); }
  });
  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    const searchOpen = search && !search.hidden && !pinnedSearch;
    const menuOpen = nav?.classList.contains('is-open');
    const submenuButton = document.querySelector('.submenu-open > .submenu-toggle');
    closeMenus(); nav?.classList.remove('is-open'); menuButton?.setAttribute('aria-expanded', 'false');
    if (search && !pinnedSearch) search.hidden = true;
    searchButton?.setAttribute('aria-expanded', 'false');
    if (searchOpen) searchButton?.focus(); else if (menuOpen) menuButton?.focus(); else submenuButton?.focus();
  });
  document.addEventListener('click', e => { if (!e.target.closest('.primary-navigation')) closeMenus(); });
  search?.addEventListener('focusin', closeMenus);
  const desktop = window.matchMedia('(min-width: 901px)');
  nav?.querySelectorAll('li.has-dropdown,li.menu-item-has-children').forEach(li => {
    const button = li.querySelector(':scope > .submenu-toggle');
    const open = () => { if (!desktop.matches) return; closeMenus(); li.classList.add('submenu-open'); button?.setAttribute('aria-expanded', 'true'); };
    li.addEventListener('pointerenter', open);
    li.addEventListener('pointerleave', () => { if (desktop.matches && !li.contains(document.activeElement)) { li.classList.remove('submenu-open'); button?.setAttribute('aria-expanded', 'false'); } });
    li.addEventListener('focusin', open);
    li.addEventListener('focusout', e => { if (desktop.matches && !li.contains(e.relatedTarget)) { li.classList.remove('submenu-open'); button?.setAttribute('aria-expanded', 'false'); } });
  });
  desktop.addEventListener('change', () => {
    nav?.classList.remove('is-open'); menuButton?.setAttribute('aria-expanded', 'false'); closeMenus();
  });
  const mobileFilters = window.matchMedia('(max-width: 650px)');
  const resizeFilters = () => document.querySelectorAll('.fin-shop-sidebar > details').forEach(details => { details.open = !mobileFilters.matches; });
  resizeFilters();
  mobileFilters.addEventListener('change', resizeFilters);
})();
// Open the precise support topic reached from the homepage help centre.
(() => {
  const revealSupport = () => {
    if (!/^#support-(delivery|care|returns)$/.test(location.hash)) return;
    const topic = document.getElementById(location.hash.slice(1));
    if (topic?.tagName === 'DETAILS') topic.open = true;
  };
  revealSupport();
  window.addEventListener('hashchange', revealSupport);
})();
// Reveal navigation on upward intent; hide only after leaving the initial header area.
(() => {
  const header = document.querySelector('.site-header');
  if (!header) return;
  let previous = Math.max(0, window.scrollY), intent = 0, scheduled = false;
  const reveal = () => header.classList.remove('fin-header-hidden');
  const update = () => {
    scheduled = false;
    const y = Math.max(0, window.scrollY), delta = y - previous;
    previous = y;
    if (Math.abs(delta) < 1) return;
    intent = Math.sign(delta) === Math.sign(intent) ? intent + delta : delta;
    const locked = header.querySelector('.primary-navigation.is-open') || (header.contains(document.activeElement) && document.activeElement?.matches(':focus-visible'));
    if (y < header.offsetHeight + 120 || locked) { reveal(); intent = 0; return; }
    if (intent > 18) { header.classList.add('fin-header-hidden'); intent = 0; }
    if (intent < -8) { reveal(); intent = 0; }
  };
  window.addEventListener('scroll', () => { if (!scheduled) { scheduled = true; requestAnimationFrame(update); } }, {passive:true});
  header.addEventListener('focusin', reveal);
  window.addEventListener('hashchange', reveal);
})();
