# HeadCore CMS

## Lancer le projet en local

### Prérequis
- [Docker](https://www.docker.com/) et Docker Compose installés

### 1. Configurer les variables d'environnement

Copier le texte ci-dessous dans un fichier `.env` à la racine du projet :

```env
POSTGRES_DB=headcore
POSTGRES_USER=headcore
POSTGRES_PASSWORD=secret
POSTGRES_HOST=headcore-db-postgre
POSTGRES_PORT=5432
```

### 2. Démarrer les services

```bash
docker-compose up --build -d
```

| Service | URL |
|---|---|
| Backend PHP | http://localhost:80 |
| Adminer (DB UI) | http://localhost:8080 |

Pour se connecter à Adminer : sélectionner **PostgreSQL**, serveur `headcore-db-postgre`, avec les credentials du `.env`.

### 3. Arrêter les services

```bash
docker-compose down
```

Pour supprimer aussi les données de la base :

```bash
docker-compose down -v
```

---

## Choix techniques

### Images Docker

| Service | Image | Raison |
|---|---|---|
| Backend | `php:8.4-apache` | Dernière version stable PHP avec Apache intégré, `mod_rewrite` pour le front controller |
| Base de données | `postgres:18` | Version imposée par le cahier des charges |
| Adminer | `adminer:4` | Interface légère pour administrer PostgreSQL |
| Frontend | `node:20-alpine` | LTS, image Alpine pour minimiser la taille |

---

## Structure du projet

```
/app        → fonctionnalités CMS (Controllers, Entities, Repositories, Services)
/core       → framework PHP maison (Router, ORM, Http, Database…)
/public     → front controller, point d'entrée unique (index.php),  il reçoit toutes les requêtes et les dispatche au bon endroit.
/resources  → SCSS et JS pour le front du back office
/doc        → diagrammes UML et flux
```

## Diagrammes

![UML](doc/UML.png)
![Diagramme de flux](doc/flow.png)