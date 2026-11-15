<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The admin panel is in English, whatever language the storefront uses (APP_LOCALE=bn).
 * Bangla shop content is still shown in its own *_bn columns.
 */
class UseAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale('en');

        return $next($request);
    }
}
