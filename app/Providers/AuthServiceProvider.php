<?php

namespace App\Providers;

use App\Models\Task;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Сопоставление моделей с политиками авторизации.
     *
     */
    protected $policies = [
        Task::class => TaskPolicy::class,
    ];

    /**
     * Регистрация любых сервисов аутентификации / авторизации.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
