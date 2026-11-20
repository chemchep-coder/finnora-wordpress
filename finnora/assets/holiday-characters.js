/* One visible companion at a time; keyboard and touch friendly. */
(() => {
  const cards=[...document.querySelectorAll('.fin-design-card,.woocommerce ul.products li.product')].filter(c=>c.querySelector('.fin-reindeer-mini'));
  let active=null;
  const activate=card=>{if(active===card)return;active?.classList.remove('fin-character-active');active=card;card?.classList.add('fin-character-active');};
  cards.forEach(card=>{
    card.addEventListener('pointerenter',e=>{if(e.pointerType!=='touch')activate(card);});
    card.addEventListener('pointerleave',()=>{if(!card.contains(document.activeElement))activate(null);});
    card.addEventListener('focusin',()=>activate(card));
    card.addEventListener('focusout',e=>{if(!card.contains(e.relatedTarget))activate(null);});
    card.addEventListener('pointerdown',()=>activate(card));
  });
  document.addEventListener('pointerdown',e=>{if(active&&!active.contains(e.target))activate(null);});
  document.querySelector('[data-fin-builder]')?.addEventListener('fin:photo-ready',()=>{
    const santa=document.querySelector('.fin-santa-photo');if(!santa)return;
    santa.classList.remove('is-photo-ready');requestAnimationFrame(()=>requestAnimationFrame(()=>santa.classList.add('is-photo-ready')));
  });
})();
