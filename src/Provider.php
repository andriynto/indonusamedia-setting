<?php

namespace Indonusamedia\Setting;

use Indonusamedia\Setting\Middleware\AutoSaveSetting;
use Blade;
use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Config/setting.php' => config_path('setting.php'),
            __DIR__.'/Migrations/2018_08_24_000000_create_settings_table.php' => database_path('migrations/2018_08_24_000000_create_settings_table.php'),
        ], 'setting');

        $this->app->singleton('setting.manager', function ($app) {
            return new Manager($app);
        });

        $this->app->singleton('setting', function ($app) {
            return $app['setting.manager']->driver();
        });

        // Auto save setting
        if (config('setting.auto_save')) {
            $kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
            $kernel->pushMiddleware(AutoSaveSetting::class);
        }

        // Override config
        if (config('setting.override')) {
            foreach (config('setting.override') as $config_key => $setting_key) {
                config([$config_key => setting($setting_key)]);
            }
        }

        // Register blade directive
        Blade::directive('setting', function ($expression) {
            return "<?php echo setting($expression); ?>";
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/Config/setting.php', 'setting');
    }
}
