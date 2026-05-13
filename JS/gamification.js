// ===== NAVIGATION PAR ONGLETS =====
document.querySelectorAll('[data-section]').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        const target = this.dataset.section;

        // Masquer toutes les sections
        document.querySelectorAll('.gami-section').forEach(s => s.style.display = 'none');

        // Afficher la section cible
        const section = document.getElementById(target + 'Section');
        if (section) section.style.display = 'block';

        // Mettre à jour les onglets actifs
        document.querySelectorAll('[data-section]').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// ===== SMOOTH SCROLL =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && document.querySelector(href)) {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// ===== BARRE DE PROGRESSION ANIMÉE =====
document.querySelectorAll('.progress-fill[data-width]').forEach(bar => {
    const width = bar.getAttribute('data-width');
    setTimeout(() => { bar.style.width = width + '%'; }, 200);
});

// ===== TABS DE CONTENU =====
document.querySelectorAll('.tab-btn').forEach(tab => {
    tab.addEventListener('click', function () {
        const tabId = this.dataset.tab;
        if (!tabId) return;
        document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active-pane'));
        this.classList.add('active');
        const pane = document.getElementById(tabId);
        if (pane) pane.classList.add('active-pane');
    });
});

// ===== AUTH TABS =====
document.querySelectorAll('.auth-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        const targetId = this.dataset.authTab;
        if (!targetId) return;
        document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.auth-panel-inner').forEach(panel => panel.classList.remove('active-auth-panel'));
        this.classList.add('active');
        const targetPanel = document.getElementById(targetId);
        if (targetPanel) targetPanel.classList.add('active-auth-panel');
    });
});

// ===== CONFIRMATION SUPPRESSION =====
document.querySelectorAll('.btn-delete-confirm').forEach(btn => {
    btn.addEventListener('click', function (e) {
        if (!confirm('Confirmer la suppression ?')) {
            e.preventDefault();
        }
    });
});

// ============================================================
// ===== BACKOFFICE – MODALES & VALIDATION DÉFIS/PARTICIPATIONS
// ============================================================

(function () {
    // --- Références aux éléments du DOM ---
    const addDefiModal = document.getElementById('addDefiModal');
    const editDefiModal = document.getElementById('editDefiModal');
    const openGlobalDefiBtn = document.getElementById('openGlobalDefiBtn');
    const quickAddDefi = document.getElementById('quickAddDefi');
    const closeDefiModalBtn = document.getElementById('closeDefiModal');
    const closeEditDefiModalBtn = document.getElementById('closeEditDefiModal');
    const addDefiForm = document.getElementById('addDefiForm');
    const editDefiForm = document.getElementById('editDefiForm');
    const editDefiButtons = document.querySelectorAll('.edit-defi-btn');

    const addParticipationModal = document.getElementById('addParticipationModal');
    const editParticipationModal = document.getElementById('editParticipationModal');
    const openAddParticipationBtn = document.getElementById('openAddParticipationBtn');
    const closeAddParticipationModalBtn = document.getElementById('closeAddParticipationModal');
    const closeEditParticipationModalBtn = document.getElementById('closeEditParticipationModal');
    const addParticipationForm = document.getElementById('addParticipationForm');
    const editParticipationForm = document.getElementById('editParticipationForm');
    const editParticipationButtons = document.querySelectorAll('.edit-participation-btn');

    // Si aucun élément du backoffice n'existe, on sort (page front-office)
    if (!addDefiModal && !editDefiModal && !addParticipationModal) return;

    // --- Utilitaires modales ---
    function openModalByType(type) {
        const modal = document.getElementById('modal' + type.charAt(0).toUpperCase() + type.slice(1));
        if (modal) modal.style.display = 'flex';
    }

    function openModal(modal) {
        if (modal) modal.style.display = 'flex';
    }

    function closeModal(modal) {
        if (modal) modal.style.display = 'none';
    }

    // ===== VALIDATION DÉFIS =====

    // Collecter les titres existants depuis le tableau des défis
    function getExistingDefiTitles() {
        const titres = [];
        document.querySelectorAll('#defisListUnified tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 2) {
                titres.push({
                    id: cells[0].textContent.trim(),
                    titre: cells[1].textContent.trim().toLowerCase()
                });
            }
        });
        return titres;
    }

    function validateDefiForm(form) {
        const titre = form.querySelector('[name="titre"]').value.trim();
        const typeField = form.querySelector('[name="type"]').value;
        const pointsStr = form.querySelector('[name="points"]').value.trim();
        const points = parseInt(pointsStr, 10);
        const dateDebut = form.querySelector('[name="date_debut"]').value;
        const dateFin = form.querySelector('[name="date_fin"]').value;

        // === Vérification des champs vides ===
        if (!titre) {
            alert('Le titre du défi est obligatoire.');
            return false;
        }
        if (!typeField) {
            alert('Le type du défi est obligatoire.');
            return false;
        }
        if (!pointsStr) {
            alert('Les points sont obligatoires.');
            return false;
        }
        if (!dateDebut) {
            alert('La date de début est obligatoire.');
            return false;
        }
        if (!dateFin) {
            alert('La date de fin est obligatoire.');
            return false;
        }

        // === Vérification des points ===
        if (isNaN(points) || points < 0) {
            alert('Les points doivent être un nombre positif.');
            return false;
        }

        // === Vérification de la redondance du titre ===
        const existingTitres = getExistingDefiTitles();
        const editIdField = form.querySelector('[name="id"]');
        const currentEditId = editIdField ? editIdField.value : null;

        const titreExiste = existingTitres.some(d => {
            if (currentEditId && d.id === currentEditId) return false;
            return d.titre === titre.toLowerCase();
        });
        if (titreExiste) {
            alert('Ce titre de défi existe déjà. Veuillez choisir un titre unique.');
            return false;
        }

        // === Vérification date de début >= aujourd'hui ===
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const dateDebutObj = new Date(dateDebut + 'T00:00:00');
        if (dateDebutObj < today) {
            alert("La date de début doit être aujourd'hui ou dans le futur.");
            return false;
        }

        // === Vérification date de fin > date de début ===
        const dateFinObj = new Date(dateFin + 'T00:00:00');
        if (dateFinObj <= dateDebutObj) {
            alert('La date de fin doit être strictement postérieure à la date de début.');
            return false;
        }

        return true;
    }

    // ===== VALIDATION PARTICIPATIONS =====

    function validateParticipationForm(form) {
        const utilisateur = form.querySelector('[name="utilisateur_id"]').value;
        const defi = form.querySelector('[name="defi_id"]').value;
        const progression = parseInt(form.querySelector('[name="progression"]').value, 10);
        const points = parseInt(form.querySelector('[name="points_gagnes"]').value, 10);
        if (!utilisateur) {
            alert('Veuillez sélectionner un utilisateur.');
            return false;
        }
        if (!defi) {
            alert('Veuillez sélectionner un défi.');
            return false;
        }
        if (isNaN(progression) || progression < 0 || progression > 100) {
            alert('La progression doit être un nombre entre 0 et 100.');
            return false;
        }
        if (isNaN(points) || points < 0) {
            alert('Les points gagnés doivent être un nombre positif.');
            return false;
        }
        return true;
    }

    // ===== SUBMIT LISTENERS =====

    if (addDefiForm) {
        addDefiForm.addEventListener('submit', function (event) {
            if (!validateDefiForm(this)) {
                event.preventDefault();
            }
        });
    }
    if (editDefiForm) {
        editDefiForm.addEventListener('submit', function (event) {
            if (!validateDefiForm(this)) {
                event.preventDefault();
            }
        });
    }
    if (addParticipationForm) {
        addParticipationForm.addEventListener('submit', function (event) {
            if (!validateParticipationForm(this)) {
                event.preventDefault();
            }
        });
    }
    if (editParticipationForm) {
        editParticipationForm.addEventListener('submit', function (event) {
            if (!validateParticipationForm(this)) {
                event.preventDefault();
            }
        });
    }

    // ===== EDIT BUTTONS – PRÉ-REMPLISSAGE =====

    editDefiButtons.forEach(button => {
        button.addEventListener('click', function () {
            const form = editDefiForm;
            form.querySelector('[name="id"]').value = this.dataset.id;
            form.querySelector('[name="titre"]').value = this.dataset.titre || '';
            form.querySelector('[name="type"]').value = this.dataset.type || 'nutrition';
            form.querySelector('[name="points"]').value = this.dataset.points || 0;
            form.querySelector('[name="date_debut"]').value = this.dataset.dateDebut || '';
            form.querySelector('[name="date_fin"]').value = this.dataset.dateFin || '';
            openModal(editDefiModal);
        });
    });

    editParticipationButtons.forEach(button => {
        button.addEventListener('click', function () {
            const form = editParticipationForm;
            form.querySelector('[name="id"]').value = this.dataset.id;
            form.querySelector('[name="utilisateur_id"]').value = this.dataset.utilisateurId || '';
            form.querySelector('[name="defi_id"]').value = this.dataset.defiId || '';
            form.querySelector('[name="progression"]').value = this.dataset.progression || 0;
            form.querySelector('[name="points_gagnes"]').value = this.dataset.points || 0;
            form.querySelector('[name="termine"]').checked = this.dataset.termine === '1';
            openModal(editParticipationModal);
        });
    });

    // ===== OPEN MODAL BUTTONS =====

    if (openGlobalDefiBtn) {
        openGlobalDefiBtn.addEventListener('click', function () {
            openModal(addDefiModal);
        });
    }
    if (quickAddDefi) {
        quickAddDefi.addEventListener('click', function () {
            openModal(addDefiModal);
        });
    }
    if (openAddParticipationBtn) {
        openAddParticipationBtn.addEventListener('click', function () {
            openModal(addParticipationModal);
        });
    }

    // ===== CLOSE MODAL BUTTONS =====

    if (closeDefiModalBtn) {
        closeDefiModalBtn.addEventListener('click', function () {
            closeModal(addDefiModal);
        });
    }
    if (closeEditDefiModalBtn) {
        closeEditDefiModalBtn.addEventListener('click', function () {
            closeModal(editDefiModal);
        });
    }
    if (closeAddParticipationModalBtn) {
        closeAddParticipationModalBtn.addEventListener('click', function () {
            closeModal(addParticipationModal);
        });
    }
    if (closeEditParticipationModalBtn) {
        closeEditParticipationModalBtn.addEventListener('click', function () {
            closeModal(editParticipationModal);
        });
    }

    // ===== SUPPRESSION CONFIRMATION (backoffice) =====

    document.querySelectorAll('.btn-delete-confirm').forEach(link => {
        link.addEventListener('click', function (event) {
            if (!confirm('Voulez-vous vraiment supprimer cet enregistrement ?')) {
                event.preventDefault();
            }
        });
    });

    // ===== FERMER MODALE AU CLIC EN DEHORS =====

    window.addEventListener('click', function (event) {
        if (event.target.classList && event.target.classList.contains('modal')) {
            closeModal(event.target);
        }
    });
})();
