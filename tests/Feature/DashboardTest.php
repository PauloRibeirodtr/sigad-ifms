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
        ->assertSeeText('Meus Planos de Trabalho')
        ->assertDontSeeText('Resumo dos seus planos')
        ->assertDontSeeText('Atividades que precisam de atenção')
        ->assertSee('Nenhum Plano de Trabalho cadastrado')
        ->assertDontSee('PrismaBet');
});

it('shows only the work plans section on the plans page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('plans.index'))
        ->assertSuccessful()
        ->assertSeeText('Meus Planos de Trabalho')
        ->assertDontSeeText('Resumo dos seus planos')
        ->assertDontSeeText('Atividades que precisam de atenção');
});
