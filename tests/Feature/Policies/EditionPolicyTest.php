<?php

namespace Tests\Feature\Policies;

use Tests\TestCase;
use App\Models\User;
use App\Models\Edition;
use App\Models\Signup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class EditionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_user_to_sign_up_if_not_already_signed_up()
    {
        $user = User::factory()->create();
        $edition = Edition::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('signup', $edition));
    }

    public function test_denies_user_from_signing_up_if_already_signed_up()
    {
        $user = User::factory()->create();
        $edition = Edition::factory()->create();

        Signup::factory()->create([
            'user_id' => $user->id,
            'edition_id' => $edition->id,
        ]);

        $this->assertFalse(Gate::forUser($user)->allows('signup', $edition));
    }
}
