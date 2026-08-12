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
        ->assertSeeText('Meus PITs')
        ->assertDontSeeText('Resumo dos seus planos')
        ->assertDontSeeText('Atividades que precisam de atenção')
        ->assertSee('Nenhum PIT cadastrado')
        ->assertDontSee('PrismaBet');
});

it('shows only the PIT section on the PIT page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pits.index'))
        ->assertSuccessful()
        ->assertSeeText('Meus PITs')
        ->assertDontSeeText('Resumo dos seus planos')
        ->assertDontSeeText('Atividades que precisam de atenção');
});
