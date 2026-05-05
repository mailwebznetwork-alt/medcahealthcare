<?php

it('renders the public marketing shell with Medca chrome', function () {
    $this->get('/')->assertSuccessful()
        ->assertSee(config('medca.top_bar_claim'), false)
        ->assertSee(config('medca.brand_name'), false)
        ->assertSee('medca-logo.png', false)
        ->assertSee('medca-public-surface', false);
});
