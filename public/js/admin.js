(function(){'use strict';var root=document.documentElement;
(function(){var t=null;try{t=localStorage.getItem('mc_admin_theme')}catch(e){}
if(t==='dark')root.classList.add('dark');else if(t==='light')root.classList.remove('dark');
else if(window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches)root.classList.add('dark')})();
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
 z.addEventListener('drop',function(e){if(e.dataTransfer&&e.dataTransfer.files.length){i.files=e.dataTransfer.files;i.form&&i.form.submit()}});
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
