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
| Rôle | Responsabilité typique |                                                                                                                              
|------|----------------------|                                                                                                                              
| `admin` | Gestion complète (users, config, tout) |                                                                                                           
| `editor` | Publie/archive le contenu des autres |                                                                                                            
| `author` | Crée et gère son propre contenu |                                                                                                                 
| `reader` | Lecture seule (rôle par défaut à l'inscription) |

UML
```mermaid
classDiagram
direction LR

class User {
  +id
  +name
  +email
  +passwordHash
  +status
  +createdAt
  +updatedAt
}

class Role {
  +id
  +name
}

class Content {
  +id
  +title
  +slug
  +body
  +status
  +createdAt
  +updatedAt
  +publishedAt
}

class Category {
  +id
  +name
  +slug
}

class Tag {
  +id
  +name
  +slug
}

class Media {
  +id
  +fileName
  +filePath
  +altText
  +mimeType
  +createdAt
  +updatedAt
}

class Version {
  +id
  +versionNumber
  +title
  +body
  +createdAt
}

class AuditLog {
  +id
  +action
  +entityType
  +entityId
  +createdAt
}

%% RELATIONS

User "1" --> "1" Role : has
User "1" --> "0..*" Content : creates

Content "0..*" --> "0..*" Category : categorized as
Content "0..*" --> "0..*" Tag : tagged with
Content "0..*" --> "0..*" Media : uses

Content "1" --> "0..*" Version : has
Version "0..*" --> "1" User : created by

User "1" --> "0..*" AuditLog : performs
```
Diagramme de flux
```mermaid
flowchart TD
    A[Utilisateur arrive sur le CMS] --> B{Connecté ?}

    B -- Non --> C[Page de connexion]
    C --> D[Saisie email + mot de passe]
    D --> E{Identifiants valides ?}
    E -- Non --> C
    E -- Oui --> F{Rôle utilisateur}

    B -- Oui --> F{Rôle utilisateur}

    %% Gestion accès
    F -- Lecteur --> R1[Accès refusé au back-office]
    F -- Auteur / Éditeur / Admin --> G[Accès au back-office]

    %% Actions
    G --> H{Action choisie}
    
    H --> I[Créer un contenu]
    H --> J[Modifier un contenu]
    H --> K[Supprimer un contenu]
    H --> L[Gérer catégories / tags]
    H --> M[Gérer médias]

    %% Création
    I --> N[Saisie des informations]
    N --> O[Enregistrer en brouillon]

    %% Modification
    J --> P[Mettre à jour le contenu]
    P --> Q[Enregistrer les modifications]

    %% Workflow
    O --> S{Demander publication ?}
    Q --> S

    S -- Non --> T[Contenu reste en brouillon]
    S -- Oui --> U{Rôle autorisé à publier ?}

    %% Rôles publication
    U -- Auteur --> V[Envoyer en relecture]
    U -- Éditeur / Admin --> W[Publier le contenu]

    %% Validation
    V --> X[Relecture par éditeur/admin]
    X --> Y{Validé ?}
    Y -- Non --> Z[Retour en brouillon]
    Y -- Oui --> W[Publier le contenu]

    %% Résultat final
    W --> AA[Contenu visible sur le site / API publique]

    %% Suppression
    K --> AB{Rôle autorisé ?}
    AB -- Admin --> AC[Suppression définitive]
    AB -- Auteur / Éditeur --> AD[Archivage du contenu]
```

# Rôles et permissions
| Permission        | Admin | Editor | Author | Reader |
|------------------|:-----:|:------:|:------:|:------:|
| content.read     |  ✅   |   ✅   |   ✅   |   ✅   |
| content.create   |  ✅   |   ✅   |   ✅   |   ❌   |
| content.edit.own |  ✅   |   ✅   |   ✅   |   ❌   |
| content.edit.any |  ✅   |   ✅   |   ❌   |   ❌   |
| content.publish  |  ✅   |   ✅   |   ❌   |   ❌   |
| content.archive  |  ✅   |   ✅   |   ❌   |   ❌   |
| content.delete   |  ✅   |   ❌   |   ❌   |   ❌   |
| media.upload     |  ✅   |   ✅   |   ✅   |   ❌   |
| media.delete     |  ✅   |   ✅   |   ❌   |   ❌   |
| user.manage      |  ✅   |   ❌   |   ❌   |   ❌   |
| settings.manage  |  ✅   |   ❌   |   ❌   |   ❌   |