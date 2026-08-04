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

class UpdateAtividadeMovimentacao
{
    public function __construct(
        private SyncAtividadeStatus $syncActivityStatus,
        private MovementAttachmentStorage $attachmentStorage,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(
        Atividade $activity,
        AtividadeMovimentacao $movement,
        array $data,
    ): AtividadeMovimentacao {
        $storedAttachment = isset($data['anexo']) && $data['anexo'] instanceof UploadedFile
            ? $this->attachmentStorage->store($data['anexo'], $activity->user_id)
            : null;
        $oldAttachmentPath = null;

        try {
            $updatedMovement = DB::transaction(function () use (
                $activity,
                $movement,
                $data,
                $storedAttachment,
                &$oldAttachmentPath,
            ): AtividadeMovimentacao {
                $lockedActivity = Atividade::query()->whereKey($activity->getKey())->lockForUpdate()->firstOrFail();
                $lockedMovement = AtividadeMovimentacao::query()
                    ->whereKey($movement->getKey())
                    ->whereBelongsTo($lockedActivity, 'atividade')
                    ->lockForUpdate()
                    ->firstOrFail();
                $oldAttachmentPath = $lockedMovement->anexo_path;
                $lockedMovement->fill(Arr::only($data, [
                    'data_movimentacao',
                    'descricao',
                    'aguardando_por',
                    'aguardando_descricao',
                    'minutos_trabalhados',
                ]));
                $lockedMovement->status = AtividadeStatus::from($data['status']);

                if ($storedAttachment !== null) {
                    $lockedMovement->anexo_path = $storedAttachment['path'];
                    $lockedMovement->anexo_nome_original = $storedAttachment['original_name'];
                } elseif ($data['remover_anexo'] ?? false) {
                    $lockedMovement->anexo_path = null;
                    $lockedMovement->anexo_nome_original = null;
                }

                $lockedMovement->save();
                $this->syncActivityStatus->execute($lockedActivity);

                return $lockedMovement;
            }, attempts: 3);
        } catch (Throwable $exception) {
            $this->attachmentStorage->delete($storedAttachment['path'] ?? null);

            throw $exception;
        }

        if ($storedAttachment !== null || ($data['remover_anexo'] ?? false)) {
            $this->attachmentStorage->delete($oldAttachmentPath);
        }

        return $updatedMovement;
    }
}
