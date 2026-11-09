<?php

namespace Encore\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Port note: PJAX is retired — rendering is handled by Filament/Livewire.
 * The middleware is kept as a pass-through so the `admin.pjax` alias and
 * legacy middleware groups keep working.
 */
class Pjax
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Send a response through this middleware.
     *
     * @param Response $response
     */
    public static function respond(Response $response)
    {
        $response->send();

        exit;
    }
}
