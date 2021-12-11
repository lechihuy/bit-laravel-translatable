<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default locale for the translation.
    |--------------------------------------------------------------------------
    |
    | The locale that is applied default for the translation. 
    | It's different from the "app.locale" configuration.
    |
    */
    'default' => null,

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | The locales are allowed to interact with your application.
    |
    */
    'locales' => [
        'vi',
        'en'
    ],

    /*
    |--------------------------------------------------------------------------
    | The name of locale column's traslation tables
    |--------------------------------------------------------------------------
    |
    | It must be matched with the name of locale column's translation tables in your application.
    | You can also configure it on each translation model.
    |
    */
    'locale_key' => 'locale',
];