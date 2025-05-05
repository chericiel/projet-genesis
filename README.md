Projet Genesis
Projet Genesis est un MVP de téléconsultation en ligne réalisé dans le cadre d’un projet de fin de cursus. L’objectif est de démontrer la mise en œuvre des compétences en développement web full-stack.
 Le projet est structuré en deux parties :
Backend : Développement de l’API RESTful avec Laravel


Frontend : Développement de l’interface utilisateur avec React, en communication avec l’API via Axios



Table des matières
Présentation du Projet


Fonctionnalités Principales


Architecture et Tech Stack


Plan de Versionning et Organisation


Plan de Travail Journalier pour Trello


Semaine Backend


Semaine Frontend


Installation et Lancement


Documentation Complémentaire



Présentation du Projet
Le MVP Genesis permet de gérer un système de téléconsultation où différents rôles (patients, médecins, administrateur) interagissent pour :
Gestion des utilisateurs (authentification sécurisée, gestion des profils, 2FA, etc.)


Prise de rendez-vous (consultation de l’agenda du médecin, planification, annulation)


Consultation médicale (enregistrement des diagnostics et comptes rendus)


Gestion des paiements (enregistrement et visualisation des transactions)


Dossier médical (stockage et consultation des informations de santé)


Ce projet est pensé comme une démonstration fonctionnelle et technique, avec une application backend réalisée en Laravel et une interface frontend en React.

Fonctionnalités Principales
Authentification & Sécurité : Inscription, connexion, 2FA, gestion des tokens.


Gestion des Profils : Table centralisée pour les utilisateurs et tables spécifiques pour patients, médecins et administrateurs.


Rendez-vous : Prise et gestion des rendez-vous entre patients et médecins.


Agenda Médecin : Consultation des disponibilités des médecins.


Consultations : Saisie des diagnostics et notes de consultation.


Paiements : Gestion des transactions avec plusieurs méthodes de paiement.


Dossier Médical : Stockage des informations de santé du patient.



Architecture et Tech Stack
Backend : Laravel avec API RESTful


Base de données MySQL/MariaDB (schéma évolutif avec tables pour utilisateurs, rendez-vous, dossier médical, etc.)


Frontend : React


Communication avec l'API via Axios


Versionning et Déploiement :


Git pour le suivi des versions (hébergé sur GitHub/GitLab)


Organisation via Trello pour la gestion de projet



Plan de Versionning et Organisation
Le versionning suit les étapes du projet sous forme de commit réguliers correspondant aux sprints et aux tâches définies dans Trello.
 Les phases clés sont :
V0.1 : Mise en place de l’environnement, configuration initiale, création du schéma de BDD et des premières migrations.


V0.2 : Fonctionnalités d’authentification, gestion des profils et premières validations API.


V0.3 : Implémentation de la gestion des rendez-vous, agenda, consultation et paiement.


V0.4 : Intégration frontend complète avec test des flux utilisateur.


V1.0 : Dernières optimisations, correctifs et préparation de la soutenance.


Les commits sont réalisés à chaque étape majeure de validation, en suivant le plan de travail journalier.

Plan de Travail Journalier pour Trello
Pour faciliter le suivi du projet, un tableau Trello est utilisé avec quatre colonnes :
Backlog : Tâches identifiées et planifiées sur le long terme.


À faire : Tâches prévues pour la journée ou le sprint.


En cours : Tâches sur lesquelles tu travailles actuellement.


Terminé : Tâches finalisées et validées.


Chaque carte inclut une description, des checklists détaillées et une date limite d’échéance.

Semaine Backend
Objectif : Développer l’API RESTful avec Laravel (authentification, gestion utilisateurs, rendez-vous, agenda, consultation, paiement, dossier médical).
Exemple de Cartes Trello pour un Jour Backend
Carte : Configuration de l’environnement et schéma de BDD
Checklist :


Installer Laravel et initialiser le projet.


Configurer le fichier .env (connexion DB, variables d’environnement).


Créer le dépôt Git et initialiser le versionning.


Définir le schéma de la base de données (diagramme et migrations initiales).


Carte : Implémentation de l’authentification
Checklist :


Créer les migrations pour users, roles et connections.


Développer les endpoints d’inscription et connexion.


Implémenter le 2FA (simulation ou intégration simple).


Tester avec Postman et écrire les tests unitaires.


Carte : Gestion des rendez-vous et agenda
Checklist :


Créer les migrations pour rendez_vous et agenda_medecin.


Implémenter l’API pour la création, modification, et suppression des rendez-vous.


Développer les endpoints pour récupérer l’agenda d’un médecin.


Tester les interactions entre le patient et le médecin.


Carte : Gestion des consultations et paiements
Checklist :


Créer la migration pour consultation.


Implémenter l’API de passage d’un rendez-vous planifié en consultation.


Créer et tester les endpoints pour la gestion des paiements (paiement).


Valider les flux métiers et la sécurité (middleware, policies).


Carte : Finalisation et tests d’intégration
Checklist :


Effectuer les tests d’intégration avec Postman.


Corriger les bugs et améliorer les validations.


Mettre à jour la documentation de l’API (Swagger ou Postman collection).


Commit final pour la semaine backend.



Semaine Frontend
Objectif : Développer l’interface utilisateur en React, intégrer Axios pour consommer l’API, tester les flux complets.
Exemple de Cartes Trello pour un Jour Frontend
Carte : Mise en place du projet React
Checklist :


Initialiser le projet avec Create React App (ou Vite).


Installer les dépendances (Axios, React Router, Redux ou Context API).


Organiser la structure des dossiers (components, pages, services).


Configurer les appels API et tester la connexion avec le backend.


Carte : Pages d’authentification et gestion de profil
Checklist :


Développer la page de connexion avec validations.


Créer le formulaire d’inscription (si nécessaire).


Gérer la 2FA dans le flux de connexion.


Implémenter la page de profil utilisateur pour consultation et édition des informations.


Carte : Interface pour prise de rendez-vous
Checklist :


Créer le formulaire de réservation (sélection du médecin, date, heure).


Ajouter les filtres (spécialité, langue) dans l’UI.


Implémenter la liste et la gestion des rendez-vous (édition/annulation).


Vérifier le bon fonctionnement avec les appels Axios.


Carte : Interface pour consultation et dossier médical
Checklist :


Développer la page de consultation (affichage du diagnostic, compte-rendu).


Créer la page de consultation du dossier médical (affichage, téléchargement de documents).


Tester l’intégration des données depuis l’API.


Carte : Intégration complète et responsive design
Checklist :


Gérer l’état global de l’application (Redux ou Context API).


Effectuer des tests d’intégration frontend-backend.


Adapter l’interface pour mobile et desktop.


Finaliser la documentation technique (captures d’écran, flux utilisateur).


Commit final pour la semaine frontend.



Installation et Lancement
Cloner le dépôt :

 bash
Copier le code
git clone https://github.com/toncompte/ProjetGenesis.git
cd ProjetGenesis


Backend (Laravel) :


Installer les dépendances avec Composer :

 bash
Copier le code
composer install


Configurer le fichier .env et lancer les migrations :

 bash
Copier le code
php artisan migrate


Lancer le serveur local :

 bash
Copier le code
php artisan serve


Frontend (React) :


Installer les dépendances avec npm ou yarn :

 bash
Copier le code
npm install


Lancer le serveur de développement :

 bash
Copier le code
npm start



Documentation Complémentaire
Schéma de la base de données : Voir le diagramme sur dbdiagram.io (fichier DBML inclus dans le dépôt).


API Documentation : Une collection Postman et/ou une documentation Swagger est disponible dans le dossier /docs/api.


Plan de travail Trello : Un lien vers le tableau Trello est indiqué dans la section Plan de Versionning et Organisation.



Ce README fournit une vue d’ensemble structuré

