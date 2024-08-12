# COMMANDES

## Nouveau Controller
```bash
symfony console make:controller
```
## Nouvelle entité (ou modifier)
```bash
symfony console make:entity
```
## Nouveau formulaire
```bash
symfony console make:form
```
## Classe Users + Authentification + formulaire d'inscription
```bash
symfony console make:user
```
```bash
symfony console make:auth
```
```bash
symfony console make:registration-form
```

## Migration
```bash
symfony console make:migration
symfony console d:m:m
```


## Fixtures

Nouvelle fixtures : 
```bash
symfony console make:fixtures
```
Persister les données : 
```bash
symfony console d:f:l --no-interaction
```
```bash
symfony console d:f:l --append
```

## Validator
```bash
symfony console make:validator
```

## Voter
```bash
symfony console make:voter
```

## Divers

### Créer la base de données
```bash
symfony console d:d:c
```
### Recréer les tables
```bash
symfony console doctrine:schema:update --force
```
### Effacer toutes les tables
```bash
symfony console doctrine:schema:drop --force
```

### Cache Clear

```bash
symfony console cache:clear 
```
### Lister les routes

```bash
symfony console debug:router
```

# Tailwaind CSS
```bash
composer require symfonycasts/tailwind-bundle
php bin/console tailwind:init
php bin/console tailwind:build --watch
```
# donner les droits depuis le container
```bash
chmod -R 775 /var/www/project/...
chown -R www-data:www-data /var/www/project/...
```

