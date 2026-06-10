<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFrontendLogRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FrontendLogController extends Controller
{
    /**
     * Recibe errores de JavaScript del browser y los escribe al canal 'frontend'.
     * Sirve para diagnosticar problemas que no se replican localmente
     * (página en blanco, CDN caído, scripts que no cargan, etc).
     */
    public function store(StoreFrontendLogRequest $request)
    {
        $data = $request->validated();

        Log::channel('frontend')->warning($data['type'], [
            'message' => $data['message'] ?? null,
            'source' => $data['source'] ?? null,
            'line' => $data['line'] ?? null,
            'column' => $data['column'] ?? null,
            'stack' => $data['stack'] ?? null,
            'url' => $data['url'] ?? null,
            'diagnostics' => $data['diagnostics'] ?? null,
            'user_id' => Auth::id(),
            'user_agent' => substr((string) $request->userAgent(), 0, 300),
            'ip' => $request->ip(),
        ]);

        return response()->noContent();
    }
}
