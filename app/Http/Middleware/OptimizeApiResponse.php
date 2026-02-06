<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Response Optimization Middleware
 * Compresses responses and adds caching headers
 */
class OptimizeApiResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only optimize JSON responses
        if ($response->headers->get('Content-Type') === 'application/json') {
            // Add cache headers for GET requests
            if ($request->isMethod('GET')) {
                $response->headers->set('Cache-Control', 'public, max-age=60');
                $response->headers->set('ETag', md5($response->getContent()));
            }

            // Compress response if client supports it
            if (str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
                $content = $response->getContent();
                if (strlen($content) > 1024) { // Only compress if > 1KB
                    $compressed = gzencode($content, 6); // Level 6 compression
                    $response->setContent($compressed);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->set('Content-Length', strlen($compressed));
                }
            }
        }

        return $response;
    }
}
