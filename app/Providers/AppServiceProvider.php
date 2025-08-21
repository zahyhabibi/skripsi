<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
 public function register(): void
    {
      
        $this->app->singleton(Factory::class, function () {
            $factory = new Factory();

        
            if ($dbUrl = env('FIREBASE_DATABASE_URL')) {
                $factory = $factory->withDatabaseUri($dbUrl);
            }

           
            if ($json = env('FIREBASE_CREDENTIALS')) {
                $creds = json_decode($json, true);


                if (json_last_error() !== JSON_ERROR_NONE) {
                    $decoded = base64_decode($json, true);
                    if ($decoded !== false) {
                        $arr = json_decode($decoded, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $creds = $arr;
                        }
                    }
                }

                if (is_array($creds)) {
                    $factory = $factory->withServiceAccount($creds);
                }
            }


            if (env('GOOGLE_APPLICATION_CREDENTIALS')) {
                $factory = $factory->withServiceAccount(env('GOOGLE_APPLICATION_CREDENTIALS'));
            }

            return $factory;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('development')) {
        URL::forceScheme('https');
        }
    }
}
