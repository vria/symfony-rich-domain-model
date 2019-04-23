Modèle de domaine riche dans l'app Symfony
==========================================

[![Build Status](https://travis-ci.org/vria/symfony-rich-domain-model.svg?branch=master)](https://travis-ci.org/vria/symfony-rich-domain-model)
[![codecov](https://codecov.io/gh/vria/symfony-rich-domain-model/branch/master/graph/badge.svg)](https://codecov.io/gh/vria/symfony-rich-domain-model)

Domaine:

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
  - [CompteurInfoDTO]
- Exceptions:
  - [PersonneNotFoundException]
  - [PersonneEmailAlreadyTakenException]
  - [AbsenceDatesInvalidesException]
  - [AbsenceDatesDansLePasseException]
  - [AbsenceTypeInvalidException]
  - [AbsenceAlreadyTakenException]
  - [AbsenceJoursDisponiblesInsuffisantsException]
  - [AbsenceNotFoundException]

Veuillez regarder les commentaires dans les fichiers sources.

[Personne]: src/Domain/Personne.php
[Absence]: src/Domain/Absence.php
[AbsenceType]: src/Domain/AbsenceType.php
[AbsenceCompteur]: src/Domain/AbsenceCompteur.php
[AbsenceCompteurService]: src/Domain/Service/AbsenceCompteurService.php
[PersonneRepositoryInterface]: src/Domain/Repository/PersonneRepositoryInterface.php
[AbsenceRepositoryInterface]: src/Domain/Repository/AbsenceRepositoryInterface.php
[CompteurInfoDTO]: src/Domain/DTO/CompteurInfoDTO.php
[PersonneNotFoundException]: src/Domain/Exception/PersonneNotFoundException.php
[PersonneEmailAlreadyTakenException]: src/Domain/Exception/PersonneEmailAlreadyTakenException.php
[AbsenceDatesInvalidesException]: src/Domain/Exception/AbsenceDatesInvalidesException.php
[AbsenceDatesDansLePasseException]: src/Domain/Exception/AbsenceDatesDansLePasseException.php
[AbsenceTypeInvalidException]: src/Domain/Exception/AbsenceTypeInvalidException.php
[AbsenceAlreadyTakenException]: src/Domain/Exception/AbsenceAlreadyTakenException.php
[AbsenceJoursDisponiblesInsuffisantsException]: src/Domain/Exception/AbsenceJoursDisponiblesInsuffisantsException.php
[AbsenceNotFoundException]: src/Domain/Exception/AbsenceNotFoundException.php
