<?php

use App\Enums\PlanoTrabalhoStatus;
use App\Models\Atividade;
use App\Models\AtividadeCategoria;
use App\Models\AtividadeMovimentacao;
use App\Models\Pit;
use App\Models\PlanoTrabalho;
use App\Models\User;
use Carbon\CarbonImmutable;

it('creates a PIT whose name is derived from year and semester', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('pits.store'), [
        'ano' => 2026,
        'semestre' => 1,
        'data_inicial' => '2026-02-01',
        'data_final' => '2026-07-31',
    ])->assertRedirect();

    $pit = Pit::query()->sole();

    expect($pit->nome)->toBe('2026.1')
        ->and($pit->user_id)->toBe($user->id);
});

it('allows the same semester more than once when periods do not overlap', function () {
    $user = User::factory()->create();
    Pit::factory()->for($user)->create([
        'ano' => 2026,
        'semestre' => 1,
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-03-31',
    ]);

    $this->actingAs($user)->post(route('pits.store'), [
        'ano' => 2026,
        'semestre' => 1,
        'data_inicial' => '2026-04-01',
        'data_final' => '2026-07-31',
    ])->assertRedirect();

    expect(Pit::query()->count())->toBe(2);
});

it('rejects inclusive period overlaps for the same user', function () {
    $user = User::factory()->create();
    Pit::factory()->for($user)->create([
        'data_inicial' => '2026-02-01',
        'data_final' => '2026-07-31',
    ]);

    $this->actingAs($user)->post(route('pits.store'), [
        'ano' => 2026,
        'semestre' => 2,
        'data_inicial' => '2026-07-31',
        'data_final' => '2026-12-20',
    ])->assertSessionHasErrors('data_inicial');
});

it('allows another user to use the same period', function () {
    $period = ['data_inicial' => '2026-02-01', 'data_final' => '2026-07-31'];
    Pit::factory()->for(User::factory())->create($period);
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('pits.store'), [
        'ano' => 2026,
        'semestre' => 1,
        ...$period,
    ])->assertRedirect();

    expect(Pit::query()->count())->toBe(2);
});

it('calculates PIT status with inclusive boundaries', function () {
    $pit = Pit::factory()->create([
        'data_inicial' => '2026-02-01',
        'data_final' => '2026-07-31',
    ]);

    $this->travelTo(CarbonImmutable::parse('2026-01-31'));
    expect($pit->status)->toBe(PlanoTrabalhoStatus::Aguardando);
    $this->travelTo(CarbonImmutable::parse('2026-02-01'));
    expect($pit->status)->toBe(PlanoTrabalhoStatus::EmAndamento);
    $this->travelTo(CarbonImmutable::parse('2026-08-01'));
    expect($pit->status)->toBe(PlanoTrabalhoStatus::Encerrado);
    $this->travelBack();
});

it('allows reducing a PIT period when all records remain inside it', function () {
    $user = User::factory()->create();
    $pit = Pit::factory()->for($user)->create([
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-12-31',
    ]);

    $this->actingAs($user)->put(route('pits.update', $pit), [
        'ano' => 2026,
        'semestre' => 1,
        'data_inicial' => '2026-02-01',
        'data_final' => '2026-07-31',
    ])->assertRedirectToRoute('pits.show', $pit);

    expect($pit->refresh()->data_inicial->toDateString())->toBe('2026-02-01')
        ->and($pit->data_final->toDateString())->toBe('2026-07-31');
});

it('rejects a PIT reduction that excludes an activity or movement', function () {
    $user = User::factory()->create();
    $pit = Pit::factory()->for($user)->create([
        'data_inicial' => '2026-01-01',
        'data_final' => '2026-12-31',
    ]);
    $plan = PlanoTrabalho::factory()->for($pit)->create();
    $category = AtividadeCategoria::factory()->for($user)->create();
    $activity = Atividade::factory()->for($user)->for($plan, 'planoTrabalho')->for($category, 'categoria')->create([
        'data_atividade' => '2026-02-01',
    ]);
    AtividadeMovimentacao::factory()->for($activity)->create(['data_movimentacao' => '2026-11-01']);

    $this->actingAs($user)->put(route('pits.update', $pit), [
        'ano' => 2026,
        'semestre' => 1,
        'data_inicial' => '2026-03-01',
        'data_final' => '2026-10-31',
    ])->assertSessionHasErrors(['data_inicial', 'data_final']);
});

it('restricts PIT access by ownership and deletion while PATs exist', function () {
    $owner = User::factory()->create();
    $pit = Pit::factory()->for($owner)->create();
    PlanoTrabalho::factory()->for($pit)->create();
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)->get(route('pits.show', $pit))->assertForbidden();
    $this->actingAs($owner)->delete(route('pits.destroy', $pit))->assertForbidden();
    expect(Pit::query()->whereKey($pit)->exists())->toBeTrue();
});
