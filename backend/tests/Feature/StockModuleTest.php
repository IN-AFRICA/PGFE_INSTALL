describe('Stock API', function () {

<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\actingAs;
use App\Models\User;
use App\Models\StockArticle;

uses(RefreshDatabase::class);

test('stock: forbidden for guests', function () {
    getJson('/api/v1/stock/articles')->assertUnauthorized();
});

test('stock: can list articles for authenticated user', function () {
    $user = User::factory()->create();
    actingAs($user);
    getJson('/api/v1/stock/articles')->assertOk();
});

test('stock: can create an article', function () {
    $user = User::factory()->create();
    actingAs($user);
    $data = StockArticle::factory()->make()->toArray();
    $response = postJson('/api/v1/stock/articles', $data);
    $response->assertCreated();
    expect($response->json('id'))->not->toBeNull();
});

test('stock: can update an article', function () {
    $user = User::factory()->create();
    actingAs($user);
    $article = StockArticle::factory()->create();
    $update = ['name' => 'UpdatedArticle'];
    $response = putJson("/api/v1/stock/articles/{$article->id}", $update);
    $response->assertOk();
    expect($response->json('name'))->toBe('UpdatedArticle');
});

test('stock: can delete an article', function () {
    $user = User::factory()->create();
    actingAs($user);
    $article = StockArticle::factory()->create();
    $response = deleteJson("/api/v1/stock/articles/{$article->id}");
    $response->assertOk();
    expect(StockArticle::find($article->id))->toBeNull();
});
