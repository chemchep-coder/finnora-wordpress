/* Content stays visible without JavaScript. Motion never delays navigation or checkout. */
(() => {
  const preference = matchMedia('(prefers-reduced-motion: reduce)');
  if (!Element.prototype.animate) return;
  const active = new Set();
  const play = (element, frames, options) => {
    if (!element || preference.matches) return;
    const animation = element.animate(frames, options);
    active.add(animation);
    animation.finished.catch(() => {}).finally(() => active.delete(animation));
  };
  const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      observer.unobserve(entry.target);
      play(entry.target, [{opacity: .65, transform: 'translateY(14px)'}, {opacity: 1, transform: 'none'}], {duration: 420, easing: 'cubic-bezier(.2,.7,.3,1)'});
    });
  }, {threshold: .08}) : null;
  document.querySelectorAll('.fin-category-cards>a,.fin-home-products li.product,.product-card,.story-card,.fin-shopping-guide article,.studio-split').forEach(element => observer?.observe(element));
  play(document.querySelector('.hero h1'), [{opacity: .6, transform: 'translateY(10px)'}, {opacity: 1, transform: 'none'}], {duration: 500, easing: 'ease-out'});
  const added = () => {
    const bag = document.querySelector('.bag-link');
    play(bag, [{transform: 'scale(1)'}, {transform: 'scale(1.14)'}, {transform: 'scale(1)'}], {duration: 380, easing: 'ease-out'});
    // WooCommerce retains its accessible confirmation; this is visual feedback only.
    document.querySelector('.fin-bag-feedback')?.remove();
    const note = document.createElement('div');
    note.className = 'fin-bag-feedback'; note.setAttribute('aria-hidden', 'true');
    note.textContent = 'Added to your bag ✓'; document.body.append(note);
    play(note, [{opacity: 0, transform: 'translateY(8px)'}, {opacity: 1, transform: 'none'}], {duration: 220});
    setTimeout(() => note.remove(), 3000);
  };
  if (window.jQuery) window.jQuery(document.body).on('added_to_cart.finMotion', added);
  preference.addEventListener('change', () => { if (preference.matches) active.forEach(animation => animation.cancel()); });
})();
