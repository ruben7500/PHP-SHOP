# README - Boutique de Montres de Luxe

## Présentation du Projet

Ce projet est une boutique en ligne complète spécialisée dans la vente de montres de luxe. Développée en PHP, elle intègre une interface utilisateur élégante construite avec Tailwind CSS et offre toutes les fonctionnalités essentielles d'un site e-commerce.

## Fonctionnalités

### Interface Client
- Catalogue de produits avec filtrage et recherche
- Pages de détail des produits
- Système de panier d'achat complet
- Processus de commande sécurisé
- Suivi des commandes
- Inscription et gestion de compte utilisateur

### Interface Administrateur
- Tableau de bord avec statistiques
- Gestion complète du catalogue de produits (ajout, modification, suppression)
- Gestion des commandes et mise à jour des statuts
- Gestion des utilisateurs et des droits d'accès

## Structure du Projet

```
montres-boutique/
├── admin/
│   ├── index.php 
│   ├── products.php 
│   ├── product_add.php
│   ├── product_edit.php
│   └── users.php
├── assets/
│   ├── css/
│   │   └── style.css 
│   ├── js/
│   └── images/
│       └── products/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── index.php (votre landing page)
├── about.php
├── catalog.php
├── product.php
├── login.php
├── register.php
├── logout.php
├── cart.php
├── checkout.php
└── confirmation.php
```

## Installation

1. Clonez ce dépôt sur votre serveur web
2. Créez une base de données MySQL nommée `montres_boutique`
3. Importez le fichier SQL fourni pour créer les tables et insérer les données de démo
4. Configurez les paramètres de connexion dans le fichier `config/database.php`
5. Assurez-vous que le serveur web a les permissions d'écriture sur le dossier `assets/images/products`

## Configuration Requise

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Extension PDO PHP activée
- GD Library pour la manipulation d'images

## Identifiants de Démonstration

### Administrateur
- Email: b.b@gmail.com
- Mot de passe: b
- Pour la creation d'un compte admin code secret: admin123

### Client
- Email: a.a@a.com
- Mot de passe: a

## Personnalisation

Pour ajouter des fonctionnalités ou personnaliser le site:

1. Modifiez les fichiers PHP existants pour ajuster la logique métier
2. Personnalisez l'apparence en modifiant les classes Tailwind dans les fichiers HTML
3. Ajoutez de nouvelles pages en suivant la structure existante

## Sécurité

Ce projet implémente plusieurs mesures de sécurité:
- Protection contre les injections SQL avec les requêtes préparées PDO
- Hachage des mots de passe avec `password_hash()`
- Validation des entrées utilisateur
- Contrôle d'accès pour les zones administratives

## Support et Contribution

Pour signaler des bugs ou proposer des améliorations, veuillez créer une issue ou soumettre une pull request.

## Licence

Ce projet est distribué sous licence MIT. Voir le fichier LICENSE pour plus de détails.