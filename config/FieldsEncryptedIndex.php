<?php

/*

    Con

*/


return [

    'key' => env('FIELDS_ENCRYPTED_INDEX_KEY', null),
    'nonce' => env('FIELDS_ENCRYPTED_INDEX_NONCE', null),
    'encrypt' => env('FIELDS_ENCRYPTED_INDEX_ENCRYPT', null),
    'prefix' => env('FIELDS_ENCRYPTED_INDEX_PREFIX', 'rt_'),

];
