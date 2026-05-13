/**
 * Kool Healthy – Module 5 : Plan Nutritionnel
 * JS commun Front + Back office
 */

/* ─── Utilitaires généraux ──────────────────────────────────── */
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

/** Affiche un toast message temporaire */
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}`;
  Object.assign(toast.style, {
    position: 'fixed', bottom: '24px', right: '24px',
    background: type === 'success' ? '#388E3C' : '#E65100',
    color: '#fff', padding: '12px 20px', borderRadius: '40px',
    fontWeight: '600', fontSize: '.88rem', zIndex: '9999',
    boxShadow: '0 4px 16px rgba(0,0,0,.15)',
    transition: 'opacity .4s', opacity: '1'
  });
  document.body.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 3000);
}

/* ─── Confirmation de suppression ───────────────────────────── */
$$('[data-confirm]').forEach(btn => {
  btn.addEventListener('click', e => {
    if (!confirm(btn.dataset.confirm || 'Confirmer la suppression ?')) {
      e.preventDefault();
    }
  });
});

/* ─── Modale générique (Back office) ────────────────────────── */
function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('open');
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('open');
}
// Fermer en cliquant en dehors
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal')) {
    e.target.classList.remove('open');
  }
});
// Boutons close-modal
$$('.close-modal').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.closest('.modal')?.classList.remove('open');
  });
});

/* ─── Navigation Back office (tabs) ─────────────────────────── */
const navItems = $$('.nav-item[data-tab]');
const tabContents = $$('[data-content]');

function showTab(tabId) {
  tabContents.forEach(c => c.style.display = c.dataset.content === tabId ? 'block' : 'none');
  navItems.forEach(n => {
    n.classList.toggle('active', n.dataset.tab === tabId);
  });
  // Déclencher l'initialisation des graphiques si besoin
  if (typeof initChart === 'function') initChart(tabId);
}

navItems.forEach(item => {
  item.addEventListener('click', () => showTab(item.dataset.tab));
});

// Afficher le premier tab au chargement
if (navItems.length) {
  const firstTab = navItems[0].dataset.tab;
  showTab(firstTab);
}

/* ─── Filtre tableau (recherche côté client) ────────────────── */
const searchInput = $('#tableSearch');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase();
    $$('.data-table tbody tr, .plans-grid .plan-card').forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(q) ? '' : 'none';
    });
  });
}

/* ─── Animation des barres de progression ───────────────────── */
function animateProgressBars() {
  $$('.progress-fill[data-width]').forEach(bar => {
    const w = bar.dataset.width;
    setTimeout(() => { bar.style.width = w + '%'; }, 200);
  });
}
document.addEventListener('DOMContentLoaded', animateProgressBars);

/* ─── Calcul BMR / recommandation calories côté client ──────── */
function calculerCaloriesRecommandees() {
  const poids = parseFloat($('#inputPoids')?.value || 0);
  const taille = parseFloat($('#inputTaille')?.value || 0);
  const age   = parseInt($('#inputAge')?.value || 0);
  const genre = $('#inputGenre')?.value || 'h';

  if (!poids || !taille || !age) return;

  // Formule Mifflin-St Jeor
  let bmr = genre === 'f'
    ? (10 * poids) + (6.25 * taille) - (5 * age) - 161
    : (10 * poids) + (6.25 * taille) - (5 * age) + 5;

  const niveau = parseFloat($('#inputNiveauActivite')?.value || 1.375);
  const tdee = Math.round(bmr * niveau);

  const output = $('#calRecommandees');
  if (output) {
    output.textContent = tdee + ' kcal/jour recommandé';
    output.style.color = '#388E3C';
  }
  const inputCal = $('#inputCalories');
  if (inputCal && !inputCal.value) inputCal.value = tdee;
}

$$('#inputPoids, #inputTaille, #inputAge, #inputGenre, #inputNiveauActivite').forEach(el => {
  el?.addEventListener('input', calculerCaloriesRecommandees);
});

/* ─── Validation du formulaire de plan ──────────────────────── */
const planForm = $('#planForm');
if (planForm) {
  planForm.addEventListener('submit', e => {
    const debut = new Date($('#inputDateDebut')?.value);
    const fin   = new Date($('#inputDateFin')?.value);
    const cal   = parseFloat($('#inputCalories')?.value || 0);

    if (fin <= debut) {
      e.preventDefault();
      showToast('La date de fin doit être après la date de début.', 'error');
      return;
    }
    if (cal < 800 || cal > 5000) {
      e.preventDefault();
      showToast('Les calories doivent être comprises entre 800 et 5 000 kcal.', 'error');
    }
  });
}
