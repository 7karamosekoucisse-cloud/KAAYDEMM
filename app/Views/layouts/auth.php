<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Kaay Dem !') ?> — Kaay Dem !</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=7">
  <style>
    html,body { min-height:100vh; }

    /* Fond principal */
    .kd-auth-page {
      min-height:100vh;
      background: linear-gradient(160deg, #06080F 0%, #0D1230 40%, #120A28 75%, #0D0A18 100%);
      padding: 2rem 1rem 3rem;
      display: flex; flex-direction: column;
      align-items: center; justify-content: flex-start;
      position: relative; overflow-x: hidden; overflow-y: auto;
    }

    /* Orbes de lumière en arrière-plan */
    .kd-auth-orb {
      position: fixed; border-radius: 50%; pointer-events: none; z-index: 0;
      filter: blur(80px); animation: kd-orb-float 8s ease-in-out infinite;
    }
    .kd-auth-orb-1 { width:400px;height:400px;top:-120px;right:-100px;background:radial-gradient(circle,rgba(196,92,42,.18),transparent 70%); animation-delay:0s; }
    .kd-auth-orb-2 { width:350px;height:350px;bottom:-100px;left:-80px;background:radial-gradient(circle,rgba(212,168,83,.14),transparent 70%); animation-delay:-3s; }
    .kd-auth-orb-3 { width:250px;height:250px;top:40%;left:50%;transform:translateX(-50%);background:radial-gradient(circle,rgba(139,92,246,.1),transparent 70%); animation-delay:-5s; }
    @keyframes kd-orb-float { 0%,100%{transform:translateY(0) scale(1);} 50%{transform:translateY(-20px) scale(1.05);} }
    .kd-auth-orb-3 { animation-name: kd-orb-float3; }
    @keyframes kd-orb-float3 { 0%,100%{transform:translateX(-50%) translateY(0);} 50%{transform:translateX(-50%) translateY(-15px);} }

    /* Particules flottantes */
    .kd-auth-particles { position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden; }
    .kd-particle {
      position:absolute; border-radius:50%; opacity:0;
      animation: kd-particle-rise linear infinite;
    }
    @keyframes kd-particle-rise {
      0%   { transform:translateY(100vh) scale(0);  opacity:0; }
      10%  { opacity:.5; }
      90%  { opacity:.2; }
      100% { transform:translateY(-10vh) scale(1.2); opacity:0; }
    }

    /* Box auth */
    .kd-auth-box { width:100%; max-width:460px; position:relative; z-index:1; margin:0 auto; }

    /* Logo */
    .kd-auth-logo-wrap { text-align:center; margin-bottom:2rem; }
    .kd-auth-logo-icon {
      width:62px; height:62px; border-radius:18px;
      background: linear-gradient(135deg, #C45C2A, #E8930A, #D4A853);
      background-size:200% auto; animation: kd-shimmer 2.5s linear infinite;
      display:flex; align-items:center; justify-content:center;
      font-size:1.6rem; margin:0 auto .85rem;
      box-shadow: 0 8px 32px rgba(212,168,83,.35), 0 0 0 1px rgba(212,168,83,.2);
    }
    .kd-auth-brand { font-family:'Plus Jakarta Sans',sans-serif;font-size:1.9rem;font-weight:900;color:#fff;letter-spacing:-1px; }
    .kd-auth-tagline { color:rgba(255,255,255,.35);font-size:.82rem;margin-top:.2rem; }

    /* Card glass */
    .kd-auth-card {
      background: rgba(255,255,255,.06) !important;
      backdrop-filter: blur(24px) saturate(1.4);
      border: 1px solid rgba(255,255,255,.1) !important;
      border-radius: 28px !important;
      box-shadow: 0 32px 80px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.12) !important;
      padding: 2.25rem !important;
      animation: kd-card-in .5s cubic-bezier(.22,1,.36,1) both;
    }
    @keyframes kd-card-in { from{opacity:0;transform:translateY(30px) scale(.97);} to{opacity:1;transform:none;} }

    /* Titres dans la card */
    .kd-auth-title { font-family:'Plus Jakarta Sans',sans-serif;font-size:1.5rem;font-weight:900;color:#fff; }
    .kd-auth-sub   { color:rgba(255,255,255,.45);font-size:.88rem; }

    /* Inputs */
    .kd-auth-card .form-control,
    .kd-auth-card .form-select {
      background: rgba(255,255,255,.07) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      color: #fff !important; border-radius: 12px !important;
      transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .kd-auth-card .form-control:focus,
    .kd-auth-card .form-select:focus {
      background: rgba(255,255,255,.11) !important;
      border-color: rgba(212,168,83,.5) !important;
      box-shadow: 0 0 0 3px rgba(212,168,83,.15) !important;
    }
    .kd-auth-card .form-control::placeholder { color:rgba(255,255,255,.3) !important; }
    .kd-auth-card .input-group-text {
      background: rgba(255,255,255,.07) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      color: rgba(255,255,255,.5) !important; border-radius: 12px 0 0 12px !important;
    }
    .kd-auth-card .form-label { color:rgba(255,255,255,.65);font-size:.83rem;font-weight:600;margin-bottom:.4rem; }
    .kd-auth-card .form-check-input { background-color: rgba(255,255,255,.1); border-color: rgba(255,255,255,.2); }
    .kd-auth-card .form-check-label { color:rgba(255,255,255,.45);font-size:.83rem; }
    .kd-auth-card a { color:rgba(212,168,83,.9); }
    .kd-auth-card a:hover { color:#E8C070; }

    /* Séparateur */
    .kd-auth-sep { height:1px; background:linear-gradient(90deg,transparent,rgba(255,255,255,.1),transparent); margin:1.25rem 0; }

    /* Lien bas */
    .kd-auth-footer-link { color:rgba(255,255,255,.4);font-size:.88rem; }
    .kd-auth-footer-link a { color:var(--dp-gold,#D4A853);font-weight:700; }

    /* Alert */
    .kd-auth-card .alert { background:rgba(220,53,69,.15);border:1px solid rgba(220,53,69,.3);color:#fca5a5;border-radius:12px; }
  </style>
</head>
<body class="kd-auth-page">

  <!-- Orbes -->
  <div class="kd-auth-orb kd-auth-orb-1"></div>
  <div class="kd-auth-orb kd-auth-orb-2"></div>
  <div class="kd-auth-orb kd-auth-orb-3"></div>

  <!-- Particules -->
  <div class="kd-auth-particles" id="authParticles"></div>

  <div class="kd-auth-box">
    <!-- Logo -->
    <div class="kd-auth-logo-wrap">
      <a href="<?= url() ?>" class="text-decoration-none">
        <div class="kd-auth-logo-icon"><i class="bi bi-car-front-fill" style="color:#06080F"></i></div>
        <div class="kd-auth-brand">Kaay Dem !</div>
        <div class="kd-auth-tagline">Covoiturage etudiant &middot; Senegal &#127466;&#127474;</div>
      </a>
    </div>

    <!-- Card -->
    <div class="kd-auth-card card">
      <?= $content ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  /* Particules flottantes */
  (function(){
    var container = document.getElementById('authParticles');
    var colors = ['rgba(212,168,83,.6)','rgba(196,92,42,.5)','rgba(21,197,138,.4)','rgba(139,92,246,.4)','rgba(255,255,255,.25)'];
    for(var i=0;i<22;i++){
      var p = document.createElement('div');
      p.className = 'kd-particle';
      var size = 3 + Math.random()*6;
      p.style.cssText = [
        'width:'+size+'px','height:'+size+'px',
        'left:'+Math.random()*100+'%',
        'background:'+colors[Math.floor(Math.random()*colors.length)],
        'animation-duration:'+(8+Math.random()*14)+'s',
        'animation-delay:'+(-Math.random()*12)+'s'
      ].join(';');
      container.appendChild(p);
    }
  })();

  /* Ripple sur bouton auth */
  (function(){
    document.querySelectorAll('.kd-auth-btn').forEach(function(btn){
      btn.style.position='relative'; btn.style.overflow='hidden';
      btn.addEventListener('click',function(e){
        var r=btn.getBoundingClientRect(), size=Math.max(r.width,r.height)*2;
        var w=document.createElement('span');
        w.style.cssText='position:absolute;border-radius:50%;background:rgba(255,255,255,.3);pointer-events:none;' +
          'width:'+size+'px;height:'+size+'px;' +
          'left:'+(e.clientX-r.left-size/2)+'px;top:'+(e.clientY-r.top-size/2)+'px;' +
          'animation:kd-ripple .6s ease-out forwards;';
        btn.appendChild(w); setTimeout(function(){w.remove();},700);
      });
    });

    /* Loading state on submit */
    document.querySelectorAll('form').forEach(function(f){
      var b=f.querySelector('.kd-auth-btn');
      if(!b) return;
      f.addEventListener('submit',function(){
        b.classList.add('loading'); b.disabled=true;
      });
    });
  })();
  </script>
</body>
</html>
