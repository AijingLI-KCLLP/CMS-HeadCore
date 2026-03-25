# Guide du Framework  🧩

Ce guide explique comment utiliser le framework pour développer le CMS. Il est écrit pour quelqu'un qui n'a jamais utilisé de framework ni le pattern MVC.

---

## C'est quoi un framework ? 🛠️

Un framework, c'est une boîte à outils. Au lieu de réécrire à chaque projet le code pour lire une requête HTTP, connecter une base de données, ou envoyer une réponse JSON — le framework le fait pour toi. Tu te concentres uniquement sur la logique de ton application.

---

## C'est quoi MVC ? 🧱

MVC signifie **Model – View – Controller**. C'est une façon d'organiser le code en 3 rôles distincts :

| Rôle | Ce qu'il fait | Dans Headcore |
|---|---|---|
| **Model** | Représente les données (une ligne en base) | `app/Entities/` |
| **View** | Affiche les données (HTML, JSON) | `Response::json()` ou `views/` |
| **Controller** | Reçoit la requête et orchestre | `app/Controllers/` |

Dans Headcore, on ajoute deux couches supplémentaires :
- **Service** — contient la logique métier (règles, validations)
- **Repository** — accès à la base de données

---

## Comment une requête se passe-t-elle ? 🔄

Quand quelqu'un appelle `GET /users/1`, voici ce qui se passe dans l'ordre :

```
Navigateur / Client
      │
      ▼
public/index.php        ← point d'entrée unique
      │
      ▼
Router                  ← lit config/routes.json, trouve la bonne route
      │
      ▼
UserController          ← reçoit la Request, décide quoi faire
      │
      ▼
UserService             ← applique la logique métier
      │
      ▼
UserRepository          ← interroge la base de données
      │
      ▼
User (Entity)           ← objet PHP qui représente une ligne en base
      │
      ▼
Response::json()        ← renvoie le résultat au client
```

---

## Structure des dossiers 🗂️

```
headcore/
├── app/                    ← TON code (fonctionnalités du CMS)
│   ├── Controllers/        ← reçoit les requêtes HTTP
│   ├── Services/           ← logique métier
│   ├── Repositories/       ← accès base de données
│   └── Entities/           ← structure des données
│
├── core/                   ← LE FRAMEWORK (ne pas modifier)
│   ├── Http/               ← Request, Response, Router, Session
│   ├── Auth/               ← Auth, PasswordHasher
│   ├── Config/             ← Config (lecture des fichiers JSON)
│   ├── Database/           ← connexion PostgreSQL
│   ├── ORM/                ← QueryBuilder
│   ├── Entities/           ← AbstractEntity
│   ├── Repositories/       ← AbstractRepository
│   ├── Services/           ← AbstractService
│   └── Controllers/        ← AbstractController
│
├── config/                 ← fichiers de configuration JSON
│   ├── routes.json         ← toutes les routes de l'app
│   └── auth.json           ← config auth (session, algo hash)
│
└── public/
    └── index.php           ← seul point d'entrée web
```

**Règle simple** ⚠️ : tu travailles dans `app/`. Tu ne modifies pas `core/`.

---

## Étape 1 — Déclarer une route 🛣️

Toutes les routes sont dans `config/routes.json`.

```json
[
    { "path": "/articles",      "method": "GET",    "controller": "ArticleController" },
    { "path": "/articles",      "method": "POST",   "controller": "ArticleController" },
    { "path": "/articles/{id}", "method": "GET",    "controller": "ArticleController" },
    { "path": "/articles/{id}", "method": "PUT",    "controller": "ArticleController" },
    { "path": "/articles/{id}", "method": "DELETE", "controller": "ArticleController" }
]
```

- `{id}` est un paramètre dynamique — il sera accessible dans le controller via `$request->getSlug('id')`.
- `controller` est le nom de la classe dans `app/Controllers/` (sans le namespace).
- Méthodes supportées : `GET`, `POST`, `PUT`, `DELETE`.

---

## Étape 2 — Créer une Entity 🧬

Une Entity est un objet PHP qui représente une ligne dans une table de base de données. Elle utilise des **annotations** (les `#[...]`) pour décrire le mapping.

```php
<?php
// app/Entities/Article.php

namespace App\Entities;

use Core\Annotations\ORM\ORM;
use Core\Annotations\ORM\Id;
use Core\Annotations\ORM\AutoIncrement;
use Core\Annotations\ORM\Column;
use Core\Entities\AbstractEntity;

#[ORM(table: 'articles')]          // nom de la table en base
class Article extends AbstractEntity
{
    #[Id]                          // c'est la clé primaire
    #[AutoIncrement]               // valeur auto-générée par la DB
    #[Column(type: 'int')]
    private int $id;

    #[Column(type: 'string', size: 255)]
    private string $title;

    #[Column(type: 'string')]
    private string $content;

    #[Column(type: 'string', name: 'created_at')]  // nom DB différent du nom PHP
    private string $createdAt;

    // Getters et setters
    public function getId(): int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }

    public function getCreatedAt(): string { return $this->createdAt; }
}
```

**Règles** 📌 :
- Toujours étendre `AbstractEntity`.
- L'annotation `#[ORM(table: '...')]` est obligatoire — c'est le nom de la table SQL.
- Chaque propriété persistée doit avoir `#[Column(...)]`.
- La clé primaire doit avoir `#[Id]` et `#[AutoIncrement]`.
- Si le nom de la propriété PHP diffère du nom de la colonne SQL, utilise `name: 'nom_sql'`.
- Les setters doivent retourner `self` (fluent interface).

---

## Étape 3 — Créer un Repository 🗄️

Le Repository est la seule classe qui touche à la base de données. Pour la plupart des cas, `AbstractRepository` suffit — tu n'as qu'à ajouter des méthodes spécifiques si nécessaire.

```php
<?php
// app/Repositories/ArticleRepository.php

namespace App\Repositories;

use App\Entities\Article;
use Core\Repositories\AbstractRepository;

class ArticleRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Article::class);  // indique quelle entity on gère
    }

    // Méthode personnalisée — les méthodes de base sont déjà dans AbstractRepository
    public function findByTitle(string $title): array
    {
        return $this->findBy(['title' => $title]);
    }
}
```

**Méthodes disponibles sans rien écrire** ✨ (héritées de `AbstractRepository`) :

| Méthode | Ce qu'elle fait |
|---|---|
| `find(int $id)` | Récupère un enregistrement par ID |
| `findAll()` | Récupère tous les enregistrements |
| `findBy(['col' => 'val'])` | Récupère plusieurs enregistrements selon des critères |
| `findOneBy(['col' => 'val'])` | Récupère un seul enregistrement selon des critères |
| `save($entity)` | Insère un nouvel enregistrement, retourne l'ID |
| `update($entity)` | Met à jour un enregistrement existant |
| `remove($entity)` | Supprime un enregistrement |
| `paginate($page, $perPage, $criteria)` | Récupère une page de résultats avec le total |
| `raw($sql, $params)` | Exécute une requête SQL brute |

---

## Étape 4 — Créer un Service ⚙️

Le Service contient la **logique métier** : vérifications, règles, coordination entre repositories. Il ne doit jamais toucher directement à PDO ou au QueryBuilder.

```php
<?php
// app/Services/ArticleService.php

namespace App\Services;

use App\Entities\Article;
use App\Repositories\ArticleRepository;
use Core\Services\AbstractService;

class ArticleService extends AbstractService
{
    public function __construct()
    {
        parent::__construct(new ArticleRepository());
    }

    public function listArticles(): array
    {
        return $this->repository->findAll();
    }

    public function getArticleById(int $id): ?Article
    {
        return $this->repository->find($id);
    }

    public function createArticle(string $title, string $content): string
    {
        // Logique métier : vérifier que le titre n'est pas vide
        if (trim($title) === '') {
            throw new \RuntimeException('Le titre ne peut pas être vide.', 422);
        }

        $article = (new Article())
            ->setTitle($title)
            ->setContent($content);

        return $this->repository->save($article);
    }

    public function deleteArticle(int $id): void
    {
        $article = $this->getArticleById($id);
        if ($article === null) {
            throw new \RuntimeException('Article introuvable.', 404);
        }
        $this->repository->remove($article);
    }
}
```

**Règles** 📌 :
- Toujours étendre `AbstractService`.
- La propriété `$this->repository` est disponible automatiquement.
- Les erreurs métier se lancent avec `throw new \RuntimeException('message', code_http)`.

---

## Étape 5 — Créer un Controller 🎮

Le Controller est **fin** : il reçoit la requête, appelle le Service, retourne la Response. Pas de SQL, pas de logique métier ici.

```php
<?php
// app/Controllers/ArticleController.php

namespace App\Controllers;

use App\Services\ArticleService;
use Core\Auth\Auth;
use Core\Controllers\AbstractController;
use Core\Http\Request;
use Core\Http\Response;

class ArticleController extends AbstractController
{
    private ArticleService $articleService;

    public function __construct()
    {
        $this->articleService = new ArticleService();
    }

    public function process(Request $request): Response
    {
        return match ($request->getMethod()) {
            'GET'    => $this->handleGet($request),
            'POST'   => $this->handlePost($request),
            'DELETE' => $this->handleDelete($request),
            default  => Response::error('Method not allowed', 405),
        };
    }

    private function handleGet(Request $request): Response
    {
        $id = $request->getSlug('id');

        if ($id !== null) {
            $article = $this->articleService->getArticleById((int) $id);
            if ($article === null) {
                return Response::error('Article introuvable', 404);
            }
            return Response::json($article->toArray());
        }

        $articles = array_map(fn($a) => $a->toArray(), $this->articleService->listArticles());
        return Response::json($articles);
    }

    private function handlePost(Request $request): Response
    {
        Auth::guard();  // bloque si pas connecté (401)

        $body = $request->getJsonBody();

        if (empty($body['title']) || empty($body['content'])) {
            return Response::error('Champs manquants', 422);
        }

        $id = $this->articleService->createArticle($body['title'], $body['content']);
        return Response::json(['id' => $id], 201);
    }

    private function handleDelete(Request $request): Response
    {
        Auth::guard();

        $id = $request->getSlug('id');
        $this->articleService->deleteArticle((int) $id);
        return Response::json(['message' => 'Supprimé']);
    }
}
```

---

## Référence — Request 📥

Récupérer des données de la requête :

```php
$request->getMethod()        // 'GET', 'POST', 'PUT', 'DELETE'
$request->getPath()          // '/articles/1'
$request->getSlug('id')      // '1' (depuis /articles/{id}), ou null si absent
$request->getUrlParams()     // $_GET — ex: ['page' => '2'] depuis /articles?page=2
$request->getJsonBody()      // body JSON décodé en tableau PHP
$request->getPayload()       // body brut (string)
$request->expectsJson()      // true si le client attend du JSON
$request->getHeaders()       // tous les headers HTTP
```

---

## Référence — Response 📤

Créer et retourner des réponses :

```php
// Réponse JSON (la plus courante pour une API)
return Response::json(['key' => 'value']);
return Response::json(['id' => 1], 201);           // avec code HTTP personnalisé

// Réponse d'erreur
return Response::error('Non trouvé', 404);
return Response::error('Non autorisé', 401);

// Réponse HTML (pour les vues)
return $this->render('articles/list', ['articles' => $articles]);
```

Codes HTTP courants 🌐 :
- `200` — OK
- `201` — Créé
- `302` - Redirection
- `400` — Mauvaise requête
- `401` — Non authentifié
- `403` — Interdit
- `404` — Non trouvé
- `405` — Méthode non autorisée
- `409` — Conflit (ex: email déjà utilisé)
- `422` — Données invalides
- `500` — Erreur serveur

---

## Référence — Auth 🔐

Gérer l'authentification :

```php
use Core\Auth\Auth;
use Core\Auth\PasswordHasher;

// Protéger une route (lance une Exception 401 si pas connecté)
Auth::guard();

// Vérifier si connecté sans bloquer
if (Auth::check()) { ... }

// Récupérer l'ID de l'utilisateur connecté
$userId = Auth::id();

// Connecter un utilisateur (après vérification du mot de passe)
Auth::login($userEntity);

// Déconnecter
Auth::logout();

// Hasher un mot de passe (à l'inscription)
$hash = PasswordHasher::hash('monmotdepasse');

// Vérifier un mot de passe (à la connexion)
$ok = PasswordHasher::verify('monmotdepasse', $hash);
```

---

## Référence — Session 🧠

Stocker des données en session :

```php
use Core\Http\Session;

Session::set('key', 'value');        // écrire
Session::get('key');                 // lire (retourne null si absent)
Session::get('key', 'defaut');       // lire avec valeur par défaut
Session::has('key');                 // vérifier si la clé existe
Session::remove('key');              // supprimer une clé
Session::destroy();                  // détruire toute la session
```

---

## Référence — Config 🧾

Lire les fichiers de configuration dans `config/` :

```php
use Core\Config\Config;

// config/auth.json → { "session_lifetime": 3600 }
Config::get('auth.session_lifetime');    // 3600
Config::get('auth.inexistant', 'def');  // 'def' (valeur par défaut)

// Ou avec le helper global
config('auth.session_lifetime');
```

Pour ajouter une config, crée `config/monconfig.json` et accède avec `Config::get('monconfig.macle')`.

---

## Référence — QueryBuilder avancé 🧪

Pour les requêtes SQL complexes non couvertes par `findBy()` ou `findAll()`, utilise le QueryBuilder directement dans le Repository.

```php
// Dans un Repository
public function findPublishedByCategory(string $category): array
{
    $this->getQueryBuilder()
        ->build()
        ->select('a.*')
        ->from('a')
        ->where('status', QueryConditions::EQ)
        ->andWhere('category', QueryConditions::EQ)
        ->orderBy('created_at', 'DESC')
        ->limit(10)
        ->addParam('status', 'published')
        ->addParam('category', $category)
    ;

    return $this->executeQuery()->getAllResults();
}

// Avec JOIN
public function findWithAuthor(): array
{
    $this->getQueryBuilder()
        ->build()
        ->select('a.*', 'u.name AS author_name')
        ->from('a')
        ->innerJoin('users u', 'u.id = a.user_id')
    ;
    return $this->executeQuery()->getAllResults();
}

// SQL brut (à éviter sauf si nécessaire)
public function countByStatus(): array
{
    return $this->raw(
        'SELECT status, COUNT(*) FROM articles GROUP BY status',
        []
    );
}
```

**Conditions disponibles** 🧩 : `QueryConditions::EQ`, `NEQ`, `LT`, `LTE`, `GT`, `GTE`, `LIKE`, `IN`.

**Ordre des méthodes** 🪜 pour un SELECT :
```
build() → select() → from() → [join()] → [where()] → [orderBy()] → [limit()] → [offset()]
```

Pour debugger une requête 🐞 :
```php
$this->getQueryBuilder()->build()->select()->from()...;
echo $this->queryBuilder->debug();  // affiche le SQL avec les paramètres injectés
```

---

## Référence — Pagination 📚

```php
// Dans un controller
$result = $this->articleService->paginate($page, $perPage);

// Dans le service
public function paginate(int $page, int $perPage = 10): array
{
    return $this->repository->paginate($page, $perPage);
}

// Résultat retourné :
// [
//     'data'        => [...],   // tableau d'entités
//     'total'       => 42,      // nombre total d'enregistrements
//     'page'        => 1,
//     'per_page'    => 10,
//     'total_pages' => 5,
// ]
```

---

## Récapitulatif — Checklist pour créer une nouvelle fonctionnalité ✅

Pour ajouter une fonctionnalité (ex: gérer des articles) :

- [ ] Créer la table SQL en base
- [ ] Créer `app/Entities/Article.php` avec les annotations `#[ORM]`, `#[Column]`, etc.
- [ ] Créer `app/Repositories/ArticleRepository.php` qui étend `AbstractRepository`
- [ ] Créer `app/Services/ArticleService.php` qui étend `AbstractService`
- [ ] Créer `app/Controllers/ArticleController.php` qui étend `AbstractController`
- [ ] Ajouter les routes dans `config/routes.json`
- [ ] Tester avec `curl` ou un client HTTP

---

## Règles d'architecture à ne jamais violer 🚫

| ❌ Interdit | ✅ Correct |
|---|---|
| SQL ou PDO dans un Controller | Déléguer au Service |
| Logique métier dans un Repository | La mettre dans le Service |
| `new PDO(...)` dans un Controller | Utiliser le Repository |
| Modifier `core/` | Étendre les classes dans `app/` |
| Accéder à `$_SESSION` directement | Utiliser `Session::get/set()` |
| `password_hash()` en dehors de `PasswordHasher` | Utiliser `PasswordHasher::hash()` |
