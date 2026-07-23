<?php

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Filament admin panel smoke tests
|--------------------------------------------------------------------------
|
| The panel has no other automated coverage, so after the Filament v5
| migration these tests render each resource's list and create pages (which
| build the full form/table schemas) plus the dashboard (which mounts the
| widgets). A render error in any migrated resource fails here.
|
*/

function admin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

// The panel is intentionally minimal: games, game schedules, tournaments, users.
$resources = [
    'games',
    'schedules',
    'tournaments',
    'users',
];

$creatableResources = $resources;

test('admin dashboard renders with its widgets', function () {
    $this->actingAs(admin())
        ->get('/admin')
        ->assertOk();
});

test('resource list page renders', function (string $resource) {
    $this->actingAs(admin())
        ->get("/admin/{$resource}")
        ->assertOk();
})->with($resources);

test('resource create page renders the form schema', function (string $resource) {
    $this->actingAs(admin())
        ->get("/admin/{$resource}/create")
        ->assertOk();
})->with($creatableResources);

test('a non-admin cannot access the panel', function () {
    $this->actingAs(User::factory()->create(['role' => 'user']))
        ->get('/admin')
        ->assertForbidden();
});
