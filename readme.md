# 🔴🟢 Laravel TDD — PHPUnit & Test-Driven Development

TDD c'est contre-intuitif au début. Écrire un test qui échoue *avant* d'écrire le code
semble perdre du temps. En réalité, ça force à réfléchir au comportement attendu
avant de se noyer dans l'implémentation — et ça produit du code plus propre et plus
simple, parce qu'on n'écrit que ce qui est nécessaire pour faire passer le test.

Ce projet est une API Laravel construite entièrement avec l'approche TDD :
chaque endpoint a été testé en premier, le code a suivi.

---

## Le cycle Red-Green-Refactor

```
  ┌─────────────────────────────────────────────────────────────┐
  │                   Cycle TDD                                 │
  │                                                             │
  │   1. RED  ──────────────────────────────────────────────►  │
  │      Écrire un test qui décrit le comportement attendu      │
  │      → Le test échoue car le code n'existe pas encore       │
  │                                                             │
  │   2. GREEN ◄────────────────────────────────────────────   │
  │      Écrire le minimum de code pour faire passer le test    │
  │      → Pas d'optimisation, juste faire passer               │
  │                                                             │
  │   3. REFACTOR                                               │
  │      Améliorer le code sans changer le comportement         │
  │      → Les tests garantissent qu'on ne casse rien           │
  │                                                             │
  │   4. Recommencer pour la prochaine fonctionnalité ──────►  │
  └─────────────────────────────────────────────────────────────┘
```

---

## Structure de la suite de tests

```
tests/
├── Unit/
│   ├── UserTest.php           ← tests du modèle User (attributs, relations)
│   ├── PostTest.php           ← tests du modèle Post (validation, scopes)
│   └── HelperTest.php         ← fonctions utilitaires
│
└── Feature/
    ├── AuthTest.php           ← register, login, logout, refresh token
    ├── UserProfileTest.php    ← GET/PUT /api/profile (auth required)
    ├── PostCrudTest.php       ← GET/POST/PUT/DELETE /api/posts
    └── ValidationTest.php    ← cas limites : champs manquants, formats invalides
```

---

## Exemple concret : test écrit AVANT le code

```php
// tests/Feature/PostCrudTest.php

class PostCrudTest extends TestCase
{
    use RefreshDatabase;  // base de données propre pour chaque test

    /** @test */
    public function authenticated_user_can_create_a_post()
    {
        // Arrange : créer un utilisateur et s'authentifier
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Act : envoyer la requête
        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->postJson('/api/posts', [
                             'title'   => 'Mon premier article',
                             'content' => 'Contenu de l\'article...',
                         ]);

        // Assert : vérifier la réponse ET la base de données
        $response->assertCreated()
                 ->assertJsonStructure(['id', 'title', 'content', 'created_at']);

        $this->assertDatabaseHas('posts', ['title' => 'Mon premier article']);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_a_post()
    {
        $response = $this->postJson('/api/posts', ['title' => 'Test']);
        $response->assertUnauthorized();  // 401
    }

    /** @test */
    public function post_requires_title_and_content()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->postJson('/api/posts', []);  // body vide

        $response->assertUnprocessable()              // 422
                 ->assertJsonValidationErrors(['title', 'content']);
    }
}
```

---

## Configuration de l'environnement de test

```xml
<!-- phpunit.xml -->
<php>
    <server name="APP_ENV"        value="testing"/>
    <server name="DB_CONNECTION"  value="sqlite"/>
    <server name="DB_DATABASE"    value=":memory:"/>  <!-- base en mémoire -->
    <server name="CACHE_DRIVER"   value="array"/>
    <server name="QUEUE_CONNECTION" value="sync"/>
</php>
```

SQLite en mémoire = chaque test repart d'une base propre, sans ralentir.

---

## Lancer les tests

```bash
git clone https://github.com/elouafi-abderrahmane-2002/laravel-tdd.git
cd laravel-tdd
composer install && cp .env.example .env && php artisan key:generate

# Lancer tous les tests
php artisan test

# Avec couverture de code (nécessite Xdebug)
php artisan test --coverage

# Un seul fichier
php artisan test tests/Feature/PostCrudTest.php
```

---

## Ce que j'ai vraiment appris

`RefreshDatabase` vs `DatabaseTransactions` : j'ai utilisé `RefreshDatabase` au début
partout — ça recrée toute la base à chaque test. C'est propre mais lent sur de grosses
suites. `DatabaseTransactions` est plus rapide (rollback au lieu de migration) mais
pose des problèmes avec les événements qui font des requêtes dans des threads séparés.

Choisir le bon trait selon le contexte, c'est le genre de détail qui fait la différence
entre une suite de tests qui tourne en 30 secondes et une qui tourne en 5 minutes.

---

*Projet réalisé dans le cadre de ma formation ingénieur — ENSET Mohammedia*
*Par **Abderrahmane Elouafi** · [LinkedIn](https://www.linkedin.com/in/abderrahmane-elouafi-43226736b/) · [Portfolio](https://my-first-porfolio-six.vercel.app/)*
