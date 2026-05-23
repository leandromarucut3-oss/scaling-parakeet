<?php

return [
    // Default Canva template id (falls back to services.canva.template_id)
    'default_template' => env('CANVA_TEMPLATE_ID', 'EAHKeONqmV8'),

    // Map package keys/slugs to specific Canva template IDs. Example:
    // 'presidential_plan' => env('CANVA_TEMPLATE_PRESIDENTIAL_ID'),
    'template_map' => [
        // Map package slugs to the Canva template id
        'premiere_plan' => env('CANVA_TEMPLATE_PREMIERE', 'EAHKeONqmV8'),
        'deluxe_plan' => env('CANVA_TEMPLATE_DELUXE', 'EAHKeONqmV8'),
        'presidential_plan' => env('CANVA_TEMPLATE_PRESIDENTIAL', 'EAHKeONqmV8'),
    ],

    // Field mapping from our local variable names to Canva template data keys.
    // Adjust the right-hand values to match the field names/placeholders used in your Canva template.
    'field_map' => [
        'name' => 'name',
        'package' => 'plan',
        'issued_at' => 'issued_at',
        'certificate_id' => 'certificate_id',
    ],
];
