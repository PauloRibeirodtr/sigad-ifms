<?php

namespace App\Actions\Atividades;

use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeCategoria;
use App\Models\AtividadeMovimentacao;
use App\Models\PlanoTrabalho;
use App\Models\User;
use App\Support\MovementAttachmentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateAtividadeWithFirstMovement
{
    public function __construct(private MovementAttachmentStorage $attachmentStorage) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, PlanoTrabalho $plan, array $data): Atividade
    {
        $storedAttachment = isset($data['anexo']) && $data['anexo'] instanceof UploadedFile
            ? $this->attachmentStorage->store($data['anexo'], $user->getKey())
            : null;

        try {
            return DB::transaction(function () use ($user, $plan, $data, $storedAttachment): Atividade {
                $category = AtividadeCategoria::query()
                    ->whereBelongsTo($user)
                    ->where('ativa', true)
                    ->findOrFail($data['categoria_id']);

                $status = AtividadeStatus::from($data['movimentacao_status']);
                $activity = new Atividade(Arr::only($data, [
                    'titulo',
                    'descricao',
                    'solicitante',
                    'data_atividade',
                    'prioridade',
                    'prazo',
                    'proxima_acao',
                ]));
                $activity->status = $status;
                $activity->aguardando_por = $data['aguardando_por'];
                $activity->aguardando_descricao = $data['aguardando_descricao'];
                $activity->user()->associate($user);
                $activity->planoTrabalho()->associate($plan);
                $activity->categoria()->associate($category);
                $activity->save();

                $movement = new AtividadeMovimentacao([
                    'data_movimentacao' => $data['data_movimentacao'],
                    'descricao' => $data['movimentacao_descricao'],
                    'status' => $status,
                    'aguardando_por' => $data['aguardando_por'],
                    'aguardando_descricao' => $data['aguardando_descricao'],
                    'minutos_trabalhados' => $data['minutos_trabalhados'],
                    'anexo_path' => $storedAttachment['path'] ?? null,
                    'anexo_nome_original' => $storedAttachment['original_name'] ?? null,
                ]);
                $movement->atividade()->associate($activity);
                $movement->save();

                return $activity;
            }, attempts: 3);
        } catch (Throwable $exception) {
            $this->attachmentStorage->delete($storedAttachment['path'] ?? null);

            throw $exception;
        }
    }
}
