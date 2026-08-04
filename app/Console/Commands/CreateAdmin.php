<?php

namespace App\Console\Commands;

use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

#[Signature('app:create-admin')]
#[Description('Cria a primeira conta administrativa do SIGAD')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (User::query()->where('perfil', UserProfile::Administrador)->exists()) {
            $this->error('Já existe uma conta administrativa cadastrada.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nome'));
        $email = Str::lower(trim((string) $this->ask('E-mail')));
        $password = (string) $this->secret('Senha');
        $passwordConfirmation = (string) $this->secret('Confirme a senha');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->perfil = UserProfile::Administrador;
        $user->ativo = true;
        $user->must_change_password = false;
        $user->password_changed_at = now();
        $user->email_verified_at = now();
        $user->save();

        $this->info('Administrador criado com sucesso.');

        return self::SUCCESS;
    }
}
