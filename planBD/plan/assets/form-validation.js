// ligne 1 : ajoute le paramètre actionOverride
function validatePlanForm(form, actionOverride) {
    const action = actionOverride || (document.getElementById('formActionInput') ? document.getElementById('formActionInput').value : 'create');
    const id = form.querySelector('[name="id"]')?.value.trim() || '';

    const nom = form.querySelector('[name="nom"]')?.value.trim() || '';
    const objectif = form.querySelector('[name="objectif"]')?.value || '';
    const utilisateurId = form.querySelector('[name="utilisateur_id"]')?.value.trim() || '';
    const duree = form.querySelector('[name="duree"]')?.value.trim() || '';
    const preference = form.querySelector('[name="preference"]')?.value.trim() || '';
    const allergies = form.querySelector('[name="allergies"]')?.value.trim() || '';

    const errors = [];

    // Nom : minimum 3 caractères (couvre aussi le champ vide) et max 200
    if (!nom || nom.length < 3 || nom.length > 200) {
        errors.push('Le nom du plan doit contenir entre 3 et 200 caractères.');
    }

    // Autres champs : tous les champs remplis
    if (!objectif) errors.push('Veuillez choisir un objectif.');
    if (!utilisateurId) errors.push('L\'identifiant utilisateur est obligatoire.');
    if (!duree) errors.push('La durée est obligatoire.');
    if (!preference) errors.push('La préférence alimentaire est obligatoire.');
    if (!allergies) errors.push('Le champ allergies est obligatoire.');

    // Durée minimum 7 jours
    if (duree) {
        if (!/^[0-9]+$/.test(duree)) {
            errors.push('La durée doit être un nombre valide.');
        } else if (Number(duree) < 7) {
            errors.push('La durée doit être au minimum de 7 jours.');
        }
    }

    // ID utilisateur entier positif
    if (utilisateurId && (!/^[0-9]+$/.test(utilisateurId) || Number(utilisateurId) < 1)) {
        errors.push('L\'identifiant utilisateur doit être un nombre entier positif.');
    }

    // Unicité des ID plan et ID utilisateur
    if (typeof plansData !== 'undefined') {
        const isUpdate = (action === 'update' && id !== '');
        
        // 1. Unicité de l'utilisateur (un utilisateur ne peut pas avoir deux plans)
        if (utilisateurId) {
            const userExists = plansData.some((p) => {
                return String(p.utilisateur_id) === String(utilisateurId) && (!isUpdate || String(p.id) !== String(id));
            });
            if (userExists) {
                errors.push('Cet identifiant utilisateur possède déjà un plan (doit être unique).');
            }
        }
        
        // 2. Unicité de l'ID de plan (bloque l'ajout si l'ID plan saisi existe déjà en mode création)
        if (id && !isUpdate) {
            const planExists = plansData.some(p => String(p.id) === String(id));
            if (planExists) {
                errors.push('Cet identifiant de plan est déjà utilisé (doit être unique).');
            }
        }
    }

    const errorContainer = document.getElementById('formErrors');
    errorContainer.innerHTML = '';

    if (errors.length > 0) {
        const list = document.createElement('ul');
        errors.forEach((message) => {
            const item = document.createElement('li');
            item.textContent = message;
            list.appendChild(item);
        });
        errorContainer.appendChild(list);
        errorContainer.style.display = 'block';
        return false;
    }

    errorContainer.style.display = 'none';
    return true;
}

function fillPlanForm(plan) {
    const form = document.getElementById('planForm');
    form.querySelector('[name="id"]').value = plan.id;
    form.querySelector('[name="nom"]').value = plan.nom;
    form.querySelector('[name="objectif"]').value = plan.objectif;
    form.querySelector('[name="utilisateur_id"]').value = plan.utilisateur_id;
    form.querySelector('[name="duree"]').value = plan.duree;
    form.querySelector('[name="preference"]').value = plan.preference;
    form.querySelector('[name="allergies"]').value = plan.allergies;
}

function resetPlanForm() {
    const form = document.getElementById('planForm');
    form.reset();
    form.querySelector('[name="id"]').value = '';
    form.querySelector('[name="action"]').value = 'create';
    document.getElementById('formErrors').style.display = 'none';
}

function confirmDelete() {
    return confirm('Voulez-vous vraiment supprimer ce plan ?');
}
