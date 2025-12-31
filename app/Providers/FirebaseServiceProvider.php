<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Make Firebase Auth available via dependency injection
        $this->app->singleton(Auth::class, function ($app) {
            $factory = (new Factory)
                ->withServiceAccount(base_path('firebase/firebase_credentials.json'));
                
            return $factory->createAuth();
        });

        // You can also add other Firebase services, for example Firestore:
        /*
        $this->app->singleton(\Kreait\Firebase\Firestore::class, function ($app) {
            $factory = (new Factory)
                ->withServiceAccount(base_path('firebase/firebase_credentials.json'));
                
            return $factory->createFirestore();
        });
        */
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        //
    }
}
