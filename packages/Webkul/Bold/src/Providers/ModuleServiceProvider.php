<?php

namespace Webkul\Bold\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'bold');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'bold');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Carga las rutas si existen
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');

        // Registra el servicio principal del módulo
        $this->app->register(BoldServiceProvider::class);
    }

    public function register()
    {
        //
    }
}
