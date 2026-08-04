<?php

namespace App\Actions\Movimentacoes;

use App\Enums\AtividadeStatus;
use App\Models\Atividade;
use App\Models\AtividadeMovimentacao;
use App\Support\MovementAttachmentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateAtividadeMovimentacao
{
    public function __construct(
        private SyncAtividadeStatus $syncActivityStatus,
        private MovementAttachmentStorage $attachmentStorage,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Atividade $activity, array $data): AtividadeMovimentacao
    {
        $storedAttachment = isset($data['anexo']) && $data['anexo'] instanceof UploadedFile
            ? $this->attachmentStorage->store($data['anexo'], $activity->user_id)
            : null;

        try {
            return DB::transaction(function () use ($activity, $data, $storedAttachment): AtividadeMovimentacao {
                $lockedActivity = Atividade::query()->whereKey($activity->getKey())->lockForUpdate()->firstOrFail();
                $movement = new AtividadeMovimentacao(Arr::only($data, [
                    'data_movimentacao',
                    'descricao',
                    'aguardando_por',
                    'aguardando_descricao',
                    'minutos_trabalhados',
                ]));
                $movement->status = AtividadeStatus::from($data['status']);
                $movement->anexo_path = $storedAttachment['path'] ?? null;
                $movement->anexo_nome_original = $storedAttachment['original_name'] ?? null;
                $movement->atividade()->associate($lockedActivity);
                $movement->save();

                $this->syncActivityStatus->execute($lockedActivity);

                return $movement;
            }, attempts: 3);
        } catch (Throwable $exception) {
            $this->attachmentStorage->delete($storedAttachment['path'] ?? null);

            throw $exception;
        }
    }
}
