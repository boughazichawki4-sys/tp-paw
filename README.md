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

## 📁 Structure du Projet

```
tp paw/
├── index.html                 # Interface principale (HTML/CSS/JS)
├── script.js                  # Logique client (validation, tri, recherche)
├── style.css                  # Styles responsive et accessibilité
├── config.php                 # Configuration base de données
├── db_connect.php             # Fonction de connexion PDO
├── add_student.php            # Formulaire d'ajout d'étudiant
├── list_students.php          # Liste des étudiants avec tri
├── update_student.php         # Modification d'un étudiant
├── delete_student.php         # Suppression d'un étudiant
├── test_db.php                # Test de connexion BD
└── README.md                  # Documentation
```

## ✨ Améliorations Apportées

### Sécurité
- ✅ Validation des données côté client et serveur
- ✅ Échappement HTML pour prévenir les XSS
- ✅ Requêtes SQL préparées (PDO) contre les injections
- ✅ Sanitisation des entrées utilisateur
- ✅ Gestion des erreurs avec logging
- ✅ Vérification des doublons (matricule)

### Performance & UX
- ✅ Validation en temps réel avec retour utilisateur
- ✅ Messages d'erreur clairs et localisés
- ✅ Design responsive (mobile, tablette, desktop)
- ✅ Animations fluides et transitions
- ✅ Accessibilité (ARIA labels, focus management)
- ✅ Rapports avec graphiques Chart.js

### Code Quality
- ✅ Patterns PHP modernes (PDO, PSR)
- ✅ Séparation des responsabilités
- ✅ Gestion centralisée des erreurs
- ✅ Utilisation de prepared statements
- ✅ Commentaires et documentation
- ✅ Support UTF-8 complet

## 🎨 Fonctionnalités Détaillées

### 1. Formulaire d'Ajout d'Étudiant
- Validation des champs (ID, nom, email)
- Vérification des doublons
- Messages d'erreur inline
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

### 5. Codage Couleur
- 🟢 **Vert** : < 3 absences (Bon)
- 🟡 **Jaune** : 3-4 absences (Moyen)
- 🔴 **Rouge** : > 4 absences (Mauvais)

## 📊 Validation des Données

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

## 🐛 Troubleshooting

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

1. Accédez à http://localhost:8000
2. Remplissez le formulaire avec :
   - ID: 20233163
   - Nom: Boughazi Chawki
   - Email: chawki@example.com
3. Cliquez "Add Student"
4. Gérez les présences avec les checkboxes
5. Consultez les rapports en cliquant "Show Report"

## 🔐 Sécurité

Ce projet utilise :
- PDO avec prepared statements
- htmlspecialchars() pour l'échappement
- filter_var() pour la sanitisation
- Gestion centralisée des erreurs
- Logging des erreurs en fichier
- Validation stricte côté client et serveur

## 📜 Licence

À définir selon vos besoins.

## 👨‍💻 Auteur

Projet pédagogique de gestion des présences.

---

**Version**: 2.0 (Améliorations sécurité et UX)  
**Dernière mise à jour**: Novembre 2025
