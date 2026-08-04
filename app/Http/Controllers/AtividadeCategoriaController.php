<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAtividadeCategoriaRequest;
use App\Http\Requests\UpdateAtividadeCategoriaRequest;
use App\Models\AtividadeCategoria;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AtividadeCategoriaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', AtividadeCategoria::class);
        $status = $request->string('status')->value();

        $categories = $request->user()
            ->atividadeCategorias()
            ->select(['id', 'user_id', 'nome', 'descricao', 'ativa', 'created_at', 'updated_at'])
            ->when($request->filled('nome'), fn ($query) => $query->where('nome', 'like', '%'.$request->string('nome')->trim().'%'))
            ->when(in_array($status, ['ativa', 'inativa'], true), fn ($query) => $query->where('ativa', $status === 'ativa'))
            ->orderByDesc('ativa')
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', ['categories' => $categories]);
    }

    public function create(): View
    {
        Gate::authorize('create', AtividadeCategoria::class);

        return view('categories.create');
    }

    public function store(StoreAtividadeCategoriaRequest $request): RedirectResponse
    {
        $request->user()->atividadeCategorias()->create($request->validated());

        return redirect()->route('categories.index')->with('status', 'Categoria cadastrada com sucesso.');
    }

    public function edit(AtividadeCategoria $categoria): View
    {
        Gate::authorize('update', $categoria);

        return view('categories.edit', ['category' => $categoria]);
    }

    public function update(UpdateAtividadeCategoriaRequest $request, AtividadeCategoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()->route('categories.index')->with('status', 'Categoria atualizada com sucesso.');
    }
}
