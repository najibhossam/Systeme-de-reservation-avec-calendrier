# Projet de Réservation en Ligne

Ce projet est une application web de réservation en ligne développée en PHP (procédural) avec une interface basée sur Bootstrap. Le système permet aux utilisateurs de s'inscrire, de se connecter, de gérer leur profil, de prendre et annuler des rendez-vous via un calendrier interactif (FullCalendar) et d'accéder à des fonctionnalités administratives.

## Table des Matières

- [Fonctionnalités](#fonctionnalités)
- [Architecture et Technologies](#architecture-et-technologies)
- [Diagramme Use Case](#diagramme-use-case)
- [Diagramme de Séquence](#diagramme-de-séquence)
- [Schéma de Base de Données](#schéma-de-base-de-données)

## Fonctionnalités

### Pour les Utilisateurs
- **Création de compte** : Inscription via un formulaire demandant nom, prénom, date de naissance, adresse postale (avec Google Places Autocomplete), téléphone, email (unique) et mot de passe.  
  - L'utilisateur doit avoir au moins 18 ans.
  - Un email d'activation est envoyé pour activer le compte.
- **Connexion** : Authentification via email et mot de passe (vérification du hash et de l'état d'activation).
- **Modification du profil** : Possibilité de mettre à jour ses informations personnelles, avec vérification de l'unicité de l'email.
- **Prise de rendez-vous** : Sélection d'un créneau via un calendrier interactif (FullCalendar) affichant les créneaux disponibles et marquant les créneaux occupés.
  - Créneaux disponibles uniquement toutes les 30 minutes entre 8h et minuit.
  - Envoi d'un email de confirmation de réservation.
- **Annulation de rendez-vous** : Annulation d'un rendez-vous existant, avec vérification que le rendez-vous appartient bien à l'utilisateur, et envoi d'un email de confirmation.
- **Suppression de compte** : Suppression du compte et de toutes les données associées.

### Pour l'Administrateur
- **Connexion** : Accès aux fonctionnalités d'administration via un compte avec `role = admin`.
- **Gestion des rendez-vous** :
  - Liste de tous les rendez-vous.
  - Modification et annulation de rendez-vous avec notification par email aux utilisateurs concernés.
- **Gestion des comptes** (optionnel) : Possibilité de supprimer ou désactiver des comptes utilisateur.

### Fonctionnalités Avancées
- **Mot de passe oublié** : 
  - Fonctionnalité "Mot de passe oublié" avec envoi d’un lien de réinitialisation par email.
  - Réinitialisation du mot de passe via un formulaire dédié.
- **Google Places Autocomplete** :
  - Intégration de l’API Google Places pour faciliter la saisie de l’adresse lors de l'inscription et la modification du profil.
- **Interface Utilisateur Moderne** :
  - Interface responsive basée sur Bootstrap.
  - Barre de navigation personnalisée avec indication de la page active (soulignement).
  - Calendrier interactif FullCalendar avec indication claire des créneaux occupés (fond rouge, texte blanc, label "CRÉNEAU OCCUPÉ").

## Architecture et Technologies

- **Frontend** :
  - HTML, CSS, JavaScript
  - Bootstrap 5 pour la mise en page et le design responsive
  - FullCalendar pour la gestion des rendez-vous

- **Backend** :
  - PHP procédural
  - PHPMailer pour l'envoi d'emails (activation, confirmation de RDV, réinitialisation de mot de passe)
  - Base de données MySQL avec PDO

## Diagramme Use Case

![Image](https://github.com/user-attachments/assets/63183e2f-3ef2-4f84-bb7e-1c7e21eb2898)

## Diagramme de Séquence

![Image](https://github.com/user-attachments/assets/3d6de0b8-2a70-4839-bae5-eb6eeec18ee8)

## Schéma de Base de Données

![Image](https://github.com/user-attachments/assets/5701e61d-fdba-4cd2-a02f-1f5eee742b4f)