<?php
namespace Paulodiff\FieldsEncryptedIndex;

use Illuminate\Support\ServiceProvider;
use Paulodiff\FieldsEncryptedIndex\Console\FieldsEncryptedIndexCheckConfigCommand;
use Paulodiff\FieldsEncryptedIndex\Console\FieldsEncryptedIndexDbSeedCommand;
use Paulodiff\FieldsEncryptedIndex\Console\FieldsEncryptedIndexDbDynamicCommand;
use Paulodiff\FieldsEncryptedIndex\Console\FieldsEncryptedIndexParseSQLCommand;
// use Paulodiff\FieldsEncryptedIndex\Console\FieldsEncryptedIndexKeyGeneratorCommand;
// use Paulodiff\FieldsEncryptedIndex\Console\FieldsEncryptedIndexDbSeedCommand;
// use Paulodiff\FieldsEncryptedIndex\Console\FieldsEncryptedIndexDbCrudCommand;
// use Paulodiff\FieldsEncryptedIndex\Console\FieldsEncryptedIndexDbMaintenanceCommand;
// use Paulodiff\FieldsEncryptedIndex\Console\FieldsEncryptedIndexDbRelationalCommand;

class FieldsEncryptedIndexServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-package-demo');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-package-demo');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/FieldsEncryptedIndex.php' => config_path('FieldsEncryptedIndex.php'),
            ], 'config');


            if ($this->app->runningInConsole()) {
                $this->commands([
                    // FieldsEncryptedIndexCheckConfigCommand::class,
                    // FieldsEncryptedIndexDbSeedCommand::class,
                    // FieldsEncryptedIndexDbDynamicCommand::class,
                    FieldsEncryptedIndexParseSQLCommand::class,
                    // FieldsEncryptedIndexKeyGeneratorCommand::class,
                    
                    // FieldsEncryptedIndexDbCrudCommand::class,
                    // FieldsEncryptedIndexDbMaintenanceCommand::class,
                    // FieldsEncryptedIndexDbRelationalCommand::class,
                ]);
            }

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-package-demo'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-package-demo'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-package-demo'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/FieldsEncryptedIndex.php', 'rainbox-table-index');

        // Register the main class to use with the facade
        $this->app->singleton('rainbox-table-index', function () {
            return new FieldsEncryptedIndex;
        });
    }
}
