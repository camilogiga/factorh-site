(function(){
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!('IntersectionObserver' in window)) return;
  document.documentElement.classList.add('fh-js');
  var root = document.body.firstElementChild;
  var sections = root ? Array.prototype.slice.call(root.children) : [];
  var hero = document.querySelector('.fh-hero-copy') ? sections[0] : null;

  // Bar charts: hero band and the citation chart.
  document.querySelectorAll('svg').forEach(function(svg){
    var rects = svg.querySelectorAll('rect');
    if (rects.length >= 8 && !svg.closest('a[aria-label]') && !svg.closest('header')) {
      svg.classList.add('fh-bars');
      rects.forEach(function(r, i){ r.style.setProperty('--d', (i * 60) + 'ms'); });
    }
  });

  // Scroll reveal for content blocks outside the hero.
  var sel = 'h1,h2,h3,p,figure,article,li,blockquote,form,img,.fh-card,section > div > a';
  var picked = [];
  sections.forEach(function(sec){
    if (sec === hero || sec.tagName === 'HEADER') return;
    sec.querySelectorAll(sel).forEach(function(el){
      if (el.closest('header,nav,.fh-marquee,.fh-strip,[hidden]')) return;
      if (el.closest('.fh-hero-photo')) return;
      for (var i = 0; i < picked.length; i++) if (picked[i].contains(el)) return;
      picked.push(el);
    });
  });
  picked.forEach(function(el){
    var sib = Array.prototype.indexOf.call(el.parentNode.children, el);
    el.style.setProperty('--d', Math.min(sib, 6) * 80 + 'ms');
    el.classList.add('fh-rv');
  });

  // Giant decorative glyphs.
  document.querySelectorAll('span[aria-hidden="true"]').forEach(function(s){
    var fs = parseFloat(getComputedStyle(s).fontSize);
    if (fs > 150) { s.classList.add('fh-glyph'); }
  });

  var targets = picked.concat(Array.prototype.slice.call(document.querySelectorAll('.fh-bars,.fh-edge,.fh-edge-l,.fh-glyph')));
  if (reduce) { targets.forEach(function(t){ t.classList.add('fh-in'); }); return; }
  var io = new IntersectionObserver(function(entries){
    fired = true; entries.forEach(function(e){ if (e.isIntersecting) { e.target.classList.add('fh-in'); io.unobserve(e.target); } });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });

  targets.forEach(function(t){ io.observe(t); });
  // Safety net: if the observer never reports (some embedded viewers), show everything.
  var fired = false;
  setTimeout(function(){ if (!fired) targets.forEach(function(t){ t.classList.add('fh-in'); }); }, 1500);

  // Sticky header on inner pages.
  var head = root && root.querySelector(':scope > header');
  if (head) {
    head.classList.add('fh-sticky');
    head.style.background = getComputedStyle(root).backgroundColor;
    window.addEventListener('scroll', function(){ head.classList.toggle('fh-scrolled', window.scrollY > 8); }, { passive: true });
  }
})();
