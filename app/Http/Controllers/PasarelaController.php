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

        $hash = $this->buildIntegritySignature($identificador, $monto, $divisa);

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
            'correo' => 'cliente@ejemplo.com',
        ];

        $hash = $this->buildIntegritySignature(
            $pedido['identificador'],
            $pedido['monto'],
            $pedido['divisa']
        );

        $apiKey = env('BOLD_API_KEY', env('BOLD_IDENTIFICADOR_COMERCIO'));
        $redirectionUrl = env('BOLD_REDIRECTION_URL', url('/'));

        return view('pasarela.boton', compact('pedido', 'hash', 'apiKey', 'redirectionUrl'));
    }

    private function buildIntegritySignature(string $identificador, $monto, string $divisa): string
    {
        $llaveSecreta = env('BOLD_SECRET_KEY', env('LLAVE_SECRETA_PASARELA'));

        $cadenaConcatenada = "{$identificador}{$monto}{$divisa}{$llaveSecreta}";

        return hash('sha256', $cadenaConcatenada);
    }
}
