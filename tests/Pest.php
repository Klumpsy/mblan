<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

// Configure Feature tests to use Laravel TestCase with database features
uses(TestCase::class, RefreshDatabase::class)
    ->in('Feature', 'Unit');

// Alternative: If you want some unit tests to be pure PHPUnit tests
// uses(\PHPUnit\Framework\TestCase::class)->in('Unit/Pure');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeValidEmail', function () {
    return $this->toMatch('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
});

expect()->extend('toHaveValidTimestamps', function () {
    return $this->toHaveKeys(['created_at', 'updated_at']);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create a user for testing
 */
function createUser(array $attributes = []): \App\Models\User
{
    return \App\Models\User::factory()->create($attributes);
}

/**
 * Create multiple users for testing
 */
function createUsers(int $count = 3, array $attributes = []): \Illuminate\Database\Eloquent\Collection
{
    return \App\Models\User::factory()->count($count)->create($attributes);
}

/**
 * Acting as a user (authentication helper)
 */
function actingAsUser(\App\Models\User $user = null): \Tests\TestCase
{
    $user = $user ?: createUser();
    return test()->actingAs($user);
}

/**
 * Create an admin user
 */
function createAdmin(array $attributes = []): \App\Models\User
{
    return createUser(array_merge([
        'role' => 'admin',
        // Add other admin-specific attributes
    ], $attributes));
}

/**
 * Assert JSON structure helper
 */
function assertJsonStructure(array $structure): \Pest\Expectation
{
    return expect(test()->response->json())->toMatchArray($structure);
}

/**
 * Assert database has record
 */
function assertDatabaseHasRecord(string $table, array $data): void
{
    test()->assertDatabaseHas($table, $data);
}

/**
 * Assert database missing record
 */
function assertDatabaseMissingRecord(string $table, array $data): void
{
    test()->assertDatabaseMissing($table, $data);
}

/*
|--------------------------------------------------------------------------
| Dataset Helpers
|--------------------------------------------------------------------------
|
| Common datasets that can be used across multiple tests
|
*/

/**
 * Invalid email dataset
 */
function invalidEmails(): array
{
    return [
        'invalid-email',
        'test@',
        '@test.com',
        'test..test@example.com',
        'test@example',
        '',
    ];
}

/**
 * Valid email dataset
 */
function validEmails(): array
{
    return [
        'test@example.com',
        'user.name@domain.co.uk',
        'test+tag@example.org',
        'user123@test-domain.com',
    ];
}

/*
|--------------------------------------------------------------------------
| HTTP Testing Helpers
|--------------------------------------------------------------------------
|
| Helpers for API and HTTP testing
|
*/

/**
 * Make authenticated request
 */
function authenticatedRequest(string $method, string $uri, array $data = [], \App\Models\User $user = null): \Illuminate\Testing\TestResponse
{
    $user = $user ?: createUser();
    return test()->actingAs($user)->json($method, $uri, $data);
}

/**
 * Make GET request as authenticated user
 */
function authenticatedGet(string $uri, \App\Models\User $user = null): \Illuminate\Testing\TestResponse
{
    return authenticatedRequest('GET', $uri, [], $user);
}

/**
 * Make POST request as authenticated user
 */
function authenticatedPost(string $uri, array $data = [], \App\Models\User $user = null): \Illuminate\Testing\TestResponse
{
    return authenticatedRequest('POST', $uri, $data, $user);
}

/**
 * Make PUT request as authenticated user
 */
function authenticatedPut(string $uri, array $data = [], \App\Models\User $user = null): \Illuminate\Testing\TestResponse
{
    return authenticatedRequest('PUT', $uri, $data, $user);
}

/**
 * Make DELETE request as authenticated user
 */
function authenticatedDelete(string $uri, \App\Models\User $user = null): \Illuminate\Testing\TestResponse
{
    return authenticatedRequest('DELETE', $uri, [], $user);
}

/*
|--------------------------------------------------------------------------
| Traits for Specific Test Types
|--------------------------------------------------------------------------
|
| You can apply different traits to different test directories
|
*/

// Use Faker for tests that need fake data
uses(WithFaker::class)->in('Feature', 'Unit');

// Use database transactions instead of RefreshDatabase for faster tests (optional)
// uses(DatabaseTransactions::class)->in('Feature/Fast');

/*
|--------------------------------------------------------------------------
| Hooks
|--------------------------------------------------------------------------
|
| You can add global setup and teardown hooks here
|
*/

// Global setup before each test
beforeEach(function () {
    // Any setup code that should run before each test
    // For example: clear cache, reset config, etc.
});

// Global teardown after each test
afterEach(function () {
    // Any cleanup code that should run after each test
});

// Setup before all tests
beforeAll(function () {
    // Code that runs once before all tests
});

// Teardown after all tests
afterAll(function () {
    // Code that runs once after all tests
});
