<?php

use App\Filament\Resources\PhotoResource\Pages\CreatePhoto;
use App\Filament\Resources\PhotoResource\Pages\ListPhotos;
use App\Models\Edition;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->old = Edition::where('slug', 'mblan25')->firstOrFail();
});

it('lists timeline photos with the active edition as default filter', function () {
    $current = Photo::factory()->create(['story' => 'Van nu']);
    $old = Photo::factory()->create(['story' => 'Van toen', 'edition_id' => $this->old->id]);

    Livewire::actingAs($this->admin)
        ->test(ListPhotos::class)
        ->assertCanSeeTableRecords([$current])
        ->assertCanNotSeeTableRecords([$old]);
});

it('backfills a timeline photo into an older edition with its own date', function () {
    Storage::fake('public');
    $author = User::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(CreatePhoto::class)
        ->fillForm([
            'user_id' => $author->id,
            'edition_id' => $this->old->id,
            'story' => 'Legendarisch potje van 2025',
            'created_at' => '2025-08-02 21:00:00',
            'image' => UploadedFile::fake()->image('oud.jpg', 640, 480),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $photo = Photo::where('story', 'Legendarisch potje van 2025')->sole();
    expect($photo->edition_id)->toBe($this->old->id)
        ->and($photo->user_id)->toBe($author->id)
        ->and($photo->created_at->format('Y-m-d'))->toBe('2025-08-02')
        ->and($photo->image)->not->toBeEmpty();
});

it('shows backfilled photos on that edition recap', function () {
    Photo::factory()->create(['story' => 'Terug in de tijd', 'edition_id' => $this->old->id]);

    $this->actingAs(User::factory()->create())->get('/edities/mblan25')
        ->assertOk()
        ->assertSee('Terug in de tijd');
});
