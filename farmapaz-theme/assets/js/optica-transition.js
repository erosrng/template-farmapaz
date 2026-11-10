(function(){
  var ov=document.getElementById('optica-transition');
  if(!ov)return;
  var body=document.body,skip=document.getElementById('optica-transition-skip'),titleTarget=document.getElementById('optica-transition-title-target'),chip=document.getElementById('optica-transition-chip'),sub=document.getElementById('optica-transition-sub'),logoFrom=document.getElementById('optica-logo-from'),logoTo=document.getElementById('optica-logo-to'),tims=[];
  var farmLogo=(window.farmapazData&&farmapazData.themeUri)?(farmapazData.themeUri+'/assets/images/logo.png'):('/wp-content/themes/farmapaz-theme/assets/images/logo.png');
  var optLogo=(window.farmapazData&&farmapazData.themeUri)?(farmapazData.themeUri+'/assets/images/optica/optica-logo.png'):('/wp-content/themes/farmapaz-theme/assets/images/optica/optica-logo.png');
  function clr(){tims.forEach(clearTimeout);tims=[];}
  function end(){clr();ov.classList.add('out');tims.push(setTimeout(function(){ov.classList.add('is-done');ov.classList.remove('is-active','swap','out','is-reverse');body.classList.remove('optica-lock');if(logoFrom)logoFrom.src=farmLogo;if(logoTo)logoTo.src=optLogo;},900));}
  if(skip)skip.addEventListener('click',end);
  document.querySelectorAll('[data-optica-toggle]').forEach(function(a){
    a.addEventListener('click',function(e){
      var href=a.getAttribute('href'); if(!href)return;
      var toOpt=href.indexOf('/optica')!==-1;
      var toggle=a.getAttribute('data-optica-toggle')||'';
      var isToFarm=toggle==='to-farmacia' || toOpt===false;
      if(chip)chip.textContent=isToFarm?'Sección Farmacia':'Sección Óptica';
      if(titleTarget)titleTarget.textContent=isToFarm?'Farmacia':'Óptica';
      if(sub)sub.textContent=isToFarm?'Medicamentos, cuidado personal y todo lo que necesitas para tu bienestar.':'Monturas originales, lentes de sol, examen de la vista gratis y asesoría personalizada.';
      // Siempre: capa "from" = Farmacia, capa "to" = Óptica.
      // La clase is-reverse invierte el orden visual (Óptica -> Farmacia).
      if(logoFrom)logoFrom.src=farmLogo;
      if(logoTo)logoTo.src=optLogo;
      if(logoFrom)logoFrom.alt='Farmapaz';
      if(logoTo)logoTo.alt='Óptica Farmapaz';
      e.preventDefault();
      ov.classList.remove('is-done','out','swap','is-reverse');
      if(isToFarm)ov.classList.add('is-reverse');
      body.classList.add('optica-lock');
      ov.classList.add('is-active');
      void ov.offsetWidth;
      tims.push(setTimeout(function(){ov.classList.add('swap');},850));
      tims.push(setTimeout(function(){window.location.href=href;},3200));
    });
  });
})();
