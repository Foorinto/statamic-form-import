<?php

namespace Foorintodev\FormImport;

use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    public function bootAddon()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'form-import');

        Nav::extend(function ($nav) {
            $nav->tools(__('Import formulaire'))
                ->route('form-import.index')
                ->icon('upload');
        });
    }
}
