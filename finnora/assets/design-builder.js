/* Local-first drafts and illustrative canvas previews. No remote AI/photo API. */
(() => {
  const filter = document.querySelector('.fin-design-filters');
  if (filter && matchMedia('(max-width:650px)').matches) filter.open = false;
  const root = document.querySelector('[data-fin-builder]');
  if (!root) return;
  const data = JSON.parse(root.querySelector('.fin-builder-settings').textContent);
  const $ = s => root.querySelector(s);
  const form = $('.fin-builder-form'), canvas = $('[data-fin-canvas]'), ctx = canvas.getContext('2d');
  const product = $('[data-fin-product]'), variant = $('[data-fin-variant]'), text = $('[data-fin-message]'), quantity = $('[data-fin-quantity]');
  const photoInput = $('[data-fin-photo]'), look = $('[data-fin-look]'), layout = $('[data-fin-layout]');
  const confirm = $('[data-fin-confirm]'), submit = $('[data-fin-submit]'), status = $('[data-fin-builder-status]');
  let step = 0, view = 'art', photo = null, photoFile = null, photoURL = '', ready = false, saving, prepared = false, drawing = 0;
  let draftId = new URLSearchParams(location.search).get('draft');
  if (!draftId || !/^[a-z0-9_-]{1,80}$/.test(draftId)) { try { draftId = localStorage.getItem('fin-current-' + data.design.id); } catch {} }
  draftId ||= 'draft-' + (crypto.randomUUID?.() || Date.now().toString(36));
  $('[data-fin-draft-field]').value = draftId;
  const imageCache = new Map();
  const image = src => {
    if (!imageCache.has(src)) imageCache.set(src, new Promise((resolve, reject) => { const im = new Image(); im.crossOrigin = 'anonymous'; im.onload = () => resolve(im); im.onerror = reject; im.src = src; }));
    return imageCache.get(src);
  };
  const db = new Promise((resolve, reject) => {
    const request = indexedDB.open('finnora-design-drafts', 1);
    request.onupgradeneeded = () => request.result.createObjectStore('drafts');
    request.onsuccess = () => resolve(request.result); request.onerror = reject;
  });
  const storage = async (mode, value) => { const database = await db; return new Promise((resolve, reject) => { const tx = database.transaction('drafts', mode === 'get' ? 'readonly' : 'readwrite'); const store = tx.objectStore('drafts'); const req = mode === 'get' ? store.get(draftId) : mode === 'delete' ? store.delete(draftId) : store.put(value, draftId); req.onsuccess = () => resolve(req.result); req.onerror = reject; }); };
  const selectedProduct = () => data.products.find(p => p.id === Number(product?.value));
  const selectedVariant = () => selectedProduct()?.options.find(v => v.id === Number(variant?.value));
  const money = amount => new Intl.NumberFormat('en-US', {style:'currency',currency:data.currency}).format(amount);
  const adjust = name => Number($('[data-fin-adjust="' + name + '"]')?.value || (name === 'zoom' ? 1 : 0));
  const artSlug = () => layout?.value || data.design.art;
  function variants(value) {
    const p = selectedProduct(); if (!p || !variant) return;
    variant.replaceChildren();
    (p.options.length ? p.options : [{id:0,label:'Standard',priceText:p.priceText}]).forEach(v => { const op = document.createElement('option'); op.value = v.id; op.textContent = v.label; variant.append(op); });
    if ([...variant.options].some(o => o.value === String(value))) variant.value = String(value);
    attributeFields();price();
  }
  function attributeFields() {
    const container=$('[data-fin-attributes]'),p=selectedProduct(),v=selectedVariant();if(!container||!p)return;
    container.replaceChildren();
    const keys=[...new Set(p.options.flatMap(o=>Object.keys(o.attributes||{})))];
    keys.forEach((key,index)=>{
      const label=document.createElement('label'),control=document.createElement('select');
      const title=key.replace(/^attribute_(pa_)?/,'').replace(/[-_]/g,' ').replace(/\b\w/g,c=>c.toUpperCase());
      label.textContent=title; control.setAttribute('aria-label',title);
      const previous=keys.slice(0,index);
      const candidates=p.options.filter(o=>previous.every(k=>o.attributes[k]===v?.attributes[k]));
      [...new Set(candidates.map(o=>o.attributes[key]))].forEach(value=>{const option=document.createElement('option');option.value=value;option.textContent=value.replace(/[-_]/g,' ').replace(/\b\w/g,c=>c.toUpperCase());control.append(option);});
      control.value=v?.attributes[key]||control.options[0]?.value||'';
      control.addEventListener('change',()=>{
        const compatible=p.options.filter(o=>previous.every(k=>o.attributes[k]===v?.attributes[k])&&o.attributes[key]===control.value);
        const next=compatible.find(o=>keys.slice(index+1).every(k=>o.attributes[k]===v?.attributes[k]))||compatible[0];
        if(next){variant.value=next.id;attributeFields();price();invalidate();preview();saveDraft();}
      });
      label.append(control);container.append(label);
    });
    if(!keys.length){const label=document.createElement('label');label.textContent='Format';const output=document.createElement('output');output.className='fin-standard-format';output.textContent='Standard';label.append(output);container.append(label);}
  }
  function price() { const p = selectedProduct(), v = selectedVariant(); if ($('[data-fin-price]')) $('[data-fin-price]').textContent = p ? money(v?.price ?? p.price) : 'No compatible product available'; }
  function invalidate() { prepared = false; if (confirm) confirm.checked = false; if (submit) submit.disabled = true; }
  function saveDraft() {
    if (!ready) return;
    clearTimeout(saving); saving = setTimeout(() => {
      const fields = Object.fromEntries([...form.querySelectorAll('input[name],select[name]')].filter(e => !['file','hidden','checkbox'].includes(e.type)).map(e => [e.name,e.value]));
      storage('put',{fields,photoFile,design:data.design.id,updated:Date.now()}).then(() => { try { localStorage.setItem('fin-current-' + data.design.id,draftId); } catch {} }).catch(() => { status.textContent = 'Browser draft storage is unavailable. Keep this tab open until you finish.'; });
    },250);
  }
  function paintPhoto(c, rectangle) {
    if (!photo) return;
    const [x,y,w,h] = rectangle, scale = Math.max(w/photo.width,h/photo.height)*adjust('zoom');
    const dw = photo.width*scale, dh = photo.height*scale;
    const dx = x+(w-dw)/2+adjust('x')/100*Math.max(0,dw-w)/2;
    const dy = y+(h-dh)/2+adjust('y')/100*Math.max(0,dh-h)/2;
    c.save(); c.beginPath();
    if(['pet-monarch','festive-pet-club'].includes(artSlug()))c.ellipse(x+w/2,y+h/2,w/2,h/2,0,0,Math.PI*2);else c.rect(x,y,w,h);
    c.clip(); c.filter = look?.value === 'monochrome' ? 'grayscale(1)' : 'none'; c.drawImage(photo,dx,dy,dw,dh); c.restore();
  }
  function schematic(c, p, art) {
    c.fillStyle='#f4f6f4'; c.fillRect(0,0,800,800); c.strokeStyle='#c6d2ca'; c.lineWidth=3;
    const slug=p?.slug||'art-poster'; const green = Object.values(selectedVariant()?.attributes||{}).some(v=>/forest|green/i.test(v));
    c.fillStyle=green?'#123d2d':'#ffffff';
    let area=[235,235,330,330];
    c.beginPath();
    if(['colour-tee','studio-sweat'].includes(slug)) { c.moveTo(260,150);c.lineTo(170,185);c.lineTo(95,310);c.lineTo(195,365);c.lineTo(225,315);c.lineTo(220,690);c.lineTo(580,690);c.lineTo(575,315);c.lineTo(605,365);c.lineTo(705,310);c.lineTo(630,185);c.lineTo(540,150);c.quadraticCurveTo(400,235,260,150); area=[245,260,310,310]; }
    else if(slug==='paint-mug') { c.roundRect(185,240,380,370,24); c.fill();c.stroke();c.beginPath();c.roundRect(560,300,110,210,45);c.stroke();area=[215,285,320,280]; }
    else if(slug==='canvas-tote') { c.roundRect(175,255,450,410,12);c.fill();c.stroke();c.beginPath();c.roundRect(285,105,230,260,80);c.stroke();area=[220,300,360,320]; }
    else if(slug==='phone-case') { c.roundRect(240,80,320,650,42);area=[260,235,280,400]; }
    else if(slug==='sticker-sheet') { c.rect(130,160,540,500);area=[175,200,450,410]; }
    else { c.rect(160,130,480,570);area=[195,175,410,475]; }
    c.fill();c.stroke(); c.drawImage(art,...area);
    c.fillStyle='#53675f';c.font='18px Arial';c.textAlign='center';c.fillText('SCHEMATIC MOCKUP · NOT A FIT OR PRINT PROOF',400,770);
  }
  async function render(forceView) {
    const version = ++drawing;
    const art = document.createElement('canvas');art.width=800;art.height=800;const c=art.getContext('2d');
    c.clearRect(0,0,800,800);
    const src = layout ? data.assetBase+artSlug()+'.svg' : data.design.image;
    const im = await image(src); if (version !== drawing) return;
    c.drawImage(im,0,0,800,800);
    if (data.design.photo && photo) {
      const windows={'photo-story':[142,122,516,451],'together-at-christmas':[138,153,524,434],'pet-monarch':[235,225,330,325],'festive-pet-club':[245,185,310,330]};
      paintPhoto(c, windows[artSlug()] || [200,180,400,360]);
    }
    if (text?.value.trim()) { const message=text.value.trim();c.fillStyle='#123d2d';c.font='600 26px Arial';c.textAlign='center';const words=message.split(/\s+/),lines=[];let line='';words.forEach(w=>{if(c.measureText((line+' '+w).trim()).width>650&&line){lines.push(line);line=w;}else line=(line+' '+w).trim();});if(line)lines.push(line);lines.slice(0,3).forEach((l,i)=>c.fillText(l,400,735+i*27,680)); }
    ctx.clearRect(0,0,800,800);
    if ((forceView||view)==='product') schematic(ctx,selectedProduct(),art);else {ctx.fillStyle='#fff';ctx.fillRect(0,0,800,800);ctx.drawImage(art,0,0);}
    canvas.setAttribute('aria-label',(forceView||view)==='product'?'Illustrative schematic product mockup':'Your artwork with the current photo and text');
  }
  function preview() { render().catch(() => { status.textContent='The preview image could not be loaded. Please reload before adding to your bag.'; submit.disabled=true; }); }
  async function loadPhoto(file) {
    if (!file || !['image/jpeg','image/png','image/webp'].includes(file.type) || file.size>8*1024*1024) throw Error('Choose a JPG, PNG or WebP image up to 8 MB.');
    const url=URL.createObjectURL(file), im=await image(url);
    if(im.width*im.height>24000000){URL.revokeObjectURL(url);throw Error('Please resize this photo below 24 megapixels.');}
    if(photoURL)URL.revokeObjectURL(photoURL);photoURL=url;photo=im;photoFile=file;
    $('[data-fin-photo-adjustments]').hidden=false;invalidate();preview();saveDraft();
  }
  photoInput?.addEventListener('change',async()=>{try{await loadPhoto(photoInput.files[0]);status.textContent='Photo ready. Adjust the crop and check your preview.';root.dispatchEvent(new CustomEvent('fin:photo-ready'));}catch(e){photoInput.value='';photo=null;photoFile=null;status.textContent=e.message;invalidate();preview();}});
  $('[data-fin-photo-next]')?.addEventListener('click',()=>{if(!photo){status.textContent='Choose a photo first.';photoInput.focus();return;}const panel=$('.fin-builder-photo');if(panel.classList.contains('is-collapsed')){panel.classList.remove('is-collapsed');$('[data-fin-photo-next]').textContent='Continue with this photo';return;}panel.classList.add('is-collapsed');$('[data-fin-photo-next]').textContent='Edit photo & crop';$('[data-fin-builder-steps]').hidden=false;$('[data-fin-step="0"] h2').setAttribute('tabindex','-1');$('[data-fin-step="0"] h2').focus();});
  function summary() {
    const p=selectedProduct(),v=selectedVariant(),qty=Number(quantity.value), values=[['Design',layout?layout.selectedOptions[0].textContent:data.design.name],['Product',p?.name||''],['Size / color / format',v?.label||'Standard'],['Your text',text?.value.trim()||'No extra text'],['Photo',photo?(look?.selectedOptions[0].textContent||'Original photo'):'Not used'],['Quantity',String(qty)],['Item total',money((v?.price??p?.price??0)*qty)+' · demo price']];
    const target=$('[data-fin-summary]');target.replaceChildren();for(const [key,value] of values){const dt=document.createElement('dt'),dd=document.createElement('dd');dt.textContent=key;dd.textContent=value;target.append(dt,dd);}
  }
  function go(next) {
    if(!selectedProduct()){status.textContent='No compatible product is available.';return;}
    if(data.design.photo&&!photo){status.textContent='Choose your photo before continuing.';photoInput.focus();return;}
    if(!quantity.checkValidity()){quantity.reportValidity();return;}
    step=Math.max(0,Math.min(2,next));root.querySelectorAll('[data-fin-step]').forEach(e=>{e.hidden=Number(e.dataset.finStep)!==step;});root.querySelectorAll('.fin-builder-progress li').forEach((e,i)=>{if(i===step)e.setAttribute('aria-current','step');else e.removeAttribute('aria-current');});
    if(step===2){summary();view='product';root.querySelectorAll('[data-fin-view]').forEach(b=>{b.classList.toggle('is-selected',b.dataset.finView===view);b.setAttribute('aria-pressed',String(b.dataset.finView===view));});preview();}
    const heading=$('[data-fin-step="'+step+'"] h2');heading.tabIndex=-1;heading.focus({preventScroll:true});saveDraft();
  }
  root.querySelectorAll('[data-fin-next]').forEach(b=>b.addEventListener('click',()=>go(step+1)));root.querySelectorAll('[data-fin-prev]').forEach(b=>b.addEventListener('click',()=>go(step-1)));
  root.querySelectorAll('[data-fin-view]').forEach(b=>b.addEventListener('click',()=>{view=b.dataset.finView;root.querySelectorAll('[data-fin-view]').forEach(other=>{other.classList.toggle('is-selected',other===b);other.setAttribute('aria-pressed',String(other===b));});preview();}));
  product?.addEventListener('change',()=>{variants();invalidate();preview();saveDraft();});
  form.addEventListener('input',e=>{if(e.target===photoInput)return;if(e.target===confirm){submit.disabled=!confirm.checked;return;}invalidate();price();preview();saveDraft();});
  form.addEventListener('submit',async e=>{
    if(prepared)return;e.preventDefault();if(step!==2||!confirm.checked)return;
    submit.disabled=true;status.textContent='Saving your design to the bag…';
    try {await render('art');$('[data-fin-preview-field]').value=canvas.toDataURL('image/png');if(data.design.photo&&photoFile){const transfer=new DataTransfer();transfer.items.add(photoFile);photoInput.files=transfer.files;}if(!form.reportValidity()){submit.disabled=false;return;}prepared=true;HTMLFormElement.prototype.submit.call(form);}catch{status.textContent='We could not prepare this preview. Please reload; your browser draft has been kept.';submit.disabled=false;}
  });
  $('[data-fin-clear]').addEventListener('click',async()=>{
    clearTimeout(saving);
    try{await storage('delete');localStorage.removeItem('fin-current-'+data.design.id);}catch{}
    form.reset();photo=null;photoFile=null;if(photoURL)URL.revokeObjectURL(photoURL);photoURL='';if(photoInput)photoInput.value='';
    if($('[data-fin-photo-adjustments]'))$('[data-fin-photo-adjustments]').hidden=true;
    if(data.uploadFirst){$('[data-fin-builder-steps]').hidden=true;$('.fin-builder-photo').classList.remove('is-collapsed');$('[data-fin-photo-next]').textContent='Continue with this photo';}
    step=0;root.querySelectorAll('[data-fin-step]').forEach(e=>e.hidden=e.dataset.finStep!=='0');
    root.querySelectorAll('.fin-builder-progress li').forEach((e,i)=>{if(i===0)e.setAttribute('aria-current','step');else e.removeAttribute('aria-current');});
    variants();invalidate();preview();status.textContent='Your saved draft has been cleared.';
  });
  (async()=>{
    const desired=new URLSearchParams(location.search).get('product');const choice=data.products.find(p=>p.slug===desired);if(choice)product.value=choice.id;
    variants();
    try{const draft=await storage('get');if(draft){for(const [name,value] of Object.entries(draft.fields||{})){const field=form.elements.namedItem(name);if(field&&field.type!=='file')field.value=value;}variants(draft.fields?.fin_variation_id);if(draft.photoFile)await loadPhoto(draft.photoFile);status.textContent='Your previous browser draft has been restored. Review it before adding to your bag.';}}catch{status.textContent='Draft storage is unavailable. Your preview still works while this tab stays open.';}
    ready=true;preview();
  })();
})();
