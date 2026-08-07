<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddJsonHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Add JSON headers
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Accept', 'application/json');
        $origin = $request->header('Origin');
        $allowedOrigins = [
            'https://abhayavastra.store',
            'https://www.abhayavastra.store',
            'http://abhayavastra.store',
            'http://www.abhayavastra.store',
            'https://api.dinzin.in',
            'http://localhost:3000',
            'http://localhost:5173',
            'http://127.0.0.1:8000',
        ];

        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            $response->headers->set('Access-Control-Allow-Origin', env('FRONTEND_URL', 'https://abhayavastra.store'));
        }
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        
        return $response;
    }
}
