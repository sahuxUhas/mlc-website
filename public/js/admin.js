(function(){'use strict';var root=document.documentElement;
(function(){var t=null;try{t=localStorage.getItem('mc_admin_theme')}catch(e){}
if(t==='dark')root.classList.add('dark');else if(t==='light')root.classList.remove('dark');
else if(window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches)root.classList.add('dark')})();
/* ===== মোডাল (Create/Edit ফর্ম) ===== */
function mcOpenModal(id){var m=document.getElementById(id);if(m){m.classList.remove('hidden');m.classList.add('flex');document.documentElement.classList.add('mc-lock')}}
function mcCloseModal(id){var m=document.getElementById(id);if(m){m.classList.add('hidden');m.classList.remove('flex');document.documentElement.classList.remove('mc-lock')}}
document.addEventListener('click',function(e){
 var op=e.target.closest('[data-mc-modal-open]');
 if(op){mcOpenModal(op.getAttribute('data-mc-modal-open'));return}
 var cl=e.target.closest('[data-mc-modal-close]');
 if(cl){mcCloseModal(cl.getAttribute('data-mc-modal-close'));return}
 var bg=e.target.closest('[data-mc-modal]');
 if(bg&&e.target===bg){mcCloseModal(bg.getAttribute('data-mc-modal'));return}
});
document.addEventListener('keydown',function(e){
 if(e.key==='Escape'){document.querySelectorAll('[data-mc-modal]').forEach(function(m){if(!m.classList.contains('hidden'))mcCloseModal(m.getAttribute('data-mc-modal'))})}
});

/* ===== ট্যাব প্যানেল (সেটিংস/SEO গ্রুপ) ===== */
document.addEventListener('click',function(e){
 var t=e.target.closest('[data-mc-tab]');
 if(!t)return;
 var group=t.getAttribute('data-mc-tab-group'),key=t.getAttribute('data-mc-tab');
 document.querySelectorAll('[data-mc-tab-group="'+group+'"]').forEach(function(b){b.classList.remove('bg-[#E21D2B]','text-white');b.classList.add('bg-slate-100','text-slate-600')});
 t.classList.add('bg-[#E21D2B]','text-white');t.classList.remove('bg-slate-100','text-slate-600');
 document.querySelectorAll('[data-mc-pane-group="'+group+'"]').forEach(function(p){p.classList.toggle('hidden',p.getAttribute('data-mc-pane-group-key')!==key)});
});

/* ===== টেবিলের সব চেকবক্স একসাথে ===== */
document.addEventListener('change',function(e){
 if(e.target.id==='checkAll'||e.target.matches('[data-check-all]')){
  var scope=e.target.getAttribute('data-check-all')||'.bulk-check';
  document.querySelectorAll(scope).forEach(function(c){c.checked=e.target.checked});
 }
});

/* ===== রিঅর্ডার: উপরে/নিচে সরানো + hidden order ইনপুট আপডেট ===== */
document.addEventListener('click',function(e){
 var btn=e.target.closest('[data-move]');
 if(!btn)return;
 var item=btn.closest('[data-sortable-item]');
 var list=item&&item.parentElement;
 if(!item||!list)return;
 if(btn.getAttribute('data-move')==='up'&&item.previousElementSibling)list.insertBefore(item,item.previousElementSibling);
 if(btn.getAttribute('data-move')==='down'&&item.nextElementSibling)list.insertBefore(item.nextElementSibling,item);
 mcSyncOrder(list);
});
function mcSyncOrder(list){
 var ids=[];
 list.querySelectorAll('[data-sortable-item]').forEach(function(it){ids.push(it.getAttribute('data-sortable-id'))});
 var input=document.querySelector('[data-order-input]');
 if(input)input.value=ids.join(',');
}

document.addEventListener('click',function(e){
 if(e.target.closest('#adminThemeToggle')){var d=root.classList.toggle('dark');try{localStorage.setItem('mc_admin_theme',d?'dark':'light')}catch(x){}}
 if(e.target.closest('#openSidebar'))document.getElementById('adminShell').classList.add('nav-open');
 if(e.target.closest('#closeSidebar'))document.getElementById('adminShell').classList.remove('nav-open')});
document.addEventListener('submit',function(e){var f=e.target;if(f.dataset.confirm&&!window.confirm(f.dataset.confirm))e.preventDefault()});
setTimeout(function(){document.querySelectorAll('.mc-toast').forEach(function(el){el.style.transition='opacity .4s ease';el.style.opacity='0';setTimeout(function(){el.remove()},450)})},6000);
document.addEventListener('click',function(e){var b=e.target.closest('[data-copy]');if(!b)return;var t=b.getAttribute('data-copy');
 if(navigator.clipboard)navigator.clipboard.writeText(t).then(function(){flash(b)});
 else{var ta=document.createElement('textarea');ta.value=t;ta.style.position='fixed';ta.style.opacity='0';document.body.appendChild(ta);ta.select();try{document.execCommand('copy');flash(b)}catch(x){}document.body.removeChild(ta)}});
function flash(b){var o=b.innerHTML;b.innerHTML='<i class="ph-fill ph-check"></i> কপি হয়েছে';setTimeout(function(){b.innerHTML=o},1400)}
document.querySelectorAll('[data-dropzone]').forEach(function(z){var i=document.querySelector(z.getAttribute('data-dropzone'));if(!i)return;
 ['dragenter','dragover'].forEach(function(ev){z.addEventListener(ev,function(e){e.preventDefault();z.classList.add('ring-2','ring-[#E21D2B]','bg-red-50')})});
 ['dragleave','drop'].forEach(function(ev){z.addEventListener(ev,function(e){e.preventDefault();z.classList.remove('ring-2','ring-[#E21D2B]','bg-red-50')})});
 z.addEventListener('drop',function(e){if(e.dataTransfer&&e.dataTransfer.files.length){i.files=e.dataTransfer.files;i.dispatchEvent(new Event('change'));
  /* News Editor এ ফাইল ড্রপ করলে ফর্ম স্বয়ংক্রিয়ভাবে সাবমিট হবে না (data-no-auto-submit) */
  if(!i.hasAttribute('data-no-auto-submit')&&i.form)i.form.submit()}});
 z.addEventListener('click',function(){i.click()})});
document.querySelectorAll('[data-slug-from]').forEach(function(t){var s=document.querySelector(t.getAttribute('data-slug-from'));if(!s)return;
 t.addEventListener('input',function(){if(s.dataset.touched==='1')return;
 s.value=t.value.trim().toLowerCase().replace(/[^\u0980-\u09FFa-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'')});
 s.addEventListener('input',function(){s.dataset.touched='1'})});
document.querySelectorAll('[data-preview]').forEach(function(i){i.addEventListener('change',function(){
 var b=document.querySelector(i.getAttribute('data-preview'));
 if(b&&i.files&&i.files[0]){var r=new FileReader();r.onload=function(e){b.src=e.target.result;b.classList.remove('hidden')};r.readAsDataURL(i.files[0])}})});
document.querySelectorAll('[data-maxlen]').forEach(function(el){var o=document.querySelector(el.getAttribute('data-maxlen'));if(!o)return;
 var m=parseInt(el.getAttribute('maxlength')||'0',10);function u(){o.textContent=el.value.length+(m?' / '+m:'')}el.addEventListener('input',u);u()})})();
