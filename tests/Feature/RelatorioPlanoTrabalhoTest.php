<?php

use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeCategoria;
use App\Models\AtividadeMovimentacao;
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

it('shows the report search instructions before a period is informed', function () {
    $user = User::factory()->create();
    PlanoTrabalho::factory()->ended()->for($user)->create(['nome' => 'Plano ainda não pesquisado']);

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertSuccessful()
        ->assertSee('Informe um período para começar')
        ->assertDontSee('Plano ainda não pesquisado');
});

it('validates both dates and the chronological order of the search period', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('reports.index', ['data_inicial' => '2026-01-01']))
        ->assertSessionHasErrors('data_final');

    $this->actingAs($user)
        ->get(route('reports.index', [
            'data_inicial' => '2026-07-31',
            'data_final' => '2026-07-01',
        ]))->assertSessionHasErrors('data_final');
});

it('lists only owned plans finalized inside the inclusive selected period', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    PlanoTrabalho::factory()->for($user)->create([
        'nome' => 'Plano no limite inicial',
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-06-01',
    ]);
    PlanoTrabalho::factory()->for($user)->create([
        'nome' => 'Plano no limite final',
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-07-31',
    ]);
    PlanoTrabalho::factory()->for($user)->create([
        'nome' => 'Plano fora do período',
        'data_inicial' => '2025-01-01',
        'data_final' => '2026-05-31',
    ]);
    PlanoTrabalho::factory()->inProgress()->for($user)->create(['nome' => 'Plano ainda ativo']);
    PlanoTrabalho::factory()->for($otherUser)->create([
        'nome' => 'Plano encerrado alheio',
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-07-15',
    ]);

    $this->actingAs($user)
        ->get(route('reports.index', [
            'data_inicial' => '2026-06-01',
            'data_final' => '2026-07-31',
        ]))
        ->assertSuccessful()
        ->assertSeeInOrder(['Plano no limite final', 'Plano no limite inicial'])
        ->assertDontSee('Plano fora do período')
        ->assertDontSee('Plano ainda ativo')
        ->assertDontSee('Plano encerrado alheio');
});

it('keeps period filters in paginated report search results', function () {
    $user = User::factory()->create();
    PlanoTrabalho::factory()->count(10)->for($user)->create([
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-07-01',
    ]);

    $this->actingAs($user)
        ->get(route('reports.index', [
            'data_inicial' => '2026-06-01',
            'data_final' => '2026-07-31',
        ]))
        ->assertSuccessful()
        ->assertSee('page=2', false)
        ->assertSee('data_inicial=2026-06-01', false)
        ->assertSee('data_final=2026-07-31', false);
});

it('blocks reports for another users plan and for a plan not yet finalized', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $foreignPlan = PlanoTrabalho::factory()->ended()->for($otherUser)->create();
    $activePlan = PlanoTrabalho::factory()->inProgress()->for($user)->create();

    $this->actingAs($user)
        ->get(route('reports.show', $foreignPlan))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('reports.show', $activePlan))
        ->assertNotFound();
});

it('keeps administrators restricted to their own functional reports', function () {
    $administrator = User::factory()->administrator()->create();
    $otherUser = User::factory()->create();
    $administratorPlan = PlanoTrabalho::factory()->ended()->for($administrator)->create([
        'nome' => 'Relatório do administrador',
    ]);
    $foreignPlan = PlanoTrabalho::factory()->ended()->for($otherUser)->create([
        'nome' => 'Relatório funcional alheio',
    ]);

    $this->actingAs($administrator)
        ->get(route('reports.index', [
            'data_inicial' => today()->subYear()->toDateString(),
            'data_final' => today()->toDateString(),
        ]))
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
    $plan = PlanoTrabalho::factory()->for($user)->create([
        'nome' => 'Plano Anual Individual',
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-07-31',
    ]);
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
    $otherPlan = PlanoTrabalho::factory()->ended()->for($user)->create([
        'nome' => 'Plano que não integra este relatório',
    ]);
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
        ->assertSee('Plano Anual Individual')
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
    $plan = PlanoTrabalho::factory()->ended()->for($user)->create();
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
