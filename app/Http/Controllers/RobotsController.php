<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Served by Laravel so the sitemap line always uses the domain the blog runs on. Outside
 * production crawlers are kept out entirely, matching the noindex tag the layout adds there.
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $lines = ['User-agent: *', app()->environment('production') ? 'Disallow: /dashboard' : 'Disallow: /', '', 'Sitemap: '.route('sitemap')];

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
