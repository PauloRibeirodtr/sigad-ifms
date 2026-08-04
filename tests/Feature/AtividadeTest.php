<?php

use App\Actions\Atividades\CreateAtividadeWithFirstMovement;
use App\Enums\AguardandoPor;
use App\Enums\AtividadePrioridade;
use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeCategoria;
use App\Models\AtividadeMovimentacao;
use App\Models\PlanoTrabalho;
use App\Models\User;
use Illuminate\Support\Facades\Event;

function activityPayload(AtividadeCategoria $category, array $overrides = []): array
{
    return array_merge([
        'categoria_id' => $category->id,
        'titulo' => '  Reunião de planejamento  ',
        'descricao' => '  Preparar a pauta da reunião.  ',
        'solicitante' => '  Coordenação  ',
        'data_atividade' => '2026-03-10',
        'prioridade' => AtividadePrioridade::Normal->value,
        'prazo' => '2026-08-15',
        'proxima_acao' => '  Enviar a pauta.  ',
        'data_movimentacao' => '2026-03-10',
        'movimentacao_descricao' => '  Atividade registrada.  ',
        'movimentacao_status' => AtividadeStatus::Aberta->value,
        'aguardando_por' => null,
        'aguardando_descricao' => null,
        'minutos_trabalhados' => 30,
    ], $overrides);
}

function activityContext(): array
{
    $user = User::factory()->create();
    $plan = PlanoTrabalho::factory()->for($user)->create([
        'data_inicial' => '2026-02-01',
        'data_final' => '2026-07-31',
    ]);
    $category = AtividadeCategoria::factory()->for($user)->create();

    return [$user, $plan, $category];
}

it('creates the activity and its first movement atomically', function () {
    [$user, $plan, $category] = activityContext();

    $this->actingAs($user)
        ->post(route('plans.activities.store', $plan), activityPayload($category, [
            'prioridade' => '',
            'data_movimentacao' => '',
        ]))
        ->assertRedirect();

    $activity = Atividade::query()->sole();
    $movement = AtividadeMovimentacao::query()->sole();

    expect($activity->user_id)->toBe($user->id)
        ->and($activity->plano_trabalho_id)->toBe($plan->id)
        ->and($activity->categoria_id)->toBe($category->id)
        ->and($activity->titulo)->toBe('Reunião de planejamento')
        ->and($activity->descricao)->toBe('Preparar a pauta da reunião.')
        ->and($activity->solicitante)->toBe('Coordenação')
        ->and($activity->prioridade)->toBe(AtividadePrioridade::Normal)
        ->and($activity->status)->toBe(AtividadeStatus::Aberta)
        ->and($activity->prazo->toDateString())->toBe('2026-08-15')
        ->and($movement->atividade_id)->toBe($activity->id)
        ->and($movement->data_movimentacao->toDateString())->toBe('2026-03-10')
        ->and($movement->descricao)->toBe('Atividade registrada.')
        ->and($movement->status)->toBe(AtividadeStatus::Aberta);
});

it('accepts a concluded activity as its first state', function () {
    [$user, $plan, $category] = activityContext();

    $this->actingAs($user)
        ->post(route('plans.activities.store', $plan), activityPayload($category, [
            'movimentacao_status' => AtividadeStatus::Concluida->value,
        ]))
        ->assertRedirect();

    expect(Atividade::query()->sole()->status)->toBe(AtividadeStatus::Concluida)
        ->and(AtividadeMovimentacao::query()->sole()->status)->toBe(AtividadeStatus::Concluida);
});

it('requires waiting details and clears them for another state', function () {
    [$user, $plan, $category] = activityContext();

    $this->actingAs($user)
        ->post(route('plans.activities.store', $plan), activityPayload($category, [
            'movimentacao_status' => AtividadeStatus::Aguardando->value,
        ]))
        ->assertSessionHasErrors(['aguardando_por', 'aguardando_descricao']);

    $this->actingAs($user)
        ->post(route('plans.activities.store', $plan), activityPayload($category, [
            'movimentacao_status' => AtividadeStatus::Aberta->value,
            'aguardando_por' => AguardandoPor::Colegiado->value,
            'aguardando_descricao' => 'Colegiado do curso',
        ]))
        ->assertRedirect();

    $activity = Atividade::query()->sole();

    expect($activity->aguardando_por)->toBeNull()
        ->and($activity->aguardando_descricao)->toBeNull()
        ->and($activity->movimentacoes()->sole()->aguardando_por)->toBeNull();
});

it('rejects inactive or foreign categories', function () {
    [$user, $plan] = activityContext();
    $inactiveCategory = AtividadeCategoria::factory()->inactive()->for($user)->create();
    $foreignCategory = AtividadeCategoria::factory()->create();

    foreach ([$inactiveCategory, $foreignCategory] as $category) {
        $this->actingAs($user)
            ->post(route('plans.activities.store', $plan), activityPayload($category))
            ->assertSessionHasErrors('categoria_id');
    }

    expect(Atividade::query()->count())->toBe(0);
});

it('enforces activity and first movement dates within the plan period', function () {
    [$user, $plan, $category] = activityContext();

    $invalidDates = [
        ['data_atividade' => '2026-01-31', 'data_movimentacao' => '2026-03-10', 'field' => 'data_atividade'],
        ['data_atividade' => '2026-03-10', 'data_movimentacao' => '2026-03-09', 'field' => 'data_movimentacao'],
        ['data_atividade' => '2026-03-10', 'data_movimentacao' => '2026-08-01', 'field' => 'data_movimentacao'],
    ];

    foreach ($invalidDates as $dates) {
        $this->actingAs($user)
            ->post(route('plans.activities.store', $plan), activityPayload($category, $dates))
            ->assertSessionHasErrors($dates['field']);
    }

    expect(Atividade::query()->count())->toBe(0);
});

it('allows late registration in an ended plan when dates are within its period', function () {
    $user = User::factory()->create();
    $plan = PlanoTrabalho::factory()->ended()->for($user)->create([
        'data_inicial' => '2025-01-01',
        'data_final' => '2025-12-31',
    ]);
    $category = AtividadeCategoria::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('plans.activities.store', $plan), activityPayload($category, [
            'data_atividade' => '2025-06-10',
            'data_movimentacao' => '2025-06-12',
        ]))
        ->assertRedirect();

    expect(Atividade::query()->count())->toBe(1)
        ->and(AtividadeMovimentacao::query()->count())->toBe(1);
});

it('rejects zero worked minutes but allows a deadline after the plan period', function () {
    [$user, $plan, $category] = activityContext();

    $this->actingAs($user)
        ->post(route('plans.activities.store', $plan), activityPayload($category, [
            'minutos_trabalhados' => 0,
        ]))
        ->assertSessionHasErrors('minutos_trabalhados');

    $this->actingAs($user)
        ->post(route('plans.activities.store', $plan), activityPayload($category, [
            'prazo' => '2027-01-15',
        ]))
        ->assertRedirect();

    expect(Atividade::query()->sole()->prazo->toDateString())->toBe('2027-01-15');
});

it('lists and filters only activities from the selected owned plan', function () {
    [$user, $plan, $category] = activityContext();
    $otherPlan = PlanoTrabalho::factory()->for($user)->create();
    $otherUser = User::factory()->create();
    $visible = Atividade::factory()->for($user)->for($plan, 'planoTrabalho')->for($category, 'categoria')->create([
        'titulo' => 'Atividade urgente visível',
        'status' => AtividadeStatus::EmAndamento,
        'prioridade' => AtividadePrioridade::Urgente,
        'data_atividade' => '2026-03-10',
    ]);
    Atividade::factory()->for($user)->for($otherPlan, 'planoTrabalho')->create(['titulo' => 'Outro plano']);
    Atividade::factory()->for($otherUser)->create(['titulo' => 'Outro usuário']);

    $this->actingAs($user)
        ->get(route('plans.activities.index', [
            'plano' => $plan,
            'status' => AtividadeStatus::EmAndamento->value,
            'prioridade' => AtividadePrioridade::Urgente->value,
            'titulo' => 'urgente',
        ]))
        ->assertSuccessful()
        ->assertSee($visible->titulo)
        ->assertDontSee('Outro plano')
        ->assertDontSee('Outro usuário');
});

it('keeps administrators restricted to their own activities', function () {
    $administrator = User::factory()->administrator()->create();
    $plan = PlanoTrabalho::factory()->for($administrator)->create();
    Atividade::factory()->for($administrator)->for($plan, 'planoTrabalho')->create(['titulo' => 'Atividade administrativa própria']);
    Atividade::factory()->create(['titulo' => 'Atividade funcional alheia']);

    $this->actingAs($administrator)
        ->get(route('activities.overview'))
        ->assertSuccessful()
        ->assertSee($plan->nome)
        ->assertDontSee('Atividade funcional alheia');
});

it('prevents cross-plan nested access and foreign ownership access', function () {
    [$user, $plan, $category] = activityContext();
    $secondPlan = PlanoTrabalho::factory()->for($user)->create();
    $activity = Atividade::factory()->for($user)->for($plan, 'planoTrabalho')->for($category, 'categoria')->create([
        'data_atividade' => '2026-03-10',
    ]);
    $foreignActivity = Atividade::factory()->create();

    $this->actingAs($user)
        ->get(route('plans.activities.show', [$secondPlan, $activity]))
        ->assertNotFound();

    $this->actingAs($user)
        ->get(route('plans.activities.show', [$foreignActivity->planoTrabalho, $foreignActivity]))
        ->assertForbidden();
});

it('updates general data and permits retaining only the current inactive category', function () {
    [$user, $plan, $currentCategory] = activityContext();
    $currentCategory->update(['ativa' => false]);
    $otherInactiveCategory = AtividadeCategoria::factory()->inactive()->for($user)->create();
    $activeCategory = AtividadeCategoria::factory()->for($user)->create();
    $activity = Atividade::factory()->for($user)->for($plan, 'planoTrabalho')->for($currentCategory, 'categoria')->create([
        'data_atividade' => '2026-03-10',
    ]);

    $payload = [
        'categoria_id' => $currentCategory->id,
        'titulo' => '  Atividade revisada  ',
        'descricao' => '',
        'solicitante' => '',
        'data_atividade' => '2026-03-10',
        'prioridade' => AtividadePrioridade::Alta->value,
        'prazo' => '',
        'proxima_acao' => '',
    ];

    $this->actingAs($user)
        ->put(route('plans.activities.update', [$plan, $activity]), $payload)
        ->assertRedirect();

    expect($activity->refresh()->titulo)->toBe('Atividade revisada')
        ->and($activity->categoria_id)->toBe($currentCategory->id);

    $this->actingAs($user)
        ->put(route('plans.activities.update', [$plan, $activity]), array_merge($payload, [
            'categoria_id' => $otherInactiveCategory->id,
        ]))
        ->assertSessionHasErrors('categoria_id');

    $this->actingAs($user)
        ->put(route('plans.activities.update', [$plan, $activity]), array_merge($payload, [
            'categoria_id' => $activeCategory->id,
        ]))
        ->assertRedirect();

    expect($activity->refresh()->categoria_id)->toBe($activeCategory->id);
});

it('does not allow moving the activity date after its first movement', function () {
    [$user, $plan, $category] = activityContext();
    $activity = Atividade::factory()->for($user)->for($plan, 'planoTrabalho')->for($category, 'categoria')->create([
        'data_atividade' => '2026-03-10',
    ]);

    $this->actingAs($user)
        ->put(route('plans.activities.update', [$plan, $activity]), [
            'categoria_id' => $category->id,
            'titulo' => $activity->titulo,
            'data_atividade' => '2026-03-11',
            'prioridade' => AtividadePrioridade::Normal->value,
        ])
        ->assertSessionHasErrors('data_atividade');
});

it('rolls back the activity when creating its first movement fails', function () {
    [$user, $plan, $category] = activityContext();
    AtividadeMovimentacao::creating(function (): void {
        throw new RuntimeException('Falha simulada na movimentação.');
    });

    try {
        expect(fn () => app(CreateAtividadeWithFirstMovement::class)->execute(
            $user,
            $plan,
            activityPayload($category),
        ))->toThrow(RuntimeException::class);
    } finally {
        Event::forget('eloquent.creating: '.AtividadeMovimentacao::class);
    }

    expect(Atividade::query()->count())->toBe(0)
        ->and(AtividadeMovimentacao::query()->count())->toBe(0);
});

it('does not expose an activity deletion route', function () {
    expect(collect(app('router')->getRoutes()->getRoutes())
        ->contains(fn ($route) => in_array('DELETE', $route->methods(), true) && str_contains($route->uri(), 'atividades')))
        ->toBeFalse();
});
