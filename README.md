# Horizon QueryBuilder

Un QueryBuilder fluide et moderne pour WordPress, développé par l'Agence Adeliom.

## Installation

### Prérequis

- PHP 8.2 ou supérieur
- WordPress 5.0 ou supérieur

### Via Composer

Ce package n'étant pas publié sur Packagist, vous devez ajouter le repository VCS dans votre fichier `composer.json` :

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/agence-adeliom/horizon-querybuilder.git"
        }
    ]
}
```

Puis installez le package :

```bash
composer require agence-adeliom/horizon-querybuilder
```

## Utilisation de base

### Namespace

```php
use Adeliom\HorizonQueryBuilder\Database\QueryBuilder;
use Adeliom\HorizonQueryBuilder\Database\MetaQuery;
use Adeliom\HorizonQueryBuilder\Database\TaxQuery;
use Adeliom\HorizonQueryBuilder\Database\LatLngQuery;
```

### Requête simple sur les posts

```php
$posts = (new QueryBuilder())
    ->postType('post')
    ->status('publish')
    ->perPage(10)
    ->page(1)
    ->orderBy('DESC', 'date')
    ->get();
```

### Requête sur les taxonomies

```php
$terms = (new QueryBuilder())
    ->taxonomy('category')
    ->fetchEmptyTaxonomies(false)
    ->orderBy('ASC', 'name')
    ->get();
```

## Référence des méthodes

### Sélection du type de requête

| Méthode | Description |
|---------|-------------|
| `postType(string\|array $postType)` | Définit le(s) type(s) de post à requêter |
| `taxonomy(string\|array $taxonomy)` | Définit la/les taxonomie(s) à requêter |

### Filtrage par ID

| Méthode | Description |
|---------|-------------|
| `whereIdIn(int\|array $ids)` | Inclure uniquement ces IDs |
| `whereIdNotIn(int\|array $ids)` | Exclure ces IDs |
| `removeIdIn(int\|array $ids)` | Retirer des IDs de la liste d'inclusion |
| `removeIdNotIn(int\|array $ids)` | Retirer des IDs de la liste d'exclusion |
| `whereParentIn(int\|WP_Post\|array $ids)` | Filtrer par ID(s) parent |

### Filtrage par slug

| Méthode | Description |
|---------|-------------|
| `whereSlug(null\|string\|array $slug)` | Filtrer par slug(s) |

### Recherche

```php
$posts = (new QueryBuilder())
    ->postType('post')
    ->search('mot-clé', ['post_title', 'post_content'], 'AND')
    ->get();
```

**Colonnes de recherche disponibles :**

- `QueryBuilder::SEARCH_COLUMN_TITLE` - `post_title`
- `QueryBuilder::SEARCH_COLUMN_CONTENT` - `post_content`
- `QueryBuilder::SEARCH_COLUMN_EXCERPT` - `post_excerpt`
- `QueryBuilder::SEARCH_COLUMN_NAME` - `post_name`

### Statut des posts

```php
->status(string|array $status)
```

**Statuts autorisés :** `any`, `publish`, `pending`, `draft`, `future`, `auto-draft`, `private`, `inherit`, `trash`

### Pagination

| Méthode | Description |
|---------|-------------|
| `page(int $page)` | Définir le numéro de page |
| `perPage(?int $perPage)` | Définir le nombre d'éléments par page |
| `offset(int $offset)` | Définir le nombre d'éléments à ignorer |
| `getPage()` | Récupérer le numéro de page actuel |
| `getPerPage()` | Récupérer le nombre d'éléments par page |

### Tri

```php
// Tri simple
->orderBy('DESC', 'date')

// Tri par meta
->orderBy('ASC', 'prix', true, true) // isMeta = true, isMetaNumeric = true

// Tri par taxonomie
->orderByTaxonomy('category', 'ASC')
```

### Sélection des champs

| Méthode | Description |
|---------|-------------|
| `fields(?string $fields)` | Spécifier les champs à retourner |
| `onlyIDs(bool $onlyIDs = true)` | Retourner uniquement les IDs |

**Valeurs autorisées pour les post types :** `''`, `'ids'`, `'id=>parent'`

**Valeurs autorisées pour les taxonomies :** `'all'`, `'all_with_object_id'`, `'ids'`, `'tt_ids'`, `'names'`, `'slugs'`, `'count'`, `'id=>parent'`, `'id=>name'`, `'id=>slug'`

### Transformation des résultats

```php
// Transformer chaque résultat en instance d'une classe personnalisée
$posts = (new QueryBuilder())
    ->postType('post')
    ->as(MonPostDTO::class)
    ->get();

// Avec un callback de transformation
$posts = (new QueryBuilder())
    ->postType('post')
    ->get(function($post) {
        $post->custom_field = get_post_meta($post->ID, 'custom', true);
        return $post;
    });
```

### Cache

```php
// Activer le cache (durée par défaut : 3600 secondes)
->useCache()

// Activer le cache avec une durée personnalisée
->useCache(7200) // 2 heures

// Désactiver le cache
->disableCache()
```

> **Note :** Le cache peut être désactivé globalement en définissant la constante `DISABLE_QUERY_BUILDER_CACHE` à `true`.

### Méthodes d'exécution

| Méthode | Description |
|---------|-------------|
| `get(?callable $callback = null)` | Exécuter la requête et retourner un tableau de résultats |
| `getOneOrNull()` | Récupérer le premier résultat ou `null` |
| `getQuery()` | Récupérer l'objet `WP_Query` ou `WP_Term_Query` brut |
| `getCount()` | Récupérer le nombre total de résultats |
| `getPagesCount()` | Récupérer le nombre total de pages |
| `getPaginatedData(?callable $callback = null)` | Récupérer les données paginées complètes |

#### Exemple avec getPaginatedData

```php
$result = (new QueryBuilder())
    ->postType('post')
    ->perPage(15)
    ->page(1)
    ->getPaginatedData();

// Retourne :
// [
//     'items' => [...],
//     'perPage' => 15,
//     'pages' => 5,
//     'total' => 75,
//     'current' => 1
// ]
```

## MetaQuery - Filtrage par meta

La classe `MetaQuery` permet de construire des requêtes complexes sur les meta des posts.

### Utilisation

```php
$metaQuery = new MetaQuery();
$metaQuery->add('color', 'blue', '=', 'CHAR')
          ->add('price', 100, '>', 'NUMERIC')
          ->setRelation('AND');

$posts = (new QueryBuilder())
    ->postType('product')
    ->addMetaQuery($metaQuery)
    ->get();
```

### Requêtes imbriquées

```php
$metaQuery = new MetaQuery();
$metaQuery->setRelation('OR');

$subQuery1 = new MetaQuery();
$subQuery1->add('color', 'blue');

$subQuery2 = new MetaQuery();
$subQuery2->add('color', 'red');

$metaQuery->add($subQuery1)->add($subQuery2);
```

### Comparateurs supportés

| Comparateur | Description |
|-------------|-------------|
| `=` | Égal (par défaut) |
| `!=` | Différent |
| `>` | Supérieur |
| `>=` | Supérieur ou égal |
| `<` | Inférieur |
| `<=` | Inférieur ou égal |
| `LIKE` | Contient |
| `NOT LIKE` | Ne contient pas |
| `IN` | Dans la liste |
| `NOT IN` | Pas dans la liste |
| `BETWEEN` | Entre deux valeurs |
| `NOT BETWEEN` | Pas entre deux valeurs |
| `EXISTS` | Le meta existe |
| `NOT EXISTS` | Le meta n'existe pas |

### Types de données supportés

`NUMERIC`, `BINARY`, `CHAR`, `DATE`, `DATETIME`, `DECIMAL`, `SIGNED`, `TIME`, `UNSIGNED`

## TaxQuery - Filtrage par taxonomie

La classe `TaxQuery` permet de filtrer les posts par termes de taxonomie.

### Utilisation

```php
$taxQuery = new TaxQuery();
$taxQuery->add('category', ['news', 'blog'], 'slug', 'IN')
         ->add('post_tag', [5, 10], 'term_id', 'AND')
         ->setRelation('AND');

$posts = (new QueryBuilder())
    ->postType('post')
    ->addTaxQuery($taxQuery)
    ->get();
```

### Paramètres de la méthode add

```php
$taxQuery->add(
    string $taxonomy,           // Nom de la taxonomie
    $terms,                     // Terme(s) : string, int, array ou WP_Term
    string $field = 'slug',     // Champ de comparaison
    string $operator = 'IN',    // Opérateur
    bool $includeChildren = false // Inclure les termes enfants
);
```

### Champs supportés

`slug`, `term_id`, `name`, `term_taxonomy_id`

### Opérateurs supportés

`IN`, `NOT IN`, `AND`, `EXISTS`, `NOT EXISTS`

## LatLngQuery - Recherche géolocalisée

La classe `LatLngQuery` permet de rechercher des posts par distance géographique.

### Utilisation

```php
$latLngQuery = new LatLngQuery();
$latLngQuery->add(
    'latitude',      // Clé meta contenant la latitude
    'longitude',     // Clé meta contenant la longitude
    48.8566,         // Latitude de référence
    2.3522,          // Longitude de référence
    50,              // Rayon en kilomètres
    true,            // Trier par distance
    'ASC'            // Ordre du tri (ASC = plus proche en premier)
);

$places = (new QueryBuilder())
    ->postType('place')
    ->addLatLngQuery($latLngQuery)
    ->get();
```

> **Note :** Une seule `LatLngQuery` peut être ajoutée par requête.

## Exemples avancés

### Requête complète avec filtres multiples

```php
$metaQuery = new MetaQuery();
$metaQuery->add('featured', '1', '=', 'CHAR')
          ->add('price', 50, '>=', 'NUMERIC')
          ->setRelation('AND');

$taxQuery = new TaxQuery();
$taxQuery->add('category', 'promotions', 'slug', 'IN');

$products = (new QueryBuilder())
    ->postType('product')
    ->status('publish')
    ->addMetaQuery($metaQuery)
    ->addTaxQuery($taxQuery)
    ->search('offre', [QueryBuilder::SEARCH_COLUMN_TITLE])
    ->orderBy('ASC', 'price', true, true)
    ->perPage(20)
    ->page(1)
    ->useCache(3600)
    ->get();
```

### Récupération avec transformation en DTO

```php
class ProductDTO {
    public int $id;
    public string $title;
    public float $price;

    public function __construct(WP_Post $post) {
        $this->id = $post->ID;
        $this->title = $post->post_title;
        $this->price = (float) get_post_meta($post->ID, 'price', true);
    }
}

$products = (new QueryBuilder())
    ->postType('product')
    ->as(ProductDTO::class)
    ->get();
```

### Pagination complète pour une API

```php
$page = $_GET['page'] ?? 1;
$perPage = $_GET['per_page'] ?? 10;

$response = (new QueryBuilder())
    ->postType('post')
    ->status('publish')
    ->perPage($perPage)
    ->page($page)
    ->useCache(1800)
    ->getPaginatedData(function($post) {
        return [
            'id' => $post->ID,
            'title' => $post->post_title,
            'excerpt' => $post->post_excerpt,
            'date' => $post->post_date,
        ];
    });

// $response contient : items, perPage, pages, total, current
```

## Licence

MIT - Agence Adeliom
