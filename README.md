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
  +int id
  +string? name
  +string email
  +string passwordHash
  +string role
  +string status
  +string createdAt
  +string? updatedAt
}

class Content {
  +int id
  +string title
  +string slug
  +string body
  +int authorId
  +string status
  +int? categoryId
  +int? updatedBy
  +string? publishedAt
  +string createdAt
  +string? updatedAt
}

class Category {
  +int id
  +string name
  +string slug
}

class Tag {
  +int id
  +string name
  +string slug
}

class ContentTag {
  +int contentId
  +int tagId
}

class Media {
  +int id
  +string fileName
  +string filePath
  +string altText
  +string mimeType
  +string createdAt
}

class AuditLog {
  +int id
  +string action
  +string entityType
  +int entityId
  +int userId
  +string createdAt
}

%% RELATIONS

User "1" --> "0..*" Content : authors
User "1" --> "0..*" AuditLog : performs

Content "0..*" --> "0..1" Category : categorized as
Content "0..*" --> "0..*" ContentTag : tagged via
Tag "1" --> "0..*" ContentTag : referenced by
Content "0..*" --> "0..*" Media : uses
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


Workflow de statut :
draft → review → published → archived
draft
contenu en cours d’écriture
non visible publiquement
modifiable librement par l’auteur
review
contenu soumis pour validation
non visible publiquement
en attente d’un éditeur/admin
published
visible publiquement
version officielle
archived
contenu retiré
non visible
conservé pour historique

| Action        | Qui               | Résultat                                      |
|---------------|-------------------|-----------------------------------------------|
| softDelete()  | Editor, Author    | deleted_at = now(), invisible mais récupérable |
| hardDelete()  | Admin seulement   | Supprimé définitivement de la DB              |