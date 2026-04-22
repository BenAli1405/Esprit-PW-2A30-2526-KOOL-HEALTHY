/* validation.js
 * Contrôle de saisie client pour les formulaires Entraînement et Exercice.
 */

/* Helpers généraux */
function clearErrors(container) {
  container.innerHTML = '';
  container.style.display = 'none';
}

function showErrors(container, errors) {
  if (!errors.length) {
    clearErrors(container);
    return;
  }

  const list = document.createElement('ul');
  list.style.marginLeft = '1rem';
  list.style.paddingLeft = '1.2rem';

  errors.forEach(function (message) {
    const item = document.createElement('li');
    item.textContent = message;
    list.appendChild(item);
  });

  container.innerHTML = '';
  container.className = 'alert';
  container.appendChild(list);
  container.style.display = 'block';
}

function isIntegerString(value) {
  return /^-?\d+$/.test(value);
}

function parseInteger(value) {
  return Number.parseInt(value, 10);
}

function validateDateISO(value) {
  if (!/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test(value)) {
    return false;
  }

  const parts = value.split('-').map(function (part) {
    return Number.parseInt(part, 10);
  });

  const year = parts[0];
  const month = parts[1];
  const day = parts[2];

  const date = new Date(value + 'T00:00:00');
  return (
    date.getFullYear() === year &&
    date.getMonth() + 1 === month &&
    date.getDate() === day
  );
}

function getFieldValue(form, fieldName) {
  const field = form.elements[fieldName];
  if (!field || typeof field.value !== 'string') {
    return '';
  }
  return field.value.trim();
}

/* Validation spécifique à l'entraînement */
function validateEntrainementForm(form) {
  const errors = [];
  const dateValue = getFieldValue(form, 'date');
  const dureeValue = getFieldValue(form, 'duree_minutes');
  const typeValue = getFieldValue(form, 'type_sport');
  const caloriesValue = getFieldValue(form, 'calories_brulees');

  if (!dateValue) {
    errors.push('La date est obligatoire.');
  } else if (!validateDateISO(dateValue)) {
    errors.push('La date doit être au format AAAA-MM-JJ et correspondre à une date réelle.');
  }

  if (!dureeValue) {
    errors.push('La durée en minutes est obligatoire.');
  } else if (!isIntegerString(dureeValue)) {
    errors.push('La durée doit être un nombre entier.');
  } else if (parseInteger(dureeValue) <= 0) {
    errors.push('La durée doit être supérieure à 0.');
  }

  if (!typeValue) {
    errors.push('Le type de sport est obligatoire.');
  }

  if (!caloriesValue) {
    errors.push('Le nombre de calories brûlées est obligatoire.');
  } else if (!isIntegerString(caloriesValue)) {
    errors.push('Les calories brûlées doivent être un nombre entier.');
  } else if (parseInteger(caloriesValue) < 0) {
    errors.push('Les calories brûlées ne peuvent pas être négatives.');
  }

  return errors;
}

/* Validation spécifique à l'exercice */
function validateExerciceForm(form) {
  const errors = [];
  const nomValue = getFieldValue(form, 'nom');
  const seriesValue = getFieldValue(form, 'series');
  const repetitionsValue = getFieldValue(form, 'repetitions');
  const reposValue = getFieldValue(form, 'repos_secondes');
  const ordreValue = getFieldValue(form, 'ordre');

  if (!nomValue) {
    errors.push('Le nom de l’exercice est obligatoire.');
  }

  if (!seriesValue) {
    errors.push('Le nombre de séries est obligatoire.');
  } else if (!isIntegerString(seriesValue)) {
    errors.push('Le nombre de séries doit être un nombre entier.');
  } else if (parseInteger(seriesValue) <= 0) {
    errors.push('Le nombre de séries doit être supérieur à 0.');
  }

  if (!repetitionsValue) {
    errors.push('Le nombre de répétitions est obligatoire.');
  } else if (!isIntegerString(repetitionsValue)) {
    errors.push('Le nombre de répétitions doit être un nombre entier.');
  } else if (parseInteger(repetitionsValue) <= 0) {
    errors.push('Le nombre de répétitions doit être supérieur à 0.');
  }

  if (!reposValue) {
    errors.push('Le repos en secondes est obligatoire.');
  } else if (!isIntegerString(reposValue)) {
    errors.push('Le repos doit être un nombre entier.');
  } else if (parseInteger(reposValue) < 0) {
    errors.push('Le repos ne peut pas être négatif.');
  }

  if (ordreValue) {
    if (!isIntegerString(ordreValue)) {
      errors.push('L’ordre doit être un nombre entier si renseigné.');
    } else if (parseInteger(ordreValue) < 0) {
      errors.push('L’ordre ne peut pas être négatif.');
    }
  }

  return errors;
}

/* Attache un validateur à un formulaire existant */
function attachFormValidation(formId, errorContainerId, validator) {
  const form = document.getElementById(formId);
  const errorContainer = document.getElementById(errorContainerId);

  if (!form || !errorContainer) {
    return;
  }

  clearErrors(errorContainer);

  form.addEventListener('submit', function (event) {
    const errors = validator(form);
    if (errors.length > 0) {
      event.preventDefault();
      showErrors(errorContainer, errors);
      errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
      clearErrors(errorContainer);
    }
  });
}

/* Initialisation lors du chargement de la page */
document.addEventListener('DOMContentLoaded', function () {
  attachFormValidation('entrainement-form', 'entrainement-errors', validateEntrainementForm);
  attachFormValidation('exercice-form', 'exercice-errors', validateExerciceForm);
});
