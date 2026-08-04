<?php

namespace App\Http\Controllers;

use App\Models\AtividadeCategoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class AtividadeCategoriaStatusController extends Controller
{
    public function activate(AtividadeCategoria $categoria): RedirectResponse
    {
        Gate::authorize('activate', $categoria);
        $categoria->update(['ativa' => true]);

        return redirect()->route('categories.index')->with('status', 'Categoria ativada com sucesso.');
    }

    public function deactivate(AtividadeCategoria $categoria): RedirectResponse
    {
        Gate::authorize('deactivate', $categoria);
        $categoria->update(['ativa' => false]);

        return redirect()->route('categories.index')->with('status', 'Categoria desativada com sucesso.');
    }
}
