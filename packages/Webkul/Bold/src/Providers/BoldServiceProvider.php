<?php

namespace Webkul\Bold\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\Core\Tree;

class BoldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Cargar configuración del sistema cuando la app haya iniciado
        $this->app->booted(function () {
            if (class_exists(Tree::class)) {
                $configTree = $this->app->make(Tree::class);

                $systemConfig = include __DIR__ . '/../Config/system.php';

                if (is_array($systemConfig)) {
                    $configTree->add($systemConfig);
                }
            }
        });
    }

    public function register()
    {
        //
    }
}
