<?php

namespace Foorintodev\FormImport;

use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    // Feuille de style CP (externe → chargée dans le <head>, hors du conteneur Vue).
    protected $stylesheets = [
        __DIR__.'/../resources/dist/css/cp.css',
    ];

    // Bouton « Export dédoublonné » injecté sur la page d'un formulaire.
    protected $scripts = [
        __DIR__.'/../resources/dist/js/cp.js',
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
