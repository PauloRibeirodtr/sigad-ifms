<?php

use App\Enums\AtividadeIndicador;
use App\Enums\AtividadePrioridade;
use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeCategoria;
use App\Models\Pit;
use App\Models\PlanoTrabalho;
use App\Models\User;
use Carbon\CarbonImmutable;

function indicatorContext(): array
{
    $user = User::factory()->create();
    $pit = Pit::factory()->for($user)->create([
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-12-31',
    ]);
    $plan = PlanoTrabalho::factory()->for($pit)->create(['nome' => 'PAT de acompanhamento']);
    $category = AtividadeCategoria::factory()->for($user)->create(['nome' => 'Colegiado']);

    return [$user, $plan, $category];
}

function createIndicatorActivity(
    User $user,
    PlanoTrabalho $plan,
    AtividadeCategoria $category,
    array $attributes = [],
    string $lastMovementDate = '2026-08-01',
): Atividade {
    $activity = Atividade::factory()
        ->for($user)
        ->for($plan, 'planoTrabalho')
        ->for($category, 'categoria')
        ->create(array_merge([
            'data_atividade' => '2026-07-01',
            'status' => AtividadeStatus::EmAndamento,
            'prioridade' => AtividadePrioridade::Normal,
            'prazo' => null,
        ], $attributes));

    $activity->movimentacoes()->sole()->update([
        'data_movimentacao' => $lastMovementDate,
        'status' => $activity->status,
        'aguardando_por' => $activity->aguardando_por,
        'aguardando_descricao' => $activity->aguardando_descricao,
    ]);

    return $activity;
}

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-03 12:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

it('classifies overdue activities only when an active deadline is before today', function () {
    [$user, $plan, $category] = indicatorContext();
    $overdue = createIndicatorActivity($user, $plan, $category, ['titulo' => 'Atrasada', 'prazo' => '2026-08-02']);
    createIndicatorActivity($user, $plan, $category, ['titulo' => 'Vence hoje', 'prazo' => '2026-08-03']);
    createIndicatorActivity($user, $plan, $category, ['titulo' => 'Sem prazo']);
    createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Concluída com prazo antigo',
        'prazo' => '2026-08-01',
        'status' => AtividadeStatus::Concluida,
    ]);
    createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Cancelada com prazo antigo',
        'prazo' => '2026-08-01',
        'status' => AtividadeStatus::Cancelada,
    ]);

    expect(Atividade::query()->overdue()->pluck('id')->all())->toBe([$overdue->id])
        ->and($overdue->isOverdue())->toBeTrue();
});

it('classifies ten full days without movement while the deadline remains valid', function () {
    [$user, $plan, $category] = indicatorContext();
    $exactlyTenDays = createIndicatorActivity($user, $plan, $category, ['titulo' => 'Dez dias'], '2026-07-24');
    $deadlineToday = createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Prazo hoje',
        'prazo' => '2026-08-03',
    ], '2026-07-20');
    createIndicatorActivity($user, $plan, $category, ['titulo' => 'Nove dias'], '2026-07-25');
    createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Já atrasada',
        'prazo' => '2026-08-02',
    ], '2026-07-20');
    createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Concluída antiga',
        'status' => AtividadeStatus::Concluida,
    ], '2026-07-20');

    $activities = Atividade::query()->withLatestMovementDate()->withoutRecentUpdate()->get();

    expect($activities->pluck('id')->sort()->values()->all())
        ->toBe(collect([$exactlyTenDays->id, $deadlineToday->id])->sort()->values()->all())
        ->and($activities->firstWhere('id', $exactlyTenDays->id)->isWithoutRecentUpdate())->toBeTrue();
});

it('classifies an old open activity without movements as lacking an update', function () {
    [$user, $plan, $category] = indicatorContext();
    $activity = Atividade::factory()
        ->for($user)
        ->for($plan, 'planoTrabalho')
        ->for($category, 'categoria')
        ->create([
            'data_atividade' => '2026-07-24',
            'status' => AtividadeStatus::Aberta,
            'prazo' => null,
        ]);
    $activity->movimentacoes()->delete();

    $activities = Atividade::query()->withLatestMovementDate()->withoutRecentUpdate()->get();

    expect($activities->pluck('id')->all())->toBe([$activity->id])
        ->and($activities->sole()->isWithoutRecentUpdate())->toBeTrue();
});

it('keeps urgent and waiting indicators restricted to operational states', function () {
    [$user, $plan, $category] = indicatorContext();
    $urgent = createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Urgente ativa',
        'prioridade' => AtividadePrioridade::Urgente,
    ]);
    $waiting = createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Aguardando retorno',
        'status' => AtividadeStatus::Aguardando,
    ]);
    createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Urgente concluída',
        'prioridade' => AtividadePrioridade::Urgente,
        'status' => AtividadeStatus::Concluida,
    ]);

    expect(Atividade::query()->forIndicator(AtividadeIndicador::Urgentes)->pluck('id')->all())->toBe([$urgent->id])
        ->and(Atividade::query()->forIndicator(AtividadeIndicador::Aguardando)->pluck('id')->all())->toBe([$waiting->id]);
});

it('shows dashboard indicator counts only for the authenticated user', function () {
    [$user, $plan, $category] = indicatorContext();
    createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Minha atrasada urgente',
        'prazo' => '2026-08-01',
        'prioridade' => AtividadePrioridade::Urgente,
    ], '2026-08-01');
    createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Minha aguardando antiga',
        'status' => AtividadeStatus::Aguardando,
    ], '2026-07-20');
    $otherUser = User::factory()->create();
    $otherPit = Pit::factory()->for($otherUser)->create([
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-12-31',
    ]);
    $otherPlan = PlanoTrabalho::factory()->for($otherPit)->create();
    $otherCategory = AtividadeCategoria::factory()->for($otherUser)->create();
    createIndicatorActivity($otherUser, $otherPlan, $otherCategory, [
        'titulo' => 'Indicador alheio',
        'prazo' => '2026-08-01',
    ], '2026-07-20');

    $response = $this->actingAs($user)->get(route('dashboard'))->assertSuccessful();

    expect($response->viewData('pits')->sole()->planosTrabalho->sum('atividades_count'))->toBe(2);
});

it('filters a plan activity list by indicator and exposes latest movement date', function () {
    [$user, $plan, $category] = indicatorContext();
    $overdue = createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Atividade atrasada visível',
        'prazo' => '2026-08-01',
    ], '2026-07-30');
    createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Atividade dentro do prazo',
        'prazo' => '2026-08-10',
    ], '2026-08-01');

    $response = $this->actingAs($user)
        ->get(route('plans.activities.index', ['plano' => $plan, 'indicador' => 'atrasadas']))
        ->assertSuccessful()
        ->assertSee($overdue->titulo)
        ->assertSee('30/07/2026')
        ->assertDontSee('Atividade dentro do prazo');

    expect($response->viewData('indicator'))->toBe(AtividadeIndicador::Atrasadas)
        ->and($response->viewData('indicatorCounts')['atrasadas'])->toBe(1);
});

it('searches activities by title description requester or category', function (string $search, string $expectedTitle) {
    [$user, $plan, $category] = indicatorContext();
    createIndicatorActivity($user, $plan, $category, [
        'titulo' => 'Reunião pedagógica',
        'descricao' => 'Análise curricular especial',
        'solicitante' => 'Direção de Ensino',
    ]);
    $otherCategory = AtividadeCategoria::factory()->for($user)->create(['nome' => 'Atendimento discente']);
    createIndicatorActivity($user, $plan, $otherCategory, ['titulo' => 'Orientação individual']);

    $this->actingAs($user)
        ->get(route('plans.activities.index', ['plano' => $plan, 'busca' => $search]))
        ->assertSuccessful()
        ->assertSee($expectedTitle);
})->with([
    'title' => ['pedagógica', 'Reunião pedagógica'],
    'description' => ['curricular', 'Reunião pedagógica'],
    'requester' => ['Direção', 'Reunião pedagógica'],
    'category' => ['discente', 'Orientação individual'],
]);

it('filters and paginates plan shortcuts from the activity overview', function () {
    $user = User::factory()->create();

    foreach (range(1, 10) as $number) {
        $pit = Pit::factory()->for($user)->create([
            'ano' => 2020 + $number,
            'semestre' => 1,
            'data_inicial' => '2026-01-01',
            'data_final' => '2026-12-31',
        ]);
        $plan = PlanoTrabalho::factory()->for($pit)->create(['nome' => sprintf('PAT indicador %02d', $number)]);
        $category = AtividadeCategoria::factory()->for($user)->create();
        createIndicatorActivity($user, $plan, $category, [
            'titulo' => 'Demanda atrasada',
            'prazo' => '2026-08-01',
        ]);
    }

    $this->actingAs($user)
        ->get(route('activities.overview', ['indicador' => 'atrasadas']))
        ->assertSuccessful()
        ->assertSee('Exibindo somente PATs')
        ->assertSee('page=2', false);
});

it('keeps indicator plan overview isolated from other users', function () {
    [$user, $plan, $category] = indicatorContext();
    createIndicatorActivity($user, $plan, $category, ['prazo' => '2026-08-01']);
    $otherUser = User::factory()->create();
    $foreignPit = Pit::factory()->for($otherUser)->create([
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-12-31',
    ]);
    $foreignPlan = PlanoTrabalho::factory()->for($foreignPit)->create(['nome' => 'PAT alheio atrasado']);
    $foreignCategory = AtividadeCategoria::factory()->for($otherUser)->create();
    createIndicatorActivity($otherUser, $foreignPlan, $foreignCategory, ['prazo' => '2026-08-01']);

    $this->actingAs($user)
        ->get(route('activities.overview', ['indicador' => 'atrasadas']))
        ->assertSuccessful()
        ->assertSee($plan->nome)
        ->assertDontSee('PAT alheio atrasado');
});

it('keeps plan activity queries paginated when using indicators', function () {
    [$user, $plan, $category] = indicatorContext();

    foreach (range(1, 11) as $number) {
        createIndicatorActivity($user, $plan, $category, [
            'titulo' => 'Atividade atrasada '.$number,
            'prazo' => '2026-08-01',
        ]);
    }

    $this->actingAs($user)
        ->get(route('plans.activities.index', ['plano' => $plan, 'indicador' => 'atrasadas']))
        ->assertSuccessful()
        ->assertSee('page=2', false);
});
