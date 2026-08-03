<?php

/*
|--------------------------------------------------------------------------
| Boss registry contract
|--------------------------------------------------------------------------
|
| resources/js/space-classic/bosses.json is the single source of truth for
| the named bosses in the editie-klassieker. The JS engine and Vitest cover
| the behaviour; here we guard the asset contract: every boss has a sprite
| on disk and the blade can build its sprite map.
|
*/

function bossRegistry(): array
{
    return json_decode(file_get_contents(resource_path('js/space-classic/bosses.json')), true);
}

it('has nineteen bosses: every deelnemer plus Darth Arti', function () {
    $names = array_column(bossRegistry(), 'name');

    expect($names)->toHaveCount(19)
        ->and($names)->toContain('Darth Arti')
        ->and($names)->toContain('Kaasbal')
        ->and($names)->toContain('Anne (BlueRaven)')
        ->and($names)->toContain('Thomas (10ft_T)');
});

it('ships a sprite for every boss', function () {
    foreach (bossRegistry() as $boss) {
        expect(is_file(public_path("images/game/bosses/{$boss['key']}.png")))
            ->toBeTrue("missing boss sprite: {$boss['key']}");
    }
});

it('keeps keys url- and filename-safe', function () {
    foreach (bossRegistry() as $boss) {
        expect($boss['key'])->toMatch('/^[a-z0-9\-]+$/');
    }
});
