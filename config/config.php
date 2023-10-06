<?php

return [
    'i18n' => [
        'locals'  =>  ['en', 'ar'],
        'auto_fill' => false,
        'property' => 'translatable',
        // When i18n field marked as required, all translations will be required (true) or at least one (false)
        'all_required' => false,
    ],
    'auto_fill_fillable' => false,
    'ruler_filler' => [
        'search' => "// TODO: Add rules for all fillable fields\n",
        'separator' => "\t\t\t",
    ]
];