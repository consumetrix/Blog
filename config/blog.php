<?php

return [

    'name' => env('BLOG_NAME', 'Consumetrix Blog'),
    'company' => env('BLOG_COMPANY', 'Consumetrix'),
    'tagline' => env('BLOG_TAGLINE', 'Technology, governance and transformation, explained plainly.'),
    // The company website the blog belongs to. Used for the "Talk to us" and "Main site" links.
    'main_site' => env('BLOG_MAIN_SITE', 'https://consumetrix.biz'),
    'contact_url' => env('BLOG_CONTACT_URL', 'https://consumetrix.biz/talk-to-us'),

    // Posts per page on the public list and in the RSS feed.
    'per_page' => 12,
    'feed_limit' => 50,

    /*
    | Dashboard sign-in: after the password, a 6-digit code from an authenticator app
    | (App\Support\TwoFactor). LOGIN_TWO_FACTOR=false turns it off (for example on a local copy).
    */
    'two_factor' => [
        'enabled' => (bool) env('LOGIN_TWO_FACTOR', true),
        'minutes' => 10,
        'attempts' => 5,
    ],

];
