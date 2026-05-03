<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public careers / JobPosting structured data
    |--------------------------------------------------------------------------
    |
    | Used for SEO meta defaults and schema.org JobPosting hiringOrganization.
    |
    */

    'organization_name' => env('CAREERS_ORG_NAME', env('APP_NAME', 'MarkOnMinds')),

    'organization_url' => env('CAREERS_ORG_URL', env('APP_URL', 'http://localhost')),

    'organization_logo' => env('CAREERS_ORG_LOGO'),

];
