<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHakAkses
{
    public function handle(Request $request, Closure $next, string $modul, string $action): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasHakAkses($modul, $action)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk melakukan aksi ini.'
            ], 403);
        }

        return redirect()->route('dashboard')->with('error', "Anda tidak memiliki hak akses {$action} untuk modul {$modul}.");
    }
}
