<?php

use App\Models\AtividadeCategoria;
use App\Models\User;

it('creates a trimmed active category for the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->post(route('categories.store'), [
            'nome' => '  Atendimento Discente  ',
            'descricao' => '  Orientações e atendimentos aos estudantes.  ',
            'user_id' => $otherUser->id,
            'ativa' => false,
        ])->assertRedirectToRoute('categories.index');

    $category = AtividadeCategoria::query()->sole();

    expect($category->nome)->toBe('Atendimento Discente')
        ->and($category->descricao)->toBe('Orientações e atendimentos aos estudantes.')
        ->and($category->user_id)->toBe($user->id)
        ->and($category->ativa)->toBeTrue();
});

it('allows an empty optional description', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('categories.store'), [
            'nome' => 'Documentação',
            'descricao' => '   ',
        ])->assertRedirectToRoute('categories.index');

    expect(AtividadeCategoria::query()->sole()->descricao)->toBeNull();
});

it('blocks duplicate names for the same user after trimming', function () {
    $user = User::factory()->create();
    AtividadeCategoria::factory()->for($user)->create(['nome' => 'Reuniões']);

    $this->actingAs($user)
        ->from(route('categories.create'))
        ->post(route('categories.store'), [
            'nome' => '  Reuniões  ',
        ])->assertRedirect(route('categories.create'))
        ->assertSessionHasErrors('nome');

    expect($user->atividadeCategorias()->count())->toBe(1);
});

it('allows the same category name for different users', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    AtividadeCategoria::factory()->for($firstUser)->create(['nome' => 'Extensão']);

    $this->actingAs($secondUser)
        ->post(route('categories.store'), ['nome' => 'Extensão'])
        ->assertRedirectToRoute('categories.index');

    expect(AtividadeCategoria::query()->where('nome', 'Extensão')->count())->toBe(2);
});

it('lists and filters only categories owned by the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    AtividadeCategoria::factory()->for($user)->create(['nome' => 'Categoria Própria']);
    AtividadeCategoria::factory()->inactive()->for($user)->create(['nome' => 'Categoria Inativa']);
    AtividadeCategoria::factory()->for($otherUser)->create(['nome' => 'Categoria Alheia']);

    $this->actingAs($user)
        ->get(route('categories.index', ['nome' => 'Própria', 'status' => 'ativa']))
        ->assertSuccessful()
        ->assertSee('Categoria Própria')
        ->assertDontSee('Categoria Inativa')
        ->assertDontSee('Categoria Alheia');
});

it('keeps administrators restricted to their own categories', function () {
    $administrator = User::factory()->administrator()->create();
    $otherUser = User::factory()->create();
    AtividadeCategoria::factory()->for($administrator)->create(['nome' => 'Categoria do Administrador']);
    AtividadeCategoria::factory()->for($otherUser)->create(['nome' => 'Categoria do Servidor']);

    $this->actingAs($administrator)
        ->get(route('categories.index'))
        ->assertSuccessful()
        ->assertSee('Categoria do Administrador')
        ->assertDontSee('Categoria do Servidor');
});

it('paginates categories', function () {
    $user = User::factory()->create();
    AtividadeCategoria::factory()->count(11)->for($user)->create();

    $this->actingAs($user)
        ->get(route('categories.index'))
        ->assertSuccessful()
        ->assertSee('11 categorias encontradas')
        ->assertSee('page=2', false);
});

it('updates only an owned category and preserves its ownership and status', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = AtividadeCategoria::factory()->for($user)->create();

    $this->actingAs($user)
        ->put(route('categories.update', $category), [
            'nome' => '  Nome Atualizado  ',
            'descricao' => '  Nova descrição.  ',
            'user_id' => $otherUser->id,
            'ativa' => false,
        ])->assertRedirectToRoute('categories.index');

    $category->refresh();

    expect($category->nome)->toBe('Nome Atualizado')
        ->and($category->descricao)->toBe('Nova descrição.')
        ->and($category->user_id)->toBe($user->id)
        ->and($category->ativa)->toBeTrue();
});

it('activates and deactivates a category without deleting it', function () {
    $user = User::factory()->create();
    $category = AtividadeCategoria::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('categories.deactivate', $category))
        ->assertRedirectToRoute('categories.index');

    expect($category->refresh()->ativa)->toBeFalse();
    $this->assertModelExists($category);

    $this->actingAs($user)
        ->patch(route('categories.activate', $category))
        ->assertRedirectToRoute('categories.index');

    expect($category->refresh()->ativa)->toBeTrue();
});

it('blocks viewing editing or changing the status of another users category', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $foreignCategory = AtividadeCategoria::factory()->for($otherUser)->create();

    $this->actingAs($user)
        ->get(route('categories.edit', $foreignCategory))
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('categories.update', $foreignCategory), [
            'nome' => 'Tentativa de alteração',
        ])->assertForbidden();

    $this->actingAs($user)
        ->patch(route('categories.deactivate', $foreignCategory))
        ->assertForbidden();

    expect($foreignCategory->refresh()->ativa)->toBeTrue();
});

it('provides an active scope for future activity forms', function () {
    $user = User::factory()->create();
    AtividadeCategoria::factory()->for($user)->create(['nome' => 'Ativa']);
    AtividadeCategoria::factory()->inactive()->for($user)->create(['nome' => 'Inativa']);

    expect($user->atividadeCategorias()->ativas()->pluck('nome')->all())->toBe(['Ativa']);
});

it('does not expose a category deletion route', function () {
    expect(collect(app('router')->getRoutes()->getRoutes())
        ->contains(fn ($route) => in_array('DELETE', $route->methods(), true) && str_starts_with($route->uri(), 'categorias')))
        ->toBeFalse();
});
