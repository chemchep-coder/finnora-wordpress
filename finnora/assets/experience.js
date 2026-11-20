(() => {
  document.querySelectorAll('form.variations_form').forEach(form=>{const selects=[...form.querySelectorAll('.variations select')];const color=selects.find(s=>/colou?r/i.test(s.name))?.closest('tr');const size=selects.find(s=>/size/i.test(s.name))?.closest('tr');if(color&&size&&color.parentNode===size.parentNode)size.parentNode.insertBefore(color,size);});
  document.querySelectorAll('[data-fin-room]').forEach(room => {
    const items = [...room.querySelectorAll('[data-fin-item]')];
    if (!items.length) return;
    const list = room.querySelector('[data-fin-items]');
    const feature = room.querySelector('[data-fin-feature]');
    const link = room.querySelector('[data-fin-link]');
    let current = 0;
    function choose(index, scroll) {
      current = (index + items.length) % items.length;
      const item = items[current];
      items.forEach((entry, i) => i === current ? entry.setAttribute('aria-current', 'true') : entry.removeAttribute('aria-current'));
      feature.src = item.dataset.image;
      room.querySelector('[data-fin-name]').textContent = item.textContent.trim();
      room.querySelector('[data-fin-price]').textContent = item.dataset.price;
      link.href = item.href;
      if (scroll && room.classList.contains('fin-showroom--rail')) list.scrollTo({left: item.offsetLeft - (list.clientWidth - item.offsetWidth) / 2, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'});
    }
    items.forEach((item, i) => item.addEventListener('click', e => {
      if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      e.preventDefault(); choose(i, true);
    }));
    if (items.length > 1) room.querySelector('.fin-room-arrows').hidden = false;
    room.querySelectorAll('[data-fin-step]').forEach(button => button.addEventListener('click', () => choose(current + Number(button.dataset.finStep), true)));
    list.addEventListener('keydown', e => {
      if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return;
      e.preventDefault(); const i = items.indexOf(document.activeElement);
      choose((i < 0 ? current : i) + (e.key === 'ArrowRight' ? 1 : -1), true);
      items[current].focus({preventScroll:true});
    });
  });
  document.querySelectorAll('[data-fin-size-open]').forEach(button=>{const dialog=button.parentElement.querySelector('dialog');button.addEventListener('click',()=>dialog.showModal());dialog.querySelector('[data-fin-size-close]').addEventListener('click',()=>dialog.close());dialog.addEventListener('close',()=>button.focus());});
  document.querySelectorAll('[data-fin-try]').forEach(root => {
    const dialog = root.querySelector('dialog');
    if (typeof dialog.showModal !== 'function') return;
    const open = root.querySelector('[data-fin-open]');
    const camera = root.querySelector('[data-fin-camera]');
    const file = root.querySelector('[data-fin-file]');
    const status = root.querySelector('[data-fin-status]');
    const video = dialog.querySelector('video');
    const photo = root.querySelector('[data-fin-photo]');
    const overlay = root.querySelector('[data-fin-overlay]');

    const form = root.closest('.summary')?.querySelector('form.variations_form');
    const sizes = JSON.parse(root.dataset.finSizing || '{}');
    const optionBox = root.querySelector('[data-fin-try-options]');
    const sizeStatus = root.querySelector('[data-fin-size-status]');
    let baseWidth = 52, originalSvg;
    const nativeSelects = form ? [...form.querySelectorAll('.variations select')] : [];
    const sizeSelect = nativeSelects.find(s => /size/i.test(s.name));
    function updateProportions() {
      const size = sizeSelect?.selectedOptions[0]?.text.trim();
      const measure = sizes[size];
      if (!measure) { overlay.style.removeProperty('height'); overlay.style.setProperty('--fin-w',baseWidth+'%'); overlay.hidden=true; sizeStatus.textContent='Choose a size with reference measurements (S–3XL). XS sizing is not verified.'; return; }
      overlay.style.setProperty('--fin-w',baseWidth * measure.width / sizes.M.width + '%');
      // The SVG has transparent margins: maintain its M aspect ratio, then scale both axes from the same chart.
      overlay.style.height = 'auto';
      overlay.style.aspectRatio = String((measure.width / sizes.M.width) / (measure.length / sizes.M.length));
      overlay.style.objectFit='fill';
      if (!photo.hidden || !video.hidden) overlay.hidden=false;
      sizeStatus.textContent=size+' reference: '+measure.width+' in flat chest × '+measure.length+' in length. Calibrate against a known garment; not a fit guarantee.';
    }
    const colorSelect = nativeSelects.find(s=>/colou?r/i.test(s.name));
    let colorRevision=0;
    async function updateColor(){
      if(!colorSelect)return;
      const version=++colorRevision;
      try{
        if(!originalSvg)originalSvg=await fetch(overlay.getAttribute('src')).then(r=>r.text());
        const svg=new DOMParser().parseFromString(originalSvg,'image/svg+xml');
        const colorName=colorSelect.selectedOptions[0]?.text.toLowerCase()||'';
        const color=colorName.includes('green')?'#123d2d':colorName.includes('red')?'#d83238':colorName.includes('white')?'#f8f8f2':null;
        if(!color||version!==colorRevision)return;
        svg.querySelector('path')?.setAttribute('fill',color);
        overlay.src='data:image/svg+xml;charset=utf-8,'+encodeURIComponent(new XMLSerializer().serializeToString(svg));
      }catch(_){sizeStatus.textContent+=' Color preview unavailable; your selected option is still saved.';}
    }
    colorSelect?.addEventListener('change',updateColor);
    updateColor();
    nativeSelects.sort((a,b)=>Number(/size/i.test(a.name))-Number(/size/i.test(b.name))).forEach(native => {
      const label=document.createElement('label');label.textContent=/colou?r/i.test(native.name)?'Color':/size/i.test(native.name)?'Size':native.name.replace('attribute_','');
      const copy=document.createElement('select');copy.setAttribute('aria-label','Preview '+label.textContent);copy.innerHTML=native.innerHTML;copy.value=native.value;label.append(copy);optionBox.append(label);
      copy.addEventListener('change',()=>{native.value=copy.value;native.dispatchEvent(new Event('change',{bubbles:true}));if(window.jQuery)window.jQuery(native).trigger('change');});
      native.addEventListener('change',()=>{copy.innerHTML=native.innerHTML;copy.value=native.value;updateProportions();});
    });
    root.querySelector('[data-fin-range="w"]').addEventListener('input',e=>{baseWidth=Number(e.target.value);updateProportions();});
    updateProportions();
    let stream, url, revision = 0;
    function clear() {
      revision++;
      stream?.getTracks().forEach(t => t.stop()); stream = null;
      video.srcObject = null; video.hidden = true;
      if (url) URL.revokeObjectURL(url); url = null;
      photo.removeAttribute('src'); photo.hidden = true; overlay.hidden = true;
      camera.disabled = false;
    }
    open.hidden = false;
    open.addEventListener('click', () => dialog.showModal());
    root.querySelector('[data-fin-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => { clear(); file.value = ''; status.textContent = 'Start the camera or choose a photo to begin.'; open.focus(); });
    window.addEventListener('pagehide', clear);
    camera.addEventListener('click', async () => {
      clear(); file.value = ''; const request = revision;
      if (!navigator.mediaDevices?.getUserMedia) { status.textContent = 'Camera access requires HTTPS or localhost. You can choose a photo instead.'; return; }
      camera.disabled = true; status.textContent = 'Waiting for camera permission…';
      try {
        const next = await navigator.mediaDevices.getUserMedia({video:{facingMode:'user'},audio:false});
        if (!dialog.open || revision !== request) { next.getTracks().forEach(t => t.stop()); return; }
        stream = next; video.srcObject = stream; video.hidden = false; await video.play();
        if (revision === request) { updateProportions(); status.textContent = 'Camera on. Use the sliders to position the garment.'; }
      } catch (_) {
        if (revision === request) { clear(); status.textContent = 'Unable to start the camera. Check your permissions or choose a photo.'; }
      } finally { if (revision === request) camera.disabled = false; }
    });
    file.addEventListener('change', async () => {
      const selected = file.files?.[0]; if (!selected) return;
      clear(); file.value = ''; const request = revision;
      if (!['image/jpeg','image/png','image/webp'].includes(selected.type) || selected.size > 12 * 1024 * 1024) { status.textContent = 'Choose a JPG, PNG or WebP file up to 12 MB.'; return; }
      status.textContent = 'Opening your photo…'; url = URL.createObjectURL(selected); photo.src = url;
      try { await photo.decode(); if (dialog.open && request === revision) { photo.hidden = false; overlay.hidden = false; status.textContent = 'Photo ready. Use the sliders to position the garment.'; } }
      catch (_) { if (request === revision) { clear(); status.textContent = 'We could not open that photo. Please choose another file.'; } }
    });
    root.querySelectorAll('[data-fin-range]').forEach(range => range.addEventListener('input', () => range.dataset.finRange !== 'w' && overlay.style.setProperty('--fin-' + range.dataset.finRange, range.value + '%')));
  });
})();
