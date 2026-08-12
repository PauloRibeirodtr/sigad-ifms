<?php

use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeCategoria;
use App\Models\AtividadeMovimentacao;
use App\Models\Pit;
use App\Models\PlanoTrabalho;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-03 10:00:00'));
});

afterEach(function () {
    $this->travelBack();
});

it('requires an authenticated verified user without a pending password change', function () {
    $this->get(route('reports.index'))->assertRedirect(route('login'));

    $unverifiedUser = User::factory()->unverified()->create();

    $this->actingAs($unverifiedUser)
        ->get(route('reports.index'))
        ->assertRedirect(route('verification.notice'));
});

it('shows all PITs and PATs without requiring a date search', function () {
    $user = User::factory()->create();
    $pit = Pit::factory()->for($user)->create(['ano' => 2026, 'semestre' => 1]);
    PlanoTrabalho::factory()->for($pit)->create(['nome' => 'PAT disponível']);

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertSuccessful()
        ->assertSee('2026.1')
        ->assertSee('PAT disponível');
});

it('ignores obsolete date filters and keeps the PIT navigation available', function () {
    $user = User::factory()->create();
    $pit = Pit::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('reports.index', [
            'data_inicial' => '2026-07-31',
            'data_final' => '2026-07-01',
        ]))->assertSuccessful()->assertSee($pit->nome);
});

it('lists all owned PITs and hides another users PITs and PATs', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $olderPit = Pit::factory()->for($user)->create(['ano' => 2025, 'semestre' => 2]);
    $newerPit = Pit::factory()->for($user)->create(['ano' => 2026, 'semestre' => 1]);
    $foreignPit = Pit::factory()->for($otherUser)->create(['ano' => 2030, 'semestre' => 1]);
    PlanoTrabalho::factory()->for($olderPit)->create(['nome' => 'PAT anterior']);
    PlanoTrabalho::factory()->for($newerPit)->create(['nome' => 'PAT recente']);
    PlanoTrabalho::factory()->for($foreignPit)->create(['nome' => 'PAT alheio']);

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertSuccessful()
        ->assertSeeInOrder(['PAT recente', 'PAT anterior'])
        ->assertDontSee('PAT alheio');
});

it('shows every registered PIT on the report page without pagination', function () {
    $user = User::factory()->create();
    Pit::factory()->count(10)->for($user)->sequence(fn ($sequence) => [
        'ano' => 2016 + $sequence->index,
        'semestre' => 1,
    ])->create();

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertSuccessful()
        ->assertSee('2016.1')
        ->assertSee('2025.1')
        ->assertDontSee('page=2', false);
});

it('blocks reports for another users PAT and allows a partial active report', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $foreignPlan = PlanoTrabalho::factory()->for(Pit::factory()->ended()->for($otherUser))->create();
    $activePlan = PlanoTrabalho::factory()->for(Pit::factory()->inProgress()->for($user))->create();

    $this->actingAs($user)
        ->get(route('reports.show', $foreignPlan))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('reports.show', $activePlan))
        ->assertSuccessful();
});

it('keeps administrators restricted to their own functional reports', function () {
    $administrator = User::factory()->administrator()->create();
    $otherUser = User::factory()->create();
    $administratorPlan = PlanoTrabalho::factory()->for(Pit::factory()->ended()->for($administrator))->create([
        'nome' => 'Relatório do administrador',
    ]);
    $foreignPlan = PlanoTrabalho::factory()->for(Pit::factory()->ended()->for($otherUser))->create([
        'nome' => 'Relatório funcional alheio',
    ]);

    $this->actingAs($administrator)
        ->get(route('reports.index'))
        ->assertSuccessful()
        ->assertSee('Relatório do administrador')
        ->assertDontSee('Relatório funcional alheio');

    $this->actingAs($administrator)
        ->get(route('reports.show', $administratorPlan))
        ->assertSuccessful();

    $this->actingAs($administrator)
        ->get(route('reports.show', $foreignPlan))
        ->assertForbidden();
});

it('generates one plan report with totals categories and chronological movement trail', function () {
    $user = User::factory()->create(['name' => 'Maria Relatora']);
    $pit = Pit::factory()->for($user)->create([
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-07-31',
    ]);
    $plan = PlanoTrabalho::factory()->for($pit)->create(['nome' => 'PAT Anual Individual']);
    $administrativeCategory = AtividadeCategoria::factory()->for($user)->create(['nome' => 'Administrativo']);
    $teachingCategory = AtividadeCategoria::factory()->for($user)->create(['nome' => 'Ensino']);

    $laterActivity = Atividade::factory()->for($user)->create([
        'plano_trabalho_id' => $plan->id,
        'categoria_id' => $teachingCategory->id,
        'titulo' => 'ATIVIDADE POSTERIOR',
        'data_atividade' => '2026-03-10',
        'status' => AtividadeStatus::Cancelada,
    ]);
    $laterActivity->movimentacoes()->sole()->update([
        'data_movimentacao' => '2026-03-10',
        'descricao' => 'MOVIMENTO DA ATIVIDADE POSTERIOR',
        'status' => AtividadeStatus::Cancelada,
        'minutos_trabalhados' => 30,
    ]);

    $earlierActivity = Atividade::factory()->for($user)->create([
        'plano_trabalho_id' => $plan->id,
        'categoria_id' => $administrativeCategory->id,
        'titulo' => 'ATIVIDADE ANTERIOR',
        'data_atividade' => '2026-02-01',
        'status' => AtividadeStatus::Concluida,
    ]);
    $earlierActivity->movimentacoes()->sole()->update([
        'data_movimentacao' => '2026-02-01',
        'descricao' => 'MOVIMENTO MAIS ANTIGO',
        'status' => AtividadeStatus::Aberta,
        'minutos_trabalhados' => null,
    ]);
    AtividadeMovimentacao::factory()->for($earlierActivity)->create([
        'data_movimentacao' => '2026-02-05',
        'descricao' => 'MOVIMENTO MAIS NOVO',
        'status' => AtividadeStatus::Concluida,
        'minutos_trabalhados' => 90,
    ]);
    $otherPlan = PlanoTrabalho::factory()->for($pit)->create(['nome' => 'PAT que não integra este relatório']);
    $otherCategory = AtividadeCategoria::factory()->for($user)->create(['nome' => 'Extensão']);
    Atividade::factory()->for($user)->create([
        'plano_trabalho_id' => $otherPlan->id,
        'categoria_id' => $otherCategory->id,
        'titulo' => 'ATIVIDADE DE OUTRO PLANO',
        'data_atividade' => $otherPlan->data_inicial,
    ]);

    $response = $this->actingAs($user)
        ->get(route('reports.show', $plan));

    $response
        ->assertSuccessful()
        ->assertSee('PAT Anual Individual')
        ->assertSee('Maria Relatora')
        ->assertSee('2')
        ->assertSee('3')
        ->assertSee('120 min')
        ->assertSee('Tempo não informado')
        ->assertSee('Administrativo')
        ->assertSee('Ensino')
        ->assertSee('Imprimir relatório')
        ->assertSee('report-document', false)
        ->assertDontSee('ATIVIDADE DE OUTRO PLANO')
        ->assertSeeInOrder([
            'ATIVIDADE ANTERIOR',
            'MOVIMENTO MAIS ANTIGO',
            'MOVIMENTO MAIS NOVO',
            'ATIVIDADE POSTERIOR',
            'MOVIMENTO DA ATIVIDADE POSTERIOR',
        ]);

    expect($response['resumo'])->toBe([
        'atividades' => 2,
        'movimentacoes' => 3,
        'minutos' => 120,
        'sem_tempo' => 1,
        'concluidas' => 1,
        'canceladas' => 1,
    ]);
});

it('uses creation and id as explicit tie breakers for movements on the same date', function () {
    $user = User::factory()->create();
    $plan = PlanoTrabalho::factory()->for(Pit::factory()->ended()->for($user))->create();
    $category = AtividadeCategoria::factory()->for($user)->create();
    $activity = Atividade::factory()->for($user)->create([
        'plano_trabalho_id' => $plan->id,
        'categoria_id' => $category->id,
        'data_atividade' => $plan->data_inicial,
    ]);
    $firstMovement = $activity->movimentacoes()->sole();
    $firstMovement->update([
        'data_movimentacao' => $plan->data_inicial,
        'descricao' => 'DESEMPATE PRIMEIRO',
    ]);
    $firstMovement->forceFill(['created_at' => '2026-01-01 08:00:00'])->save();
    AtividadeMovimentacao::factory()->for($activity)->create([
        'data_movimentacao' => $plan->data_inicial,
        'descricao' => 'DESEMPATE SEGUNDO',
        'created_at' => '2026-01-01 09:00:00',
    ]);

    $this->actingAs($user)
        ->get(route('reports.show', $plan))
        ->assertSuccessful()
        ->assertSeeInOrder(['DESEMPATE PRIMEIRO', 'DESEMPATE SEGUNDO']);
});
