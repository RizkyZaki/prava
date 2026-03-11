<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meta WhatsApp Cloud API
    |--------------------------------------------------------------------------
    */

    'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'app_secret' => env('WHATSAPP_APP_SECRET'),

    'api_version' => env('WHATSAPP_API_VERSION', 'v18.0'),
    'api_base_url' => 'https://graph.facebook.com',

    /*
    |--------------------------------------------------------------------------
    | Groq AI
    |--------------------------------------------------------------------------
    */

    'groq_api_key' => env('GROQ_API_KEY'),
    'groq_model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    'groq_system_prompt' => env('GROQ_SYSTEM_PROMPT', 'Kamu adalah PST AI dari Pratama Solusi Teknologi. Jawab singkat dan ramah.'),

    /*
    |--------------------------------------------------------------------------
    | Chat Settings
    |--------------------------------------------------------------------------
    */

    'max_history_messages' => 50,

];
