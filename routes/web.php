<?php

use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPasswordController;
use App\Http\Controllers\AtividadeCategoriaController;
use App\Http\Controllers\AtividadeCategoriaStatusController;
use App\Http\Controllers\AtividadeController;
use App\Http\Controllers\AtividadeMovimentacaoController;
use App\Http\Controllers\PitController;
use App\Http\Controllers\PlanoTrabalhoController;
use App\Http\Controllers\RelatorioPlanoTrabalhoController;
use App\Http\Controllers\UpdateForcedPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/password/change-required', function (Request $request) {
        return $request->user()->must_change_password
            ? view('auth.force-password-change')
            : redirect()->route('dashboard');
    })->name('password.force.edit');

    Route::put('/password/change-required', UpdateForcedPasswordController::class)
        ->name('password.force.update');

    Route::middleware(['verified', 'password.changed'])->group(function () {
        Route::get('/dashboard', [PitController::class, 'index'])->name('dashboard');

        Route::prefix('pits')->name('pits.')->group(function () {
            Route::get('/', [PitController::class, 'index'])->name('index');
            Route::get('/criar', [PitController::class, 'create'])->name('create');
            Route::post('/', [PitController::class, 'store'])->name('store');
            Route::get('/{pit}', [PitController::class, 'show'])->name('show');
            Route::get('/{pit}/editar', [PitController::class, 'edit'])->name('edit');
            Route::put('/{pit}', [PitController::class, 'update'])->name('update');
            Route::delete('/{pit}', [PitController::class, 'destroy'])->name('destroy');

            Route::scopeBindings()->prefix('{pit}/pats')->name('plans.')->group(function () {
                Route::get('/criar', [PlanoTrabalhoController::class, 'create'])->name('create');
                Route::post('/', [PlanoTrabalhoController::class, 'store'])->name('store');
            });
        });

        Route::prefix('pats')->name('plans.')->group(function () {
            Route::get('/{plano}', [PlanoTrabalhoController::class, 'show'])->name('show');
            Route::get('/{plano}/editar', [PlanoTrabalhoController::class, 'edit'])->name('edit');
            Route::put('/{plano}', [PlanoTrabalhoController::class, 'update'])->name('update');

            Route::scopeBindings()->prefix('{plano}/atividades')->name('activities.')->group(function () {
                Route::get('/', [AtividadeController::class, 'index'])->name('index');
                Route::get('/criar', [AtividadeController::class, 'create'])->name('create');
                Route::post('/', [AtividadeController::class, 'store'])->name('store');
                Route::get('/{atividade}', [AtividadeController::class, 'show'])->name('show');
                Route::get('/{atividade}/editar', [AtividadeController::class, 'edit'])->name('edit');
                Route::put('/{atividade}', [AtividadeController::class, 'update'])->name('update');

                Route::prefix('{atividade}/movimentacoes')->name('movements.')->group(function () {
                    Route::get('/criar', [AtividadeMovimentacaoController::class, 'create'])->name('create');
                    Route::post('/', [AtividadeMovimentacaoController::class, 'store'])->name('store');
                    Route::get('/{movimentacao}/editar', [AtividadeMovimentacaoController::class, 'edit'])->name('edit');
                    Route::put('/{movimentacao}', [AtividadeMovimentacaoController::class, 'update'])->name('update');
                    Route::get('/{movimentacao}/anexo', [AtividadeMovimentacaoController::class, 'download'])->name('download');
                });
            });
        });

        Route::get('/atividades', [AtividadeController::class, 'overview'])->name('activities.overview');

        Route::prefix('relatorios')->name('reports.')->group(function () {
            Route::get('/', [RelatorioPlanoTrabalhoController::class, 'index'])->name('index');
            Route::get('/pats/{plano}', [RelatorioPlanoTrabalhoController::class, 'show'])->name('show');
        });

        Route::prefix('categorias')->name('categories.')->group(function () {
            Route::get('/', [AtividadeCategoriaController::class, 'index'])->name('index');
            Route::get('/criar', [AtividadeCategoriaController::class, 'create'])->name('create');
            Route::post('/', [AtividadeCategoriaController::class, 'store'])->name('store');
            Route::get('/{categoria}/editar', [AtividadeCategoriaController::class, 'edit'])->name('edit');
            Route::put('/{categoria}', [AtividadeCategoriaController::class, 'update'])->name('update');
            Route::patch('/{categoria}/ativar', [AtividadeCategoriaStatusController::class, 'activate'])->name('activate');
            Route::patch('/{categoria}/desativar', [AtividadeCategoriaStatusController::class, 'deactivate'])->name('deactivate');
        });

        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('admin.index');

            Route::prefix('usuarios')->name('admin.users.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::get('/criar', [UserController::class, 'create'])->name('create');
                Route::post('/', [UserController::class, 'store'])->name('store');
                Route::get('/{user}/editar', [UserController::class, 'edit'])->name('edit');
                Route::put('/{user}', [UserController::class, 'update'])->name('update');
                Route::patch('/{user}/ativar', [UserAccessController::class, 'activate'])->name('activate');
                Route::patch('/{user}/desativar', [UserAccessController::class, 'deactivate'])->name('deactivate');
                Route::patch('/{user}/promover', [UserAccessController::class, 'promote'])->name('promote');
                Route::patch('/{user}/rebaixar', [UserAccessController::class, 'demote'])->name('demote');
                Route::post('/{user}/redefinir-senha', [UserPasswordController::class, 'update'])->name('password.reset');
            });
        });
    });
});
