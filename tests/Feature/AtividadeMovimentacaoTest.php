<?php

use App\Actions\Movimentacoes\CreateAtividadeMovimentacao;
use App\Enums\AguardandoPor;
use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeCategoria;
use App\Models\AtividadeMovimentacao;
use App\Models\Pit;
use App\Models\PlanoTrabalho;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function movementContext(): array
{
    $user = User::factory()->create();
    $pit = Pit::factory()->for($user)->create([
        'data_inicial' => '2026-02-01',
        'data_final' => '2026-07-31',
    ]);
    $plan = PlanoTrabalho::factory()->for($pit)->create();
    $category = AtividadeCategoria::factory()->for($user)->create();
    $activity = Atividade::factory()
        ->for($user)
        ->for($plan, 'planoTrabalho')
        ->for($category, 'categoria')
        ->create([
            'data_atividade' => '2026-03-10',
            'status' => AtividadeStatus::Aberta,
        ]);

    return [$user, $plan, $activity];
}

function movementPayload(array $overrides = []): array
{
    return array_merge([
        'data_movimentacao' => '2026-03-12',
        'descricao' => '  Documentação conferida e encaminhada.  ',
        'status' => AtividadeStatus::EmAndamento->value,
        'aguardando_por' => null,
        'aguardando_descricao' => null,
        'minutos_trabalhados' => 45,
    ], $overrides);
}

it('adds the first movement later and synchronizes the current activity state', function () {
    [$user, $plan, $activity] = movementContext();
    $activity->movimentacoes()->delete();

    $this->actingAs($user)
        ->post(route('plans.activities.movements.store', [$plan, $activity]), movementPayload([
            'status' => AtividadeStatus::Aguardando->value,
            'aguardando_por' => AguardandoPor::SetorInterno->value,
            'aguardando_descricao' => '  COGEA  ',
        ]))
        ->assertRedirectToRoute('plans.activities.show', [$plan, $activity]);

    $movement = $activity->movimentacoes()->latest('id')->firstOrFail();

    expect($movement->descricao)->toBe('Documentação conferida e encaminhada.')
        ->and($movement->status)->toBe(AtividadeStatus::Aguardando)
        ->and($movement->aguardando_por)->toBe(AguardandoPor::SetorInterno)
        ->and($movement->aguardando_descricao)->toBe('COGEA')
        ->and($movement->minutos_trabalhados)->toBe(45)
        ->and($activity->refresh()->status)->toBe(AtividadeStatus::Aguardando)
        ->and($activity->aguardando_por)->toBe(AguardandoPor::SetorInterno)
        ->and($activity->aguardando_descricao)->toBe('COGEA')
        ->and($activity->movimentacoes()->count())->toBe(1);
});

it('uses movement date then registration time then id as the explicit state order', function () {
    [$user, $plan, $activity] = movementContext();
    $this->freezeTime();

    $this->actingAs($user)->post(
        route('plans.activities.movements.store', [$plan, $activity]),
        movementPayload(['data_movimentacao' => '2026-03-15', 'status' => AtividadeStatus::Concluida->value]),
    )->assertRedirect();

    $this->actingAs($user)->post(
        route('plans.activities.movements.store', [$plan, $activity]),
        movementPayload(['data_movimentacao' => '2026-03-15', 'status' => AtividadeStatus::Cancelada->value]),
    )->assertRedirect();

    expect($activity->refresh()->status)->toBe(AtividadeStatus::Cancelada);

    $this->actingAs($user)->post(
        route('plans.activities.movements.store', [$plan, $activity]),
        movementPayload(['data_movimentacao' => '2026-03-14', 'status' => AtividadeStatus::Aberta->value]),
    )->assertRedirect();

    expect($activity->refresh()->status)->toBe(AtividadeStatus::Cancelada);
});

it('recalculates state after editing without promoting an older movement by updated at', function () {
    [$user, $plan, $activity] = movementContext();
    $older = AtividadeMovimentacao::factory()->for($activity)->create([
        'data_movimentacao' => '2026-03-12',
        'status' => AtividadeStatus::Concluida,
    ]);
    $latest = AtividadeMovimentacao::factory()->for($activity)->create([
        'data_movimentacao' => '2026-03-13',
        'status' => AtividadeStatus::Aguardando,
        'aguardando_por' => AguardandoPor::Colegiado,
        'aguardando_descricao' => 'Colegiado do curso',
    ]);

    $this->actingAs($user)
        ->put(route('plans.activities.movements.update', [$plan, $activity, $latest]), movementPayload([
            'data_movimentacao' => '2026-03-11',
            'status' => AtividadeStatus::Aberta->value,
        ]))
        ->assertRedirect();

    expect($activity->refresh()->status)->toBe(AtividadeStatus::Concluida)
        ->and($activity->aguardando_por)->toBeNull();

    $this->actingAs($user)
        ->put(route('plans.activities.movements.update', [$plan, $activity, $older]), movementPayload([
            'data_movimentacao' => '2026-03-12',
            'status' => AtividadeStatus::EmAndamento->value,
        ]))
        ->assertRedirect();

    expect($activity->refresh()->status)->toBe(AtividadeStatus::EmAndamento);
});

it('clears waiting fields whenever the movement status is not waiting', function () {
    [$user, $plan, $activity] = movementContext();

    $this->actingAs($user)
        ->post(route('plans.activities.movements.store', [$plan, $activity]), movementPayload([
            'status' => AtividadeStatus::Concluida->value,
            'aguardando_por' => AguardandoPor::Terceiro->value,
            'aguardando_descricao' => 'Valor malicioso',
        ]))
        ->assertRedirect();

    $movement = $activity->movimentacoes()->latest('id')->firstOrFail();

    expect($movement->aguardando_por)->toBeNull()
        ->and($movement->aguardando_descricao)->toBeNull()
        ->and($activity->refresh()->aguardando_por)->toBeNull();
});

it('validates movement dates and worked minutes', function (array $overrides, string $field) {
    [$user, $plan, $activity] = movementContext();

    $this->actingAs($user)
        ->post(route('plans.activities.movements.store', [$plan, $activity]), movementPayload($overrides))
        ->assertSessionHasErrors($field);

    expect($activity->movimentacoes()->count())->toBe(1);
})->with([
    'before activity' => [['data_movimentacao' => '2026-03-09'], 'data_movimentacao'],
    'after plan' => [['data_movimentacao' => '2026-08-01'], 'data_movimentacao'],
    'zero minutes' => [['minutos_trabalhados' => 0], 'minutos_trabalhados'],
]);

it('stores a movement attachment privately with a generated name', function () {
    Storage::fake('local');
    [$user, $plan, $activity] = movementContext();
    $attachment = UploadedFile::fake()->create('documentação.pdf', 100, 'application/pdf');

    $this->actingAs($user)
        ->post(route('plans.activities.movements.store', [$plan, $activity]), movementPayload([
            'anexo' => $attachment,
        ]))
        ->assertRedirect();

    $movement = $activity->movimentacoes()->latest('id')->firstOrFail();

    expect($movement->anexo_nome_original)->toBe('documentação.pdf')
        ->and($movement->anexo_path)->not->toContain('documentação.pdf')
        ->and($movement->anexo_path)->toStartWith('movimentacoes/'.$user->id.'/');
    Storage::disk('local')->assertExists($movement->anexo_path);
});

it('rejects invalid attachment extension mime type and size', function (UploadedFile $attachment) {
    Storage::fake('local');
    [$user, $plan, $activity] = movementContext();

    $this->actingAs($user)
        ->post(route('plans.activities.movements.store', [$plan, $activity]), movementPayload([
            'anexo' => $attachment,
        ]))
        ->assertSessionHasErrors('anexo');

    Storage::disk('local')->assertDirectoryEmpty('/');
})->with([
    'extension' => fn () => UploadedFile::fake()->create('programa.exe', 10, 'application/x-msdownload'),
    'mismatched content' => fn () => UploadedFile::fake()->image('imagem.txt'),
    'size' => fn () => UploadedFile::fake()->create('grande.pdf', 10241, 'application/pdf'),
]);

it('replaces and removes attachments without leaving the superseded file', function () {
    Storage::fake('local');
    [$user, $plan, $activity] = movementContext();
    $movement = $activity->movimentacoes()->sole();
    $oldAttachment = UploadedFile::fake()->create('antigo.pdf', 10, 'application/pdf');
    $oldPath = $oldAttachment->store('movimentacoes/'.$user->id, 'local');
    $movement->update(['anexo_path' => $oldPath, 'anexo_nome_original' => 'antigo.pdf']);

    $this->actingAs($user)
        ->put(route('plans.activities.movements.update', [$plan, $activity, $movement]), movementPayload([
            'data_movimentacao' => '2026-03-10',
            'anexo' => UploadedFile::fake()->create('novo.pdf', 10, 'application/pdf'),
        ]))
        ->assertRedirect();

    $newPath = $movement->refresh()->anexo_path;
    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists($newPath);

    $this->actingAs($user)
        ->put(route('plans.activities.movements.update', [$plan, $activity, $movement]), movementPayload([
            'data_movimentacao' => '2026-03-10',
            'remover_anexo' => true,
        ]))
        ->assertRedirect();

    Storage::disk('local')->assertMissing($newPath);
    expect($movement->refresh()->anexo_path)->toBeNull()
        ->and($movement->anexo_nome_original)->toBeNull();
});

it('removes a newly stored file when the database operation fails', function () {
    Storage::fake('local');
    [, , $activity] = movementContext();

    expect(fn () => app(CreateAtividadeMovimentacao::class)->execute($activity, movementPayload([
        'status' => 'estado_inexistente',
        'anexo' => UploadedFile::fake()->create('temporario.pdf', 10, 'application/pdf'),
    ])))->toThrow(ValueError::class);

    Storage::disk('local')->assertDirectoryEmpty('/');
    expect($activity->movimentacoes()->count())->toBe(1);
});

it('protects attachment download and nested movement access', function () {
    Storage::fake('local');
    [$owner, $plan, $activity] = movementContext();
    $movement = $activity->movimentacoes()->sole();
    $path = UploadedFile::fake()->create('parecer.pdf', 10, 'application/pdf')
        ->store('movimentacoes/'.$owner->id, 'local');
    $movement->update(['anexo_path' => $path, 'anexo_nome_original' => 'parecer.pdf']);

    $this->actingAs($owner)
        ->get(route('plans.activities.movements.download', [$plan, $activity, $movement]))
        ->assertDownload('parecer.pdf');

    $this->actingAs(User::factory()->create())
        ->get(route('plans.activities.movements.download', [$plan, $activity, $movement]))
        ->assertForbidden();

    $otherActivity = Atividade::factory()->for($owner)->for($plan, 'planoTrabalho')->create();

    $this->actingAs($owner)
        ->get(route('plans.activities.movements.edit', [$plan, $otherActivity, $movement]))
        ->assertNotFound();
});

it('shows movements chronologically and identifies the current state movement', function () {
    [$user, $plan, $activity] = movementContext();
    AtividadeMovimentacao::factory()->for($activity)->create([
        'data_movimentacao' => '2026-03-15',
        'descricao' => 'Movimentação mais recente',
        'status' => AtividadeStatus::Concluida,
    ]);

    $response = $this->actingAs($user)
        ->get(route('plans.activities.show', [$plan, $activity]))
        ->assertSuccessful()
        ->assertSee('Estado atual')
        ->assertSee('Nova movimentação');

    $response->assertSeeInOrder(['10/03/2026', '15/03/2026']);
});

it('does not expose a movement deletion route', function () {
    expect(collect(app('router')->getRoutes()->getRoutes())
        ->contains(fn ($route) => in_array('DELETE', $route->methods(), true) && str_contains($route->uri(), 'movimentacoes')))
        ->toBeFalse();
});
