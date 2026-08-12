<?php

use App\Models\Pit;
use App\Models\PlanoTrabalho;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

it('creates a trimmed PAT inside an owned PIT', function () {
    $user = User::factory()->create();
    $pit = Pit::factory()->for($user)->create();
    $otherPit = Pit::factory()->create();

    $this->actingAs($user)
        ->post(route('pits.plans.store', $pit), [
            'nome' => '  PAT de Ensino  ',
            'descricao' => '  Planejamento das atividades.  ',
            'pit_id' => $otherPit->id,
        ])->assertRedirect();

    $plan = PlanoTrabalho::query()->sole();

    expect($plan->nome)->toBe('PAT de Ensino')
        ->and($plan->descricao)->toBe('Planejamento das atividades.')
        ->and($plan->pit_id)->toBe($pit->id);
});

it('requires a PAT name', function () {
    $user = User::factory()->create();
    $pit = Pit::factory()->for($user)->create();

    $this->actingAs($user)
        ->from(route('pits.plans.create', $pit))
        ->post(route('pits.plans.store', $pit), ['nome' => ''])
        ->assertSessionHasErrors('nome');

    expect(PlanoTrabalho::query()->count())->toBe(0);
});

it('inherits its period and status from the PIT without duplicating columns', function () {
    $pit = Pit::factory()->inProgress()->create();
    $plan = PlanoTrabalho::factory()->for($pit)->create();

    expect($plan->data_inicial->toDateString())->toBe($pit->data_inicial->toDateString())
        ->and($plan->data_final->toDateString())->toBe($pit->data_final->toDateString())
        ->and($plan->status)->toBe($pit->status)
        ->and(Schema::hasColumn('planos_trabalho', 'data_inicial'))->toBeFalse()
        ->and(Schema::hasColumn('planos_trabalho', 'data_final'))->toBeFalse()
        ->and(Schema::hasColumn('planos_trabalho', 'user_id'))->toBeFalse();
});

it('shows only the authenticated users PITs and PATs on the dashboard', function () {
    $user = User::factory()->create();
    $ownedPit = Pit::factory()->for($user)->create();
    $foreignPit = Pit::factory()->create();
    PlanoTrabalho::factory()->for($ownedPit)->create(['nome' => 'Meu PAT']);
    PlanoTrabalho::factory()->for($foreignPit)->create(['nome' => 'PAT alheio']);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee($ownedPit->nome)
        ->assertDontSee($foreignPit->nome)
        ->assertDontSee('PAT alheio');
});

it('keeps administrators restricted to their own PITs and PATs', function () {
    $administrator = User::factory()->administrator()->create();
    $ownedPit = Pit::factory()->for($administrator)->create();
    $foreignPit = Pit::factory()->create();
    PlanoTrabalho::factory()->for($ownedPit)->create(['nome' => 'PAT do administrador']);
    PlanoTrabalho::factory()->for($foreignPit)->create(['nome' => 'PAT do servidor']);

    $this->actingAs($administrator)->get(route('pits.show', $ownedPit))
        ->assertSuccessful()
        ->assertSee('PAT do administrador')
        ->assertDontSee('PAT do servidor');
});

it('shows an owned PAT including one from an ended PIT', function () {
    $user = User::factory()->create();
    $pit = Pit::factory()->ended()->for($user)->create();
    $plan = PlanoTrabalho::factory()->for($pit)->create(['nome' => 'PAT encerrado consultável']);

    $this->actingAs($user)->get(route('plans.show', $plan))
        ->assertSuccessful()
        ->assertSee('PAT encerrado consultável')
        ->assertSee('Encerrado');
});

it('blocks creating or accessing a PAT through another users PIT', function () {
    $user = User::factory()->create();
    $foreignPit = Pit::factory()->create();
    $foreignPlan = PlanoTrabalho::factory()->for($foreignPit)->create();

    $this->actingAs($user)->get(route('pits.plans.create', $foreignPit))->assertForbidden();
    $this->actingAs($user)->post(route('pits.plans.store', $foreignPit), ['nome' => 'Tentativa'])->assertForbidden();
    $this->actingAs($user)->get(route('plans.show', $foreignPlan))->assertForbidden();
    $this->actingAs($user)->get(route('plans.edit', $foreignPlan))->assertForbidden();
});

it('updates only PAT data without accepting a PIT reassignment', function () {
    $user = User::factory()->create();
    $pit = Pit::factory()->for($user)->create();
    $otherPit = Pit::factory()->for($user)->create();
    $plan = PlanoTrabalho::factory()->for($pit)->create(['nome' => 'PAT original']);

    $this->actingAs($user)->put(route('plans.update', $plan), [
        'nome' => '  PAT atualizado  ',
        'descricao' => '  Nova descrição.  ',
        'pit_id' => $otherPit->id,
    ])->assertRedirectToRoute('plans.show', $plan);

    expect($plan->refresh()->nome)->toBe('PAT atualizado')
        ->and($plan->descricao)->toBe('Nova descrição.')
        ->and($plan->pit_id)->toBe($pit->id);
});

it('lists PATs inside their PIT', function () {
    $user = User::factory()->create();
    $pit = Pit::factory()->for($user)->create();
    PlanoTrabalho::factory()->count(7)->for($pit)->create();

    $this->actingAs($user)->get(route('pits.show', $pit))
        ->assertSuccessful()
        ->assertSee('7', false)
        ->assertSee('PATs deste PIT');
});

it('does not expose a PAT deletion route', function () {
    expect(collect(app('router')->getRoutes()->getRoutes())
        ->contains(fn ($route) => in_array('DELETE', $route->methods(), true) && str_starts_with($route->uri(), 'pats')))
        ->toBeFalse();
});
