<?php

use App\Livewire\PizzaOrderForm;
use App\Models\PizzaOrder;
use App\Models\PizzaRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a user can place an order in the open round', function () {
    $round = PizzaRound::create(['name' => 'Vrijdag', 'is_open' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PizzaOrderForm::class)
        ->set('pizza', '75. Sphinx')
        ->set('notes', 'zonder ui')
        ->call('save')
        ->assertHasNoErrors();

    $order = PizzaOrder::where('user_id', $user->id)->first();
    expect($order->pizza)->toBe('75. Sphinx');
    expect($order->notes)->toBe('zonder ui');
    expect($order->pizza_round_id)->toBe($round->id);
});

test('placing an order is an upsert: a second save updates, not duplicates', function () {
    PizzaRound::create(['name' => 'Vrijdag', 'is_open' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(PizzaOrderForm::class)->set('pizza', '53. Margherita')->call('save');
    Livewire::actingAs($user)->test(PizzaOrderForm::class)->set('pizza', 'Kapsalon')->call('save');

    expect(PizzaOrder::where('user_id', $user->id)->count())->toBe(1);
    expect(PizzaOrder::where('user_id', $user->id)->first()->pizza)->toBe('Kapsalon');
});

test('non-pizza options from the menu are valid choices', function () {
    PizzaRound::create(['name' => 'Vrijdag', 'is_open' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PizzaOrderForm::class)
        ->set('pizza', 'Boerensalade')
        ->call('save')
        ->assertHasNoErrors();

    expect(PizzaOrder::where('user_id', $user->id)->first()->pizza)->toBe('Boerensalade');
});

test('an item not on the menu is rejected', function () {
    PizzaRound::create(['name' => 'Vrijdag', 'is_open' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PizzaOrderForm::class)
        ->set('pizza', 'Frietje oorlog')
        ->call('save')
        ->assertHasErrors('pizza');

    expect(PizzaOrder::count())->toBe(0);
});

test('ordering food unlocks the pizza achievement', function () {
    PizzaRound::create(['name' => 'Vrijdag', 'is_open' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(PizzaOrderForm::class)->set('pizza', '60. Hawaï')->call('save');

    $slugs = $user->achievements()->wherePivotNotNull('achieved_at')->pluck('slug')->all();
    expect($slugs)->toContain('pizzaliefhebber');
});

test('everyone can see who ordered what in the open round', function () {
    $round = PizzaRound::create(['name' => 'Vrijdag', 'is_open' => true]);
    $bart = User::factory()->create(['name' => 'Bart']);
    $jasper = User::factory()->create(['name' => 'Jasper']);
    $round->orders()->create(['user_id' => $bart->id, 'pizza' => '60. Hawaï', 'notes' => 'extra kaas']);
    $round->orders()->create(['user_id' => $jasper->id, 'pizza' => 'Kapsalon']);

    Livewire::actingAs(User::factory()->create())
        ->test(PizzaOrderForm::class)
        ->assertSee('Bart')
        ->assertSee('60. Hawaï')
        ->assertSee('extra kaas')
        ->assertSee('Jasper')
        ->assertSee('Kapsalon');
});

test('the navigation links to the food ordering page', function () {
    $this->actingAs(User::factory()->create(['email_verified_at' => now()]));

    $this->get(route('timeline'))
        ->assertOk()
        ->assertSee('Eten')
        ->assertSee(route('pizza'), false);
});
