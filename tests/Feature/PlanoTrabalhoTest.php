<?php

use App\Enums\PlanoTrabalhoStatus;
use App\Models\PlanoTrabalho;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;

it('creates a trimmed work plan for the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->post(route('plans.store'), [
            'nome' => '  Plano de Trabalho 2026  ',
            'descricao' => '  Planejamento anual das atividades.  ',
            'data_inicial' => '2026-02-01',
            'data_final' => '2026-07-31',
            'user_id' => $otherUser->id,
        ])->assertRedirect();

    $plan = PlanoTrabalho::query()->sole();

    expect($plan->nome)->toBe('Plano de Trabalho 2026')
        ->and($plan->descricao)->toBe('Planejamento anual das atividades.')
        ->and($plan->user_id)->toBe($user->id)
        ->and($plan->data_inicial->toDateString())->toBe('2026-02-01')
        ->and($plan->data_final->toDateString())->toBe('2026-07-31');
});

it('requires valid dates and rejects an end before the start', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('plans.create'))
        ->post(route('plans.store'), [
            'nome' => 'Plano inválido',
            'data_inicial' => '2026-08-10',
            'data_final' => '2026-08-09',
        ])->assertRedirect(route('plans.create'))
        ->assertSessionHasErrors('data_final');

    expect(PlanoTrabalho::query()->count())->toBe(0);
});

it('calculates status with inclusive start and end dates without storing it', function () {
    $plan = PlanoTrabalho::factory()->create([
        'data_inicial' => '2026-02-01',
        'data_final' => '2026-07-31',
    ]);

    $this->travelTo(CarbonImmutable::parse('2026-01-31 12:00:00'));
    expect($plan->status)->toBe(PlanoTrabalhoStatus::Aguardando);

    $this->travelTo(CarbonImmutable::parse('2026-02-01 12:00:00'));
    expect($plan->status)->toBe(PlanoTrabalhoStatus::EmAndamento);

    $this->travelTo(CarbonImmutable::parse('2026-07-31 23:59:59'));
    expect($plan->status)->toBe(PlanoTrabalhoStatus::EmAndamento);

    $this->travelTo(CarbonImmutable::parse('2026-08-01 00:00:00'));
    expect($plan->status)->toBe(PlanoTrabalhoStatus::Encerrado)
        ->and(Schema::hasColumn('planos_trabalho', 'status'))->toBeFalse();

    $this->travelBack();
});

it('shows only the authenticated users plans on the dashboard', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    PlanoTrabalho::factory()->for($user)->create(['nome' => 'Meu Plano']);
    PlanoTrabalho::factory()->for($otherUser)->create(['nome' => 'Plano Alheio']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Meu Plano')
        ->assertDontSee('Plano Alheio');
});

it('keeps administrators restricted to their own work plans', function () {
    $administrator = User::factory()->administrator()->create();
    $otherUser = User::factory()->create();
    PlanoTrabalho::factory()->for($administrator)->create(['nome' => 'Plano do Administrador']);
    PlanoTrabalho::factory()->for($otherUser)->create(['nome' => 'Plano do Servidor']);

    $this->actingAs($administrator)
        ->get(route('plans.index'))
        ->assertSuccessful()
        ->assertSee('Plano do Administrador')
        ->assertDontSee('Plano do Servidor');
});

it('shows an owned plan including an ended plan', function () {
    $user = User::factory()->create();
    $endedPlan = PlanoTrabalho::factory()->ended()->for($user)->create([
        'nome' => 'Plano Encerrado Consultável',
    ]);

    $this->actingAs($user)
        ->get(route('plans.show', $endedPlan))
        ->assertSuccessful()
        ->assertSee('Plano Encerrado Consultável')
        ->assertSee('Encerrado');
});

it('blocks viewing editing or updating another users plan', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $foreignPlan = PlanoTrabalho::factory()->for($otherUser)->create();

    $this->actingAs($user)
        ->get(route('plans.show', $foreignPlan))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('plans.edit', $foreignPlan))
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('plans.update', $foreignPlan), [
            'nome' => 'Tentativa',
            'data_inicial' => $foreignPlan->data_inicial->toDateString(),
            'data_final' => $foreignPlan->data_final->toDateString(),
        ])->assertForbidden();
});

it('rejects reducing either boundary of an existing period', function () {
    $user = User::factory()->create();
    $plan = PlanoTrabalho::factory()->for($user)->create([
        'nome' => 'Plano original',
        'data_inicial' => '2026-02-01',
        'data_final' => '2026-07-31',
    ]);

    $this->actingAs($user)
        ->from(route('plans.edit', $plan))
        ->put(route('plans.update', $plan), [
            'nome' => 'Plano reduzido no início',
            'data_inicial' => '2026-02-02',
            'data_final' => '2026-07-31',
        ])->assertRedirect(route('plans.edit', $plan))
        ->assertSessionHasErrors('data_inicial');

    $this->actingAs($user)
        ->from(route('plans.edit', $plan))
        ->put(route('plans.update', $plan), [
            'nome' => 'Plano reduzido no fim',
            'data_inicial' => '2026-02-01',
            'data_final' => '2026-07-30',
        ])->assertRedirect(route('plans.edit', $plan))
        ->assertSessionHasErrors('data_final');

    expect($plan->refresh()->nome)->toBe('Plano original')
        ->and($plan->data_inicial->toDateString())->toBe('2026-02-01')
        ->and($plan->data_final->toDateString())->toBe('2026-07-31');
});

it('allows keeping or expanding an existing period', function () {
    $user = User::factory()->create();
    $plan = PlanoTrabalho::factory()->for($user)->create([
        'data_inicial' => '2026-02-01',
        'data_final' => '2026-07-31',
    ]);

    $this->actingAs($user)
        ->put(route('plans.update', $plan), [
            'nome' => '  Plano ampliado  ',
            'descricao' => '  Período maior.  ',
            'data_inicial' => '2026-01-15',
            'data_final' => '2026-08-15',
            'user_id' => User::factory()->create()->id,
        ])->assertRedirectToRoute('plans.show', $plan);

    $plan->refresh();

    expect($plan->nome)->toBe('Plano ampliado')
        ->and($plan->descricao)->toBe('Período maior.')
        ->and($plan->user_id)->toBe($user->id)
        ->and($plan->data_inicial->toDateString())->toBe('2026-01-15')
        ->and($plan->data_final->toDateString())->toBe('2026-08-15');

    $this->actingAs($user)
        ->put(route('plans.update', $plan), [
            'nome' => 'Mesmo período',
            'data_inicial' => '2026-01-15',
            'data_final' => '2026-08-15',
        ])->assertRedirectToRoute('plans.show', $plan);
});

it('paginates work plan cards', function () {
    $user = User::factory()->create();
    PlanoTrabalho::factory()->count(7)->for($user)->create();

    $this->actingAs($user)
        ->get(route('plans.index'))
        ->assertSuccessful()
        ->assertSee('7 planos cadastrados')
        ->assertSee('page=2', false);
});

it('does not expose a work plan deletion route', function () {
    expect(collect(app('router')->getRoutes()->getRoutes())
        ->contains(fn ($route) => in_array('DELETE', $route->methods(), true) && str_starts_with($route->uri(), 'planos')))
        ->toBeFalse();
});
