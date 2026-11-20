document.addEventListener('DOMContentLoaded', () => {
  const dialog = document.getElementById('fin-help-dialog');
  const trigger = document.querySelector('.fin-tree-help');
  if (!dialog || !trigger || !dialog.showModal) return;
  let previousOverflow;
  trigger.addEventListener('click', () => {
    if (dialog.open) return;
    previousOverflow = document.documentElement.style.overflow;
    dialog.showModal(); document.documentElement.style.overflow = 'hidden';
  });
  dialog.querySelector('.fin-help-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', event => {
    if (event.target !== dialog) return;
    const r = dialog.getBoundingClientRect();
    if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) dialog.close();
  });
  dialog.addEventListener('close', () => {
    document.documentElement.style.overflow = previousOverflow || '';
    trigger.focus({preventScroll:true});
  });
});
