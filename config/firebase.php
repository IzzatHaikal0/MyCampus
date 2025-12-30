<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | Path to your Firebase service account JSON file.
    | On Windows, make sure to use double backslashes `\\`.
    |
    */
    'credentials' => env('FIREBASE_CREDENTIALS', base_path('firebase_credentials.json')),
    'credentials' => storage_path('app/firebase/firebase_credentials.json'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Realtime Database URL
    |--------------------------------------------------------------------------
    |
    | The URL of your Firebase Realtime Database.
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://mycampus-f7b98-default-rtdb.asia-southeast1.firebasedatabase.app'),
    'database_url' => env('FIREBASE_DATABASE_URL'),
];
