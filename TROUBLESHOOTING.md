# 🔧 Troubleshooting Guide - Kool Healthy CRUD Issue

## Problem
- No sample data showing when you first load backoffice
- Clicking "Sauvegarder" redirects to empty frontoffice
- Nothing is saved to database

## Root Causes & Solutions

### **Step 1: Run Diagnostics** ✅
Go to this page to identify the exact issue:
```
http://localhost/all/diagnostic.php
```

This will tell you:
- ✓ PHP version and extensions
- ✓ Database connection status
- ✓ Which tables exist
- ✓ How many records are in each table

---

### **Step 2: Create Database** (Most Common Issue)

If diagnostics shows "Database 'web' does NOT exist":

**Option A: phpMyAdmin (Easiest)**
1. Open `http://localhost/phpmyadmin`
2. Click "New" or "Create database" (left panel)
3. Name: `web`
4. Click "Create"
5. Now click on `web` database
6. Click "Import" tab
7. Choose file: `c:\xampp\htdocs\all\kool_healthy.sql`
8. Click "Import"

**Option B: SQL Query**
1. Open `http://localhost/phpmyadmin`
2. Click "SQL" tab
3. Copy-paste entire content of `kool_healthy.sql` file
4. Click "Go" or "Execute"

**Option C: Command Line**
```bash
mysql -u root -p < c:\xampp\htdocs\all\kool_healthy.sql
```
(Leave password blank if using default XAMPP)

---

### **Step 3: Verify Tables Exist**
After importing, check diagnostics again. Should show:
- ✓ Table 'ingredients' exists - 10 records
- ✓ Table 'recettes' exists - 8 records  
- ✓ Table 'recette_ingredient' exists
- ✓ Table 'avis' exists - 7 records

---

### **Step 4: Check Browser Console**

If still not working:

1. Open Backoffice: `http://localhost/all/INDEX.php?view=backoffice`
2. Press `F12` (Developer Tools)
3. Go to **Console** tab
4. Look for error messages
5. Share the error with me

Common errors:
- `"Erreur serveur: Réponse invalide"` = Database connection failed
- `"Failed to parse recipes JSON"` = Database query returned error

---

### **Step 5: Test API Endpoints**

Manually test if data is coming from database:

- **Get Recipes**: `http://localhost/all/INDEX.php?action=getAllRecipes`
- **Get Ingredients**: `http://localhost/all/INDEX.php?action=getAllIngredients`

Should return JSON like:
```json
[
  {"id": 1, "titre": "Buddha Bowl...", "temps_preparation": 25, ...},
  {"id": 2, "titre": "Soupe de lentilles...", ...}
]
```

If you see `Connection Error` or error messages → Database issue
If you see `[]` (empty) → Database exists but no data

---

### **Step 6: Test Form Submission**

After data is showing:

1. Go to Backoffice
2. Click **Recettes** tab
3. Click **"Ajouter une recette"** button
4. Fill form:
   - Titre: "Test Recipe"  
   - Instructions: "Test"
   - Temps: 30
   - **Add at least 1 ingredient** (required!)
5. Click "Sauvegarder"
6. Watch for toast notification (green message)

If redirects to frontoffice instead:
- Open F12 Console again
- Look for error messages
- They will help identify the problem

---

### **Step 7: Common Issues Checklist**

| Issue | Solution |
|-------|----------|
| No data showing | Run diagnostics.php - check if database imported |
| "Database not exist" error | Import kool_healthy.sql file |
| 0 records in tables | Check import was successful |
| Form redirects page | Check F12 Console for error message |
| "Erreur réponse invalide" | Database query error - check error log |
| Toast shows "Erreur: ... " | Read the error message, usually describes the issue |

---

### **Step 8: Check Logs**

If you need more details on server-side errors:

Look at: `c:\xampp\php\logs\error.log`

Or in phpMyAdmin → Variables → `error_log`

---

## ✅ When Everything Works

1. **Backoffice loads** with sample data visible (8 recipes, 10 ingredients)
2. **Add Recipe** button opens modal → fill form → save → success toast → data appears
3. **Add Ingredient** button opens modal → fill form → save → success toast → data appears
4. **Edit** works - click pencil icon, modify, save
5. **Delete** works - click trash icon, confirm, deleted
6. **Frontoffice** shows all recipes from database
7. **Filters** work (difficulty, eco-score, time)

---

## Need Help?

If still having issues after these steps:

1. Go to: `http://localhost/all/diagnostic.php`
2. Copy all the output
3. Share with me along with any F12 Console errors
4. I'll help diagnose the exact issue

Good luck! 🚀
