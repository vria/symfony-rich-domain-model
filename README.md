Modèle de domaine riche dans l'app Symfony
==========================================

[![Build Status](https://travis-ci.org/vria/symfony-rich-domain-model.svg?branch=master)](https://travis-ci.org/vria/symfony-rich-domain-model)
[![codecov](https://codecov.io/gh/vria/symfony-rich-domain-model/branch/master/graph/badge.svg)](https://codecov.io/gh/vria/symfony-rich-domain-model)

### Domaine

- Entités:
  - [Personne]
  - [Absence]
  - [AbsenceType]
  - [AbsenceCompteur]
- Services:
  - [AbsenceCompteurService]
- Repositoires:
  - [PersonneRepositoryInterface]
  - [AbsenceRepositoryInterface]
- DTOs:
  - [PersonneCreerDTO]
  - [AbsenceDeposerDTO]
  - [AbsenceModifierDTO]
  - [CompteurInfoDTO]
- Exceptions:
  - [PersonneNonTrouveeException]
  - [PersonneEmailDejaEnregistreException]
  - [AbsenceNonTrouveeException]
  - [AbsenceTypeInvalideException]
  - [AbsenceDatesInvalidesException]
  - [AbsenceDatesDansLePasseException]
  - [AbsenceDejaDeposeeException]
  - [AbsenceJoursDisponiblesInsuffisantsException]

Veuillez regarder les commentaires dans les fichiers sources.


### Lancer l'application

Créer la bdd:
```bash
php bin/console doctrine:database:create
```

Mettre à jour le schema de la bdd:
```bash
php bin/console doctrine:schema:update --force
```

Charger les données de tests:
```bash
php bin/console doctrine:fixtures:load --env=test
```

Lancer les tests *sans changer les données dans la bdd*: 
```bash
vendor/bin/phpunit
```

[Personne]: src/Domain/Personne.php
[Absence]: src/Domain/Absence.php
[AbsenceType]: src/Domain/AbsenceType.php
[AbsenceCompteur]: src/Domain/AbsenceCompteur.php
[AbsenceCompteurService]: src/Domain/Service/AbsenceCompteurService.php
[PersonneRepositoryInterface]: src/Domain/Repository/PersonneRepositoryInterface.php
[AbsenceRepositoryInterface]: src/Domain/Repository/AbsenceRepositoryInterface.php
[PersonneCreerDTO]: src/Domain/DTO/PersonneCreerDTO.php
[AbsenceDeposerDTO]: src/Domain/DTO/AbsenceDeposerDTO.php
[AbsenceModifierDTO]: src/Domain/DTO/AbsenceModifierDTO.php
[CompteurInfoDTO]: src/Domain/DTO/CompteurInfoDTO.php
[PersonneNonTrouveeException]: src/Domain/Exception/PersonneNonTrouveeException.php
[PersonneEmailDejaEnregistreException]: src/Domain/Exception/PersonneEmailDejaEnregistreException.php
[AbsenceNonTrouveeException]: src/Domain/Exception/AbsenceNonTrouveeException.php
[AbsenceTypeInvalideException]: src/Domain/Exception/AbsenceTypeInvalideException.php
[AbsenceDatesInvalidesException]: src/Domain/Exception/AbsenceDatesInvalidesException.php
[AbsenceDatesDansLePasseException]: src/Domain/Exception/AbsenceDatesDansLePasseException.php
[AbsenceDejaDeposeeException]: src/Domain/Exception/AbsenceDejaDeposeeException.php
[AbsenceJoursDisponiblesInsuffisantsException]: src/Domain/Exception/AbsenceJoursDisponiblesInsuffisantsException.php
