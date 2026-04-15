<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Kool Healthy | Défis, Classement & Récompenses</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: #F5F5F5; color: #2C3E2F; scroll-behavior: smooth; }
    :root {
      --vert-kool: #4CAF50;
      --vert-kool-dark: #388E3C;
      --vert-kool-light: #E8F5E9;
      --bleu-tech: #29B6F6;
      --bleu-tech-dark: #0288D1;
      --bleu-tech-light: #E1F5FE;
      --blanc: #FFFFFF;
      --gris-clair: #F5F5F5;
      --gris-moyen: #E0E0E0;
      --gris-texte: #616161;
      --ombre-legere: 0 8px 20px rgba(0,0,0,0.05);
      --shadow-hover: 0 12px 28px rgba(0,0,0,0.08);
    }
    .navbar { background: var(--blanc); padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; box-shadow: var(--ombre-legere); position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid var(--gris-moyen); backdrop-filter: blur(2px); }
    .logo { display: flex; align-items: center; gap: 12px; }
    .logo i { font-size: 2rem; color: var(--vert-kool); }
    .logo h1 { font-size: 1.6rem; font-weight: 700; color: var(--vert-kool); }
    .nav-links { display: flex; gap: 2rem; align-items: center; flex-wrap: wrap; }
    .nav-links a { text-decoration: none; color: #4A5B4E; font-weight: 500; transition: 0.2s; cursor: pointer; }
    .nav-links a:hover { color: var(--bleu-tech); transform: translateY(-2px); }
    .btn-connect { background: var(--vert-kool); color: white; border: none; padding: 0.6rem 1.5rem; border-radius: 40px; font-weight: 600; cursor: pointer; transition: 0.2s; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    .btn-connect:hover { background: var(--vert-kool-dark); transform: scale(0.98); }
    .btn-outline { background: transparent; border: 1.5px solid var(--bleu-tech); color: var(--bleu-tech); padding: 0.6rem 1.5rem; border-radius: 40px; font-weight: 600; cursor: pointer; transition: 0.2s; }
    .btn-outline:hover { background: var(--bleu-tech-light); border-color: var(--bleu-tech-dark); }
    .hero { background: linear-gradient(135deg, var(--vert-kool-light) 0%, var(--bleu-tech-light) 100%); padding: 3rem 5%; text-align: center; border-radius: 0 0 40px 40px; margin-bottom: 1rem; }
    .hero h1 { font-size: 2.5rem; font-weight: 800; color: var(--vert-kool-dark); letter-spacing: -0.02em; }
    .hero p { margin-top: 1rem; color: #4A5B4E; font-size: 1.1rem; }
    .section { padding: 2.5rem 5%; }
    .section-title { font-size: 1.8rem; font-weight: 700; color: var(--vert-kool); margin-bottom: 1.8rem; display: flex; align-items: center; gap: 12px; border-left: 5px solid var(--bleu-tech); padding-left: 18px; }
    .stats-user { background: var(--blanc); border-radius: 32px; padding: 1.8rem; display: flex; justify-content: space-around; flex-wrap: wrap; gap: 1.5rem; margin-bottom: 2rem; border: 1px solid var(--gris-moyen); box-shadow: var(--ombre-legere); backdrop-filter: blur(2px); }
    .stat-user-item { text-align: center; background: var(--gris-clair); padding: 0.8rem 1.5rem; border-radius: 48px; min-width: 140px; transition: all 0.2s; }
    .stat-user-value { font-size: 2.2rem; font-weight: 800; color: var(--vert-kool); }
    .badge-list { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.5rem; }
    .badge-item { background: var(--bleu-tech-light); border-radius: 60px; padding: 0.6rem 1.4rem; display: flex; align-items: center; gap: 8px; font-weight: 600; color: var(--bleu-tech-dark); box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s; }
    .badge-item:hover { transform: translateY(-2px); background: #cceefc; }
    .defis-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; margin-top: 1rem; }
    .defi-card { background: var(--blanc); border-radius: 28px; padding: 1.5rem; box-shadow: var(--ombre-legere); border: 1px solid var(--gris-moyen); transition: all 0.25s ease; }
    .defi-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); border-color: var(--bleu-tech); }
    .defi-points { background: var(--vert-kool-light); color: var(--vert-kool-dark); padding: 5px 14px; border-radius: 40px; font-size: 0.8rem; font-weight: 700; display: inline-block; }
    .progress-bar { background: var(--gris-moyen); border-radius: 20px; height: 8px; margin: 12px 0; overflow: hidden; }
    .progress-fill { background: linear-gradient(90deg, var(--vert-kool), var(--bleu-tech)); height: 100%; border-radius: 20px; width: 0%; }
    .btn-participate { background: var(--bleu-tech); color: white; border: none; padding: 10px 20px; border-radius: 60px; font-weight: 600; cursor: pointer; margin-top: 16px; width: 100%; transition: 0.2s; }
    .btn-participate:hover { background: var(--bleu-tech-dark); transform: scale(0.98); }
    .ranking-list { display: flex; flex-direction: column; gap: 1rem; }
    .ranking-card { background: var(--blanc); border-radius: 20px; padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1.2rem; border: 1px solid var(--gris-moyen); transition: all 0.2s; box-shadow: 0 2px 6px rgba(0,0,0,0.03); }
    .ranking-card:hover { background: #fafeff; transform: translateX(5px); border-left: 4px solid var(--vert-kool); }
    .rank-num { font-size: 1.6rem; font-weight: 800; width: 55px; color: var(--bleu-tech); background: var(--bleu-tech-light); border-radius: 40px; text-align: center; padding: 6px 0; }
    .badges-collection { display: flex; flex-wrap: wrap; gap: 1.2rem; margin-top: 1rem; justify-content: flex-start; }
    .badge-card { background: linear-gradient(145deg, #fff, #f8f9fa); border-radius: 36px; padding: 1rem 1.8rem; display: inline-flex; align-items: center; gap: 12px; box-shadow: var(--ombre-legere); border: 1px solid rgba(76,175,80,0.2); transition: all 0.2s; }
    .badge-card i { font-size: 1.8rem; color: var(--vert-kool); }
    .badge-card span { font-weight: 700; color: #2c3e2f; }
    .badge-requis { font-size: 0.7rem; background: var(--gris-moyen); border-radius: 30px; padding: 2px 10px; margin-left: 8px; color: var(--gris-texte); }
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 2000; backdrop-filter: blur(4px); }
    .modal-content { background: white; padding: 2rem; border-radius: 36px; width: 90%; max-width: 400px; text-align: center; border-top: 6px solid var(--vert-kool); box-shadow: 0 25px 40px rgba(0,0,0,0.2); }
    .modal-content input { width: 100%; margin: 12px 0; padding: 12px 16px; border-radius: 60px; border: 1px solid var(--gris-moyen); font-family: inherit; }
    .close-modal { float: right; font-size: 1.8rem; cursor: pointer; transition: 0.2s; }
    .footer { background: #1E3A2E; color: #C6E0D4; padding: 3rem 5% 2rem; margin-top: 3rem; border-radius: 40px 40px 0 0; }
    .tab-container { background: var(--blanc); border-radius: 60px; display: inline-flex; margin-bottom: 2rem; box-shadow: var(--ombre-legere); padding: 5px; background: #f0f2f0; }
    .tab-btn { background: transparent; border: none; padding: 12px 28px; border-radius: 40px; font-weight: 600; cursor: pointer; transition: 0.2s; color: #4a5b4e; font-size: 1rem; }
    .tab-btn.active { background: var(--vert-kool); color: white; box-shadow: 0 2px 8px rgba(76,175,80,0.3); }
    .tab-pane { display: none; animation: fadeIn 0.3s ease; }
    .tab-pane.active-pane { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 768px) { .hero h1 { font-size: 1.8rem; } .tab-btn { padding: 8px 18px; font-size: 0.9rem; } .section-title { font-size: 1.5rem; } }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo"><i class="fas fa-seedling"></i><h1>Kool Healthy</h1></div>
    <div class="nav-links"><a id="navAccueil">🏠 Accueil</a><a id="navDefisUnified">🏆 Défis & Récompenses</a><button class="btn-outline" id="openSignupBtn">S'inscrire</button><button class="btn-connect" id="openLoginBtn">Se connecter</button></div>
  </nav>

  <!-- SECTION ACCUEIL (dashboard only, plus de défis populaires) -->
  <div id="accueilSection">
    <div class="hero"><h1>Bienvenue dans votre aventure 🌿<br>Gagnez des points,<br>devenez un héros durable</h1><p>Suivez vos progrès, relevez des défis et inspirez la communauté</p></div>
    <div class="section"><h2 class="section-title"><i class="fas fa-chart-line"></i> Votre tableau de bord</h2>
      <div class="stats-user"><div class="stat-user-item"><div class="stat-user-value" id="userPoints">0</div><p>Points totaux</p></div><div class="stat-user-item"><div class="stat-user-value" id="userDefisCompleted">0</div><p>Défis complétés</p></div></div>
      <div style="margin-top: 1rem;"><h3 style="margin-bottom: 1rem; font-weight: 600;"><i class="fas fa-trophy"></i> Vos succès</h3><div class="badge-list" id="userAchievementsList"></div></div>
    </div>
  </div>

  <!-- SECTION UNIFIÉE : DÉFIS + CLASSEMENT + BADGES (regroupées joliment) -->
  <div id="defisUnifiedSection" style="display:none;" class="section">
    <h2 class="section-title"><i class="fas fa-chalkboard-user"></i> Défis, Classement & Récompenses</h2>
    
    <!-- Tabs élégants -->
    <div class="tab-container">
      <button class="tab-btn active" data-tab="defisTab">🏁 Défis actifs</button>
      <button class="tab-btn" data-tab="classementTab">📈 Classement</button>

    </div>

    <!-- Pane Défis -->
    <div id="defisTab" class="tab-pane active-pane">
      <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: baseline;"><p class="badge-tech" style="color: var(--gris-texte);"><i class="fas fa-info-circle"></i> Participez et gagnez des points</p></div>
      <div class="defis-grid" id="allDefisUnifiedGrid"></div>
    </div>

    <!-- Pane Classement -->
    <div id="classementTab" class="tab-pane">
      <div style="margin-bottom: 1rem;"><p><i class="fas fa-chart-simple"></i> Les champions de la communauté durable</p></div>
      <div id="rankingUnifiedList" class="ranking-list"></div>
    </div>
  </div>

  <footer class="footer"><div class="footer-col"><h4>Kool Healthy</h4><p>Gamification & nutrition durable — Manger mieux, gagner des points, préserver la planète 🌍</p></div><div class="footer-bottom" style="margin-top:1rem;"><p>© 2025 Kool Healthy — Ensemble pour un futur healthy</p></div></footer>

  <!-- Modales -->
  <div id="loginModal" class="modal"><div class="modal-content"><span class="close-modal" id="closeLoginModal">&times;</span><h3>Connexion</h3><input type="email" placeholder="Email" id="loginEmail"><input type="password" placeholder="Mot de passe" id="loginPwd"><button class="btn-connect" style="width:100%; margin-top: 12px;" id="doLoginBtn">Se connecter</button></div></div>
  <div id="signupModal" class="modal"><div class="modal-content"><span class="close-modal" id="closeSignupModal">&times;</span><h3>Inscription</h3><input type="text" placeholder="Nom complet" id="signupName"><input type="email" placeholder="Email" id="signupEmail"><input type="password" placeholder="Mot de passe" id="signupPwd"><button class="btn-connect" style="width:100%; margin-top:12px;" id="doSignupBtn">S'inscrire</button></div></div>

  <script>
    // ---------- DATA MODEL ----------
    let currentUser = { 
      name: "Sophie M.", 
      points: 980, 
      badges: [{nom:"Éco-citoyen", icone:"fa-leaf"},{nom:"Chef végétal", icone:"fa-carrot"}], 
      defisCompletes: 4 
    };
    
    let allDefis = [
      { id:1, titre:"Manger 5 fruits/légumes par jour", type:"nutrition", points:50, participants:89, progression:80 },
      { id:2, titre:"Réduire son empreinte carbone", type:"ecologie", points:100, participants:45, progression:45 },
      { id:3, titre:"Tester 3 recettes végétales", type:"recette", points:75, participants:62, progression:100 },
      { id:4, titre:"Partager un repas durable", type:"social", points:30, participants:34, progression:0 },
      { id:5, titre:"Zero déchet pendant 3 jours", type:"ecologie", points:120, participants:27, progression:20 },
      { id:6, titre:"Cuisiner local une semaine", type:"nutrition", points:85, participants:51, progression:60 }
    ];
    
    let classement = [
      { rang:1, nom:"Julien R.", points:1250, defis:5 },
      { rang:2, nom:"Sophie M.", points:980, defis:4 },
      { rang:3, nom:"Léa B.", points:750, defis:3 },
      { rang:4, nom:"Thomas L.", points:620, defis:2 },
      { rang:5, nom:"Emma C.", points:510, defis:2 },
      { rang:6, nom:"Adam K.", points:430, defis:1 }
    ];
    
    // Helper UI updates
    function updateUserUI() {
      document.getElementById('userPoints').innerText = currentUser.points;
      document.getElementById('userDefisCompleted').innerText = currentUser.defisCompletes;
      const achievementsContainer = document.getElementById('userAchievementsList');
      if(achievementsContainer) {
        achievementsContainer.innerHTML = currentUser.defisCompletes > 0 ? `<div class="badge-item"><i class="fas fa-trophy"></i> ${currentUser.defisCompletes} défi(s) complété(s)</div>` : '<div class="badge-item" style="background:#E0E0E0;">Aucun succès pour l’instant, relevez des défis !</div>';
      }
    }
    
    // Fonction pour participer (simulation)
    window.participateDefi = (id) => {
      const defi = allDefis.find(d => d.id === id);
      if(defi) {
        if(defi.progression >= 100) {
          alert(`🎉 Défi "${defi.titre}" déjà complété ! Continuez sur d'autres défis.`);
        } else {
          // petite simulation de progression + points
          let gain = Math.floor(defi.points * 0.3);
          alert(`🎉 Vous avez rejoint le défi : "${defi.titre}" ! Réalisez les actions pour gagner ${defi.points} pts. +${gain} pts de participation (bonus de motivation).`);
          // Simule mini-avancée (user gagne un peu)
          currentUser.points += gain;
          updateUserUI();
          renderUnifiedDefis(); // refresh
          renderUnifiedRanking();
        }
      } else {
        alert(`Défi en préparation !`);
      }
    };
    
    // Rendu des défis dans la section unifiée (avec progression)
    function renderUnifiedDefis() {
      const container = document.getElementById('allDefisUnifiedGrid');
      if(!container) return;
      container.innerHTML = allDefis.map(d => `
        <div class="defi-card">
          <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
            <h3 style="font-size:1.25rem;">${d.titre}</h3>
            <span class="defi-points">${d.points} pts</span>
          </div>
          <div style="margin: 8px 0 6px;"><span class="badge-tech" style="background:var(--bleu-tech-light); padding:2px 10px; border-radius:30px; font-size:0.75rem;">${d.type}</span> <span style="margin-left:8px;">👥 ${d.participants}</span></div>
          <div class="progress-bar"><div class="progress-fill" style="width:${d.progression}%"></div></div>
          <p style="font-size:0.8rem; margin-bottom:8px;">Progression: ${d.progression}%</p>
          <button class="btn-participate" onclick="participateDefi(${d.id})">${d.progression >= 100 ? '✅ Complété' : '▶ Participer'}</button>
        </div>
      `).join('');
    }
    
    // Rendu classement unifié
    function renderUnifiedRanking() {
      const rankContainer = document.getElementById('rankingUnifiedList');
      if(!rankContainer) return;
      rankContainer.innerHTML = classement.map(c => `
        <div class="ranking-card">
          <div class="rank-num">#${c.rang}</div>
          <div style="flex:1;"><strong>${c.nom}</strong><div style="font-size:0.85rem; color:var(--gris-texte);">${c.points} points · ${c.defis} défis</div></div>
          <i class="fas fa-trophy" style="color:${c.rang === 1 ? '#FFD966' : c.rang === 2 ? '#C0C0C0' : c.rang === 3 ? '#CD7F32' : '#B0BEC5'}; font-size:1.4rem;"></i>
        </div>
      `).join('');
    }
    
    // Rafraîchir tout l'écosystème unifié
    function refreshUnifiedSections() {
      renderUnifiedDefis();
      renderUnifiedRanking();
    }
    
    // Gestion des onglets (tabs)
    function initTabs() {
      const tabs = document.querySelectorAll('.tab-btn');
      const panes = document.querySelectorAll('.tab-pane');
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          const tabId = tab.getAttribute('data-tab');
          tabs.forEach(t => t.classList.remove('active'));
          tab.classList.add('active');
          panes.forEach(pane => pane.classList.remove('active-pane'));
          const activePane = document.getElementById(tabId);
          if (activePane) activePane.classList.add('active-pane');
          // Option: re-render spécifique
          if (tabId === 'classementTab') renderUnifiedRanking();
          if (tabId === 'defisTab') renderUnifiedDefis();
        });
      });
    }
    
    // Navigation entre sections (Accueil / Défis unifié)
    function showSection(section) {
      const accueilDiv = document.getElementById('accueilSection');
      const defisUnifiedDiv = document.getElementById('defisUnifiedSection');
      if(section === 'accueil') {
        accueilDiv.style.display = 'block';
        defisUnifiedDiv.style.display = 'none';
      } else if(section === 'defisUnified') {
        accueilDiv.style.display = 'none';
        defisUnifiedDiv.style.display = 'block';
        refreshUnifiedSections();
      }
    }
    
    // Event listeners navigation
    document.getElementById('navAccueil').onclick = () => showSection('accueil');
    document.getElementById('navDefisUnified').onclick = () => {
      showSection('defisUnified');
      const defiTabBtn = document.querySelector('.tab-btn[data-tab="defisTab"]');
      if(defiTabBtn && !defiTabBtn.classList.contains('active')) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        defiTabBtn.classList.add('active');
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active-pane'));
        document.getElementById('defisTab').classList.add('active-pane');
        renderUnifiedDefis();
      }
    };
    
    // Modales gestion
    const loginModal = document.getElementById('loginModal');
    const signupModal = document.getElementById('signupModal');
    document.getElementById('openLoginBtn').onclick = () => loginModal.style.display = 'flex';
    document.getElementById('openSignupBtn').onclick = () => signupModal.style.display = 'flex';
    document.getElementById('closeLoginModal').onclick = () => loginModal.style.display = 'none';
    document.getElementById('closeSignupModal').onclick = () => signupModal.style.display = 'none';
    
    document.getElementById('doLoginBtn').onclick = () => {
      alert('Connexion réussie ! Bienvenue Sophie.');
      loginModal.style.display = 'none';
      currentUser = { name:"Sophie M.", points:980, defisCompletes:4 };
      updateUserUI();
      renderUnifiedRanking();
    };
    document.getElementById('doSignupBtn').onclick = () => {
      alert('Inscription réussie ! Vous pouvez maintenant vous connecter.');
      signupModal.style.display = 'none';
    };
    window.onclick = (e) => { if(e.target === loginModal) loginModal.style.display='none'; if(e.target === signupModal) signupModal.style.display='none'; };

    // Mise à jour initiale de l'UI dashboard
    updateUserUI();
    renderUnifiedDefis();
    renderUnifiedRanking();
    initTabs();
    showSection('accueil');
    
    const originalParticipate = window.participateDefi;
    window.participateDefi = (id) => {
      const defi = allDefis.find(d => d.id === id);
      if(defi && defi.progression < 100) {
        let gain = Math.floor(defi.points * 0.25);
        currentUser.points += gain;
        updateUserUI();
        alert(`✅ Participation confirmée ! Vous gagnez ${gain} points bonus. Continuez à progresser !`);
        if(document.getElementById('defisUnifiedSection').style.display === 'block') {
          renderUnifiedDefis();
          renderUnifiedRanking();
        }
      } else if(defi && defi.progression >= 100) {
        alert(`Défi déjà complété. Essayez un autre challenge !`);
      } else {
        alert(`Défi non trouvé.`);
      }
    };
  </script>
</body>
</html>
