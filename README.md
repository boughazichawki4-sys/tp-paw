# 📚 Système de Gestion des Présences — tp-paw

Projet complet de gestion des étudiants et des présences avec HTML, CSS, JavaScript et PHP.

## 🎯 Description

Ce projet permet de :
- ✅ Ajouter des étudiants avec validation des données
- ✅ Modifier les informations des étudiants
- ✅ Supprimer des étudiants
- ✅ Gérer les présences et participations (6 sessions)
- ✅ Consulter la liste des étudiants avec tri
- ✅ Générer des rapports de présence avec graphiques
- ✅ Rechercher les étudiants par nom
- ✅ Afficher le statut des étudiants (bon/moyen/mauvais)

## 🚀 Installation et Configuration

### 1. Prérequis
- PHP 7.4+ avec support MySQL/MariaDB
- MySQL/MariaDB server
- Navigateur web moderne
- (Optionnel) Serveur PHP local

### 2. Configuration de la Base de Données

Modifiez le fichier `config.php` avec vos paramètres :

```php
<?php
return [
    'host' => '127.0.0.1',       // Serveur MySQL
    'username' => 'root',         // Utilisateur MySQL
    'password' => '',             // Mot de passe
    'dbname' => 'tp paw',         // Nom de la base
    'charset' => 'utf8mb4',       // Encodage
];
```

### 3. Créer la Base de Données

Exécutez ce script SQL dans phpMyAdmin ou MySQL CLI :

```sql
-- Créer la base de données
CREATE DATABASE IF NOT EXISTS `tp paw` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tp paw`;

-- Créer la table students
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(100) NOT NULL,
  `matricule` VARCHAR(20) UNIQUE NOT NULL,
  `group_id` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_matricule` (`matricule`),
  INDEX `idx_group` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4. Lancer le Serveur

```bash
# Windows
cd "C:\Users\Mi-Computer N°08\Desktop\tp paw - Copie"
php -S localhost:8000

# Ou sur macOS/Linux
cd /path/to/tp\ paw
php -S localhost:8000
```

Puis ouvrez : **http://localhost:8000**

### 5. Accès à phpMyAdmin

Pour gérer la base de données MySQL directement, accédez à :

🔗 **[phpMyAdmin - Base de données tp paw](http://localhost/phpmyadmin5.2.3/index.php?route=/sql&pos=0&db=tp+paw&table=students)**

Cet accès vous permet de :
- Consulter les tables de la base de données
- Exécuter des requêtes SQL
- Modifier les données directement
- Gérer les utilisateurs MySQL

## 📁 Structure du Projet

```
tp paw/
├── index.html                 # Interface principale (HTML/CSS/JS)
├── manage_students.php        # ✨ Gestion complète des étudiants (BD + affichage)
├── script.js                  # Logique client (validation, tri, recherche)
├── style.css                  # Styles responsive et accessibilité
├── config.php                 # Configuration base de données
├── db_connect.php             # Fonction de connexion PDO
├── api_add_student.php        # ✨ API pour ajouter un étudiant (JSON)
├── api_load_students.php      # ✨ API pour charger les étudiants (JSON)
├── add_student.php            # Formulaire d'ajout (alternative)
├── list_students.php          # Liste des étudiants (alternative)
├── update_student.php         # Modification d'un étudiant
├── delete_student.php         # Suppression d'un étudiant
├── test_db.php                # Test de connexion BD
└── README.md                  # Documentation
```

##  Améliorations Apportées

### Sécurité
-  Validation des données côté client et serveur
-  Échappement HTML pour prévenir les XSS
-  Requêtes SQL préparées (PDO) contre les injections
-  Sanitisation des entrées utilisateur
-  Gestion des erreurs avec logging
-  Vérification des doublons (matricule)

### Performance & UX
-  Validation en temps réel avec retour utilisateur
-  Messages d'erreur clairs et localisés
-  Design responsive (mobile, tablette, desktop)
-  Animations fluides et transitions
-  Accessibilité (ARIA labels, focus management)
-  Rapports avec graphiques Chart.js

### Code Quality
-  Patterns PHP modernes (PDO, PSR)
-  Séparation des responsabilités
-  Gestion centralisée des erreurs
-  Utilisation de prepared statements
-  Commentaires et documentation
-  Support UTF-8 complet

##  Fonctionnalités Détaillées

### 1. Formulaire d'Ajout d'Étudiant
- Validation des champs (nom, matricule, groupe)
- Vérification des doublons
- Ajout direct à la base de données MySQL via API PHP
- Messages d'erreur inline
- Affichage immédiat dans le tableau (sans rechargement)
- Notification de succès non-intrusive

### 2. Gestion des Présences
- 6 sessions disponibles
- Checkboxes pour présence/participation
- Calcul automatique des absences
- Calcul automatique des participations
- Messages de statut automatiques

### 3. Rapport de Présence
- Statistiques globales
- Graphique en barres avec Chart.js
- Export via la console JavaScript (dev)

### 4. Recherche et Tri
- Recherche en temps réel par nom
- Tri par absences (croissant)
- Tri par participation (décroissant)
- Indicateurs visuels du tri

### 5. Mise en Évidence des Excellents Étudiants ⭐
- Cliquez sur **"Highlight Excellent Students"** pour identifier les étudiants excellents
- **Critères d'excellence** :
  - ✅ 4 participations ou plus
  - ✅ 0 ou 1 absence maximum
- Les étudiants correspondant à ces critères s'affichent avec :
  - 🟨 **Fond doré/ambré** (mise en évidence visuelle)
  - 📊 Animation de clignotement pour attirer l'attention
  - ⭐ Message de confirmation avec le nombre d'excellents étudiants
  
### 6. Codage Couleur
- 🟢 **Vert** : < 3 absences (Bon)
- 🟡 **Jaune** : 3-4 absences (Moyen)
- 🔴 **Rouge** : > 4 absences (Mauvais)
- 🟨 **Doré/Ambré** : Étudiant excellent (4+ participations + 0-1 absence)

##  Validation des Données

### ID Étudiant
- Min: 8 chiffres
- Format: Numérique uniquement

### Nom / Prénom
- Min: 2 caractères
- Max: 100 caractères
- Caractères: Lettres, espaces, tirets

### Email
- Format: user@domain.com
- Validation regex stricte

### Matricule
- Min: 8 chiffres
- Unique en base de données

##  Troubleshooting

### Erreur de connexion à la BD
1. Vérifiez que MySQL/MariaDB est démarré
2. Vérifiez les identifiants dans `config.php`
3. Consultez `db_errors.log` pour plus de détails

### Les étudiants ne s'affichent pas
1. Vérifiez que la table `students` existe
2. Exécutez le script SQL de création
3. Vérifiez les permissions MySQL

### Problème de validation
1. Consultez la console JavaScript (F12)
2. Vérifiez les messages d'erreur rouge
3. Respectez le format des données

## 📝 Exemple d'Utilisation

### Option 1 : Interface Unifiée (Recommandée) 🌟
1. Accédez à **http://localhost:8000/manage_students.php**
2. Remplissez le formulaire à gauche :
   - Nom: Boughazi Chawki
   - Matricule: 20233163
   - Groupe: A1
3. Cliquez **"Ajouter"**
4. ✅ L'étudiant apparaît instantanément dans le tableau à droite (depuis MySQL)
5. Cliquez **"Modifier"** ou **"Supprimer"** pour gérer les étudiants

### Option 2 : Interface Principale (Présences) 🎓
1. Accédez à **http://localhost:8000**
2. Remplissez le formulaire **"Add Student"** avec :
   - Student ID: 20233163
   - Last Name: Boughazi
   - First Name: Chawki
   - Email: chawki@example.com
3. Cliquez **"Add Student"**
4. ✅ L'étudiant est immédiatement :
   - **Ajouté au tableau des présences** (index.html) avec animation
   - **Sauvegardé en MySQL** automatiquement via API
   - **Visible dans manage_students.php** automatiquement (sync en temps réel ⚡)
5. Gérez les présences avec les checkboxes
6. Consultez les rapports en cliquant **"Show Report"**
7. Ouvrez **manage_students.php** dans un nouvel onglet - l'étudiant y apparaît automatiquement!
8. Cliquez **"📋 Gérer les étudiants"** pour voir la liste complète avec sync en direct 🔄

### Option 3 : Pages Séparées (Alternative)
- **add_student.php** : Ajouter uniquement
- **list_students.php** : Voir et modifier/supprimer

##  Sécurité

Ce projet utilise :
- PDO avec prepared statements
- htmlspecialchars() pour l'échappement
- filter_var() pour la sanitisation
- Gestion centralisée des erreurs
- Logging des erreurs en fichier
- Validation stricte côté client et serveur

## ✨ Architecture et Flux de Données

### Flux Complet d'Ajout d'Étudiant (SYNCHRONISÉ EN TEMPS RÉEL) ⚡

```
📝 INTERFACE PRINCIPALE (index.html)
      ↓
   Utilisateur remplit le formulaire "Add Student"
      ↓
   JavaScript valide les données (côté client)
      ↓
   Envoi AJAX vers api_add_student.php
      ↓
💾 API PHP → MySQL (Insertion sécurisée + Sauvegarde en BD)
      ↓
📊 Retour JSON au JavaScript
      ↓
🎯 TROIS ACTIONS QUASI-INSTANTANÉES :
  1. ✅ Ajout au tableau des présences (index.html) - Instantané
  2. ✅ Sauvegarde confirmée en MySQL - < 100ms
  3. ✅ Notification localStorage pour synchronisation en temps réel
      ↓
🔄 SYNCHRONISATION AUTOMATIQUE :
   manage_students.php détecte le changement via :
   - Polling API toutes les 2 secondes
   - Événements localStorage (inter-onglets)
      ↓
✨ Message de succès avec indicateur 🟢 "En sync"
```

### Intégration Complète avec Synchronisation

| Page | Action | Temps de Sync | Base de Données |
|------|--------|---|-----------------|
| **index.html** | Ajoute étudiant + gère présences | Instantané | MySQL ↔️ |
| **manage_students.php** | Gère liste + sync auto | < 2 secondes | MySQL ↔️ |
| **MySQL** | Stockage permanent | Immédiat | Source unique |

### Indicateurs de Synchronisation

- **🔄 En sync** (Vert) : Synchronisé avec la BD
- **⏳ Syncing...** (Orange) : Vérification des données
- **🔄 Actualisation...** (Orange) : Rechargement en cours
- **⚠️ Erreur sync** (Rouge) : Problème de connexion

### Caractéristiques Avancées

- ✅ **Synchronisation en temps réel** : Détection automatique en < 2 secondes
- ✅ **Polling intelligent** : Vérif toutes les 2 secondes avec localStorage
- ✅ **Données persistantes** : Sauvegardées en MySQL
- ✅ **UX fluide** : Pas de rechargement sauf si nouvelles données
- ✅ **Sécurité** : Prepared statements + sanitisation
- ✅ **Validation** : Côté client ET serveur
- ✅ **API REST** : Séparation frontend/backend
- ✅ **Animations** : Indicateurs visuels clairs

## 🔗 Liens Utiles

- **GitHub du projet** : [https://github.com/boughazichawki4-sys/tp.git](https://github.com/boughazichawki4-sys/tp.git)
- **Accès direct à la table MySQL** : [phpMyAdmin - Table students](http://localhost/phpmyadmin5.2.3/index.php?route=/sql&db=tp+paw&table=students&pos=0)
