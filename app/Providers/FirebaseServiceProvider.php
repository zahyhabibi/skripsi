<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Factory::class, function ($app) {
            // Pastikan path ke private key sudah benar dan file ada
            // Gunakan base_path() untuk mendapatkan path absolut dari root proyek Laravel
            $privateKeyPath = base_path(env('FIREBASE_PRIVATE_KEY_PATH'));

            if (!file_exists($privateKeyPath)) {
                throw new \Exception("Firebase private key file not found at: " . $privateKeyPath);
            }

            return (new Factory)
                ->withServiceAccount($privateKeyPath)
                ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));
                // Jika Anda juga menggunakan Cloud Firestore, tambahkan:
                // ->withFirestoreProjectId(env('FIREBASE_PROJECT_ID'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
