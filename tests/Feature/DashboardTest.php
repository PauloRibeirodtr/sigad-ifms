<?php

use App\Models\User;

it('renders the dashboard for an authenticated user', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('test@example.com')
        ->assertSee('SIGAD')
        ->assertSee('Planos em andamento')
        ->assertSee('Nenhum Plano de Trabalho cadastrado')
        ->assertDontSee('PrismaBet');
});
