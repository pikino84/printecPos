<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TourController extends Controller
{
    /**
     * Marcar el tour como completado para el usuario actual
     */
    public function complete(Request $request)
    {
        $user = Auth::user();
        $user->tour_completed = true;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Tour completado'
        ]);
    }

    /**
     * Reiniciar el tour para el usuario actual
     */
    public function reset(Request $request)
    {
        $user = Auth::user();
        $user->tour_completed = false;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Tour reiniciado'
        ]);
    }

    /**
     * Obtener el estado del tour y la configuración según el rol del usuario
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        $roles = $user->getRoleNames()->toArray();

        // Determinar el rol principal para el tour
        $tourRole = $this->determineTourRole($roles);

        return response()->json([
            'tour_completed' => $user->tour_completed,
            'role' => $tourRole,
            'user_name' => $user->name
        ]);
    }

    /**
     * Determinar el rol principal para mostrar el tour apropiado
     */
    private function determineTourRole(array $roles): string
    {
        // Prioridad de roles para el tour
        $rolePriority = [
            'super admin' => 'super-admin',
            'admin' => 'admin',
            'Asociado Administrador' => 'asociado-administrador',
            'Asociado Vendedor' => 'asociado-vendedor',
        ];

        foreach ($rolePriority as $role => $tourKey) {
            if (in_array($role, $roles)) {
                return $tourKey;
            }
        }

        return 'user';
    }
}
