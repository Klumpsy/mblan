<?php

use function Pest\Laravel\get;

it('serves the Arti in Space game at /spel', function () {
    get('/spel')
        ->assertOk()
        ->assertSee('spaceClassic', escape: false)
        ->assertSee('Arti en de boer, de ruimte in');
});
