(() => {
  const input = document.querySelector('[data-fin-home-name]');
  const output = document.querySelector('[data-fin-home-name-output]');
  const preview = document.querySelector('[data-fin-home-preview]');
  if (!input || !output) return;
  input.addEventListener('input', () => {
    const name = input.value.trim() || 'YOUR NAME';
    output.textContent = name;
    preview?.setAttribute('aria-label', 'Illustrative mug preview personalized with ' + name);
  });
})();
