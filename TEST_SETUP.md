# 🧪 Guide de Test et Validation du Projet

## ✅ Checklist de Configuration

### 1. Configuration de la Base de Données

```bash
# Ouvrir phpMyAdmin ou MySQL CLI et exécuter :

CREATE DATABASE IF NOT EXISTS `tp paw` CHARACTER SET utf8mb4;
USE `tp paw`;

CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(100) NOT NULL,
  `matricule` VARCHAR(20) UNIQUE NOT NULL,
  `group_id` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_matricule` (`matricule`),
  INDEX `idx_group` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Vérifier config.php

```php
<?php
return [
    'host' => '127.0.0.1',      // ✅ Correctement configuré
    'username' => 'root',        // ✅ Utilisateur MySQL
    'password' => '',            // ✅ Mot de passe (vide si non défini)
    'dbname' => 'tp paw',        // ✅ Nom de la BD
    'charset' => 'utf8mb4',
];
```

### 3. Tests Fonctionnels

#### Test 1 : Connexion BD
```bash
php test_db.php
# Résultat attendu : "✅ Connexion réussie"
```

#### Test 2 : Ajouter un étudiant
1. Allez à http://localhost:8000/add_student.php
2. Remplissez le formulaire :
   - Nom: Jean Dupont
   - Matricule: 20233163
   - Groupe: A1
3. Cliquez "Ajouter l'étudiant"
4. Résultat attendu : Message de succès + redirection

#### Test 3 : Lister les étudiants
1. Allez à http://localhost:8000/list_students.php
2. Vérifiez que votre étudiant apparaît

#### Test 4 : Interface Principale (Présences)
1. Allez à http://localhost:8000
2. Le tableau doit afficher l'étudiant ajouté
3. Testez les checkboxes
4. Testez la recherche
5. Testez le tri
6. Cliquez "Show Report"

### 4. Tests de Validation

#### Validation Côté Client

| Champ | Test | Résultat |
|-------|------|----------|
| ID Vide | Laisser vide | ❌ Erreur: "Required" |
| ID Court | "123" | ❌ Erreur: "8 numbers" |
| Nom Court | "J" | ❌ Erreur: "2+ chars" |
| Email Invalide | "invalid" | ❌ Erreur: "Valid email" |
| Tous Valides | Remplir correctement | ✅ Succès |

#### Validation Côté Serveur

1. Matricule Dupliqué
   - Créer 2 étudiants avec même matricule
   - Résultat: ❌ Erreur "matricule existe déjà"

2. Champs Trop Longs
   - Essayer de soumettre (via DevTools)
   - Résultat: ✅ Base de données le refuse

### 5. Tests de Sécurité

#### XSS Protection
```
Tentative : <script>alert('XSS')</script>
Résultat : 🔒 Échappé en HTML entities
```

#### SQL Injection Protection
```
Tentative matricule : 20233163' OR '1'='1
Résultat : 🔒 Traité comme chaîne normale (PDO prepared)
```

### 6. Tests de Responsivité

| Écran | Width | Test |
|-------|-------|------|
| Mobile | 320px | Tableau scrollable, responsive |
| Tablette | 768px | Boutons flexibles |
| Desktop | 1920px | Mise en page optimale |

## 🔍 Points Clés à Vérifier

### Visuel
- [ ] Gradient de fond visible (bleu-violet)
- [ ] Tableau avec couleurs de codage (vert/jaune/rouge)
- [ ] Formulaires avec bordures de focus bleues
- [ ] Messages de succès avec fond vert
- [ ] Messages d'erreur avec fond rouge

### Fonctionnalité
- [ ] Ajout d'étudiant sans recharger la page
- [ ] Recherche en temps réel
- [ ] Tri des étudiants
- [ ] Rapport avec graphique en barres
- [ ] Validation en temps réel
- [ ] Stockage en base de données

### Sécurité
- [ ] Aucune alerte XSS en console
- [ ] Aucune erreur SQL en logs
- [ ] Doublons refusés
- [ ] Validation stricte

## 📊 Données de Test Recommandées

```
Étudiant 1 :
- Nom: Ahmed Ben Ali
- Matricule: 20231501
- Groupe: L2-G1
- Présences: S1✓ S2✓ S3✗ S4✓ S5✓ S6✓
- Participations: S1✓ S2✗ S3✓ S4✓ S5✗ S6✓

Étudiant 2 :
- Nom: Fatima Zahra
- Matricule: 20231502
- Groupe: L2-G1
- Présences: S1✓ S2✓ S3✓ S4✗ S5✗ S6✗
- Participations: S1✗ S2✗ S3✗ S4✓ S5✓ S6✗

Étudiant 3 :
- Nom: Mohamed Samir
- Matricule: 20231503
- Groupe: L2-G2
- Présences: S1✗ S2✗ S3✗ S4✗ S5✓ S6✓
- Participations: S1✓ S2✓ S3✓ S4✓ S5✓ S6✓
```

## 🐛 Si Vous Rencontrez des Problèmes

### Erreur: "Cannot prepare statement"
→ Vérifiez la syntaxe SQL et les permissions MySQL

### Erreur: "SQLSTATE[HY000]"
→ MySQL n'est pas démarré ou config incorrecte

### Validation échoue toujours
→ Ouvrez DevTools (F12) et consultez la console

### Rien ne s'affiche
→ Vérifiez http://localhost:8000 (pas 8001)
→ Vérifiez que le serveur PHP est lancé

## 📞 Commandes Utiles

```bash
# Vérifier version PHP
php -v

# Lancer le serveur
php -S localhost:8000

# Tester la BD (si test_db.php existe)
php test_db.php

# Voir les logs d'erreur
tail -f db_errors.log

# En Windows (PowerShell)
Get-Content db_errors.log -Tail 20
```

## ✨ Après les Tests

Si tout fonctionne :
1. Supprimez les données de test
2. Faites une sauvegarde de la BD
3. Documentez votre configuration
4. Partagez avec votre équipe

---

**Bonne chance ! 🚀**
