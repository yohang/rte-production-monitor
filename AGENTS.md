# AGENTS.md

## Vue d'ensemble
- Monolithe Symfony 7.1 + Doctrine pour suivre la production electrique francaise (`src/`, `config/`).
- Coeur domaine: `ProductionUnit` -> `ProductionSubUnit` -> series temporelles (`ProductionUnitValues`, `ProductionValue`).
- UX principale: carte Leaflet + sidebar Turbo (`templates/homepage.html.twig`, `templates/production_unit/show.html.twig`).
- Admin EasyAdmin en lecture majoritaire (`src/Controller/Admin/*CrudController.php`: create/edit/delete desactives).

## Flux de donnees
- Client RTE unique: `App\Bridge\RTE\RTEClient` (OAuth + cache + deserialisation `src/Bridge/RTE/Model/*`).
- Capacites: `app:import:rte-capacities-per-production-unit` -> `RTECapacitiesPerProductionUnitImporter`.
- Production reelle: `app:import:rte-actual-generations` -> `RTEActualGenerationsImporter` (upsert `ProductionValue`).
- Geoloc historique: `app:import:power-plant-informations-from-opsd` (CSV OPSD).
- Reconciliation des groupes: `app:reconciliate:multiple-production-units` + champ `firstUnitOfGroup`.

## Conventions projet (importantes)
- IDs en UUID v6 crees dans les constructeurs (pas d'ID DB auto).
- Entites timestampables via `TimestampImpl`; appeler `initialize()` dans chaque constructeur.
- Pattern d'import: factory `fromRTE*()` + mutateur `syncWithRTE()`.
- Repositories: `save()` fait `persist()` puis `flush()` immediat (les importers en dependent).
- Upsert metier frequent: chercher par cle metier, catcher `NoResultException`, creer si absent.
- Controllers majoritairement invokables avec attributs (`#[AsController]`, `#[Route]`, `#[Template]`).

## Scheduler, async, frontend
- Scheduler code-first dans `src/Scheduler/RecurrentScheduleProvider.php` (`#[AsSchedule('recurrent')]`).
- Cron: `5 * * * *` (actual generations), `0 2 * * *` (capacites), timezone `Europe/Paris`.
- Consommation scheduler: `make run_scheduler` ou `php bin/console messenger:consume scheduler_recurrent --no-debug -vv`.
- Transport Messenger Doctrine: `config/packages/messenger.yaml`, DSN via `MESSENGER_TRANSPORT_DSN`.
- Front sans pipeline Node: Asset Mapper + importmap (`importmap.php`, `assets/controllers.json`, `config/packages/asset_mapper.yaml`).

## Workflows locaux et integrations
- Workflow local prefere: Docker + `Makefile` (`make run`, `make reset`, `make test`, `make psalm`).
- Bootstrap initial via `make first_run` (certificats `mkcert`, images, deps, DB, importmap).
- Recharge complete des donnees: `composer reset` (drop/create schema + imports).
- Entree conteneur: attend la DB puis lance les migrations (`.infra/docker/php/docker-entrypoint`).
- Secrets requis: `RTE_CLIENT_ID`, `RTE_CLIENT_SECRET` (`config/services.yaml`).
- `UX_MAP_DSN` par defaut sur Leaflet (egalement present en tests dans `phpunit.xml.dist`).
- Vigilance connue: `src/Scheduler/ImportRTECapacitiesPerProductionUnitHandler.php` type-hinte `ImportRTEActualGenerations`.
