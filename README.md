# Présentation
L'application ENI Sortir.com est un projet de groupe développé dans le cadre de la formation DWWM de l'ENI
L'objectif est de développer une application pour un bureau d'éléves avec de proposer, gérer et s'inscrire à des sorties ou des événements

Ce Readme décrit les actions à suivre pour exécuter l'application de cet environnement.

# Environnement techniques et pré-requis
L'application a été développé avec Symfony 5.4, PHP 7.4 et Mysql 8.
Le serveur web utilisé est celui de Symfony via la commance `symfony serve`.
La gestion de courriers est assurée par Papercut (https://github.com/ChangemakerStudios/Papercut-SMTP)

## Prérequis
- SGBD : MySQL
- BDD : sortir (créée à l'installation si besoin)
- WampServer pour PHP et MySQL

## Installation
1. Télécharger et installer le code sur votre machine ou cloner le via `git clone https://gitlab.com/Gregoire-Dubois/sortir.com.git`
2. Positionnez vous dans ce répertoire dans un terminal (préférez Cmder https://cmder.app/)
3. Installez les dépendances du projet `composer install`
4. Créer la BDD `symfony console doctrine:database:create`
5. Créez le schéma de la BDD `symfony console doctrine:schema:update --force`
6. Ajouter des données de test `php bin/console doctrine:fixtures:load`
7. Accédez à l'application http://localhost:8000/
8. Connectez vous avec l'utilisateur par défaut :
   id: PseudoTest
   password : azerty 
