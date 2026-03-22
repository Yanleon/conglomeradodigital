<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PasarelaController extends Controller
{
    /**
     * Genera el hash de integración para Bold.
     */
    public function generarHash(Request $request)
    {
        $request->validate([
            'identificador' => 'required|string',
            'monto' => 'required|numeric',
            'divisa' => 'required|string',
        ]);

        $identificador = $request->identificador;
        $monto = $request->monto;
        $divisa = $request->divisa;

        // Llave secreta almacenada en .env (nunca en frontend)
        $llaveSecreta = env('LLAVE_SECRETA_PASARELA');

        // Concatenar la cadena según la documentación de Bold
        $cadena_concatenada = "{$identificador}{$monto}{$divisa}{$llaveSecreta}";

        // Generar el hash SHA256
        $hash = hash("sha256", $cadena_concatenada);

        return response()->json(['hash' => $hash]);
    }

    /**
     * Muestra la vista del botón Bold.
     */
    public function mostrarBoton()
    {
        // Idealmente estos datos vendrían de tu base de datos o pedido real
        $pedido = [
            'identificador' => 'PEDIDO123',
            'monto' => 150000,
            'divisa' => 'COP',
            'descripcion' => 'Zapatos de temporada para dama',
            'correo' => 'cliente@ejemplo.com'
        ];

        return view('pasarela.boton', compact('pedido'));
    }
}
