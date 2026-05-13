# 🔗 Guide Intégration Lien KNN dans Header

## Front Office Header

Pour ajouter le lien KNN à côté de "Recommander IA", ouvrez `views/layout/header.php` et cherchez cette section (ligne ~30) :

```html
<a href="index.php?action=mes_entrainements">Mes séances</a>
<a href="index.php?action=recommander_ia">Recommander IA</a>
<a href="index.php?action=mes_entrainements">Mes exercices</a>
```

Remplacez par :

```html
<a href="index.php?action=mes_entrainements">Mes séances</a>
<a href="index.php?action=recommander_ia">Recommander IA</a>
<a href="index.php?action=recommander_knn">KNN Exercices</a>
<a href="index.php?action=mes_entrainements">Mes exercices</a>
```

---

## Back Office Sidebar

Pour ajouter le lien Features dans le menu admin, cherchez cette section (ligne ~50) :

```html
<a class="nav-item<?= in_array($action, ['admin_regles','admin_creer_regle','admin_modifier_regle']) ? ' active' : '' ?>" href="index.php?action=admin_regles"><i class="fas fa-brain"></i><span>IA Rules</span></a>
```

Ajoutez après :

```html
<a class="nav-item<?= in_array($action, ['admin_features','admin_edit_feature']) ? ' active' : '' ?>" href="index.php?action=admin_features"><i class="fas fa-flask"></i><span>Features KNN</span></a>
```

---

**Résultat** :
- Front : Lien "KNN Exercices" entre "Recommander IA" et "Mes exercices"
- Back : Menu "Features KNN" sous "IA Rules"

**Icônes disponibles** : `fa-flask`, `fa-cogs`, `fa-sliders-h`, `fa-tools`, `fa-cubes`, `fa-network-wired`

Choisir celle qui te plaît le plus ! 🎨
