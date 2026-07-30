<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConsultaDolarController extends Controller
{
    private $rucToken;
    private $decolectaApiKey;

    public function __construct()
    {
        $this->rucToken = env('RUC_API_TOKEN', '6VFja10Cc5Lv6Hdavf6b9rtxGHnHvqxRwI5WXjYCyi6WYpwuUg6B3re5UXgx');
        $this->decolectaApiKey = env('DECOLECTA_API_KEY', '');
    }

    public function index()
    {
        return view('consultas.index');
    }

    public function consultarTipoCambio(Request $request)
    {
        try {
            if (empty($this->decolectaApiKey)) {
                return response()->json([
                    'success' => false,
                    'error' => 'API Key no configurada. Por favor, configure DECOLECTA_API_KEY en el archivo .env'
                ], 500);
            }

            // Si viene month y year en el request, usarlos. Si no, usar fecha actual
            $month = $request->input('month', date('n'));
            $year = $request->input('year', date('Y'));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->decolectaApiKey,
            ])->get("https://api.decolecta.com/v1/tipo-cambio/sunat", [
                        'month' => $month,
                        'year' => $year
                    ]);

            if ($response->successful()) {
                $data = $response->json();

                // Si no vienen parámetros, devolver el último (más reciente)
                if (!$request->has('month') && !$request->has('year') && is_array($data) && count($data) > 0) {
                    $ultimoTipoCambio = end($data);
                    return response()->json([
                        'success' => true,
                        'data' => $ultimoTipoCambio
                    ]);
                }

                // Si vienen parámetros, devolver todo el mes
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudo obtener el tipo de cambio. Por favor, intente nuevamente.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al consultar el tipo de cambio: ' . $e->getMessage()
            ], 500);
        }
    }

    // public function consultarRUC(Request $request)
    // {
    //     $request->validate([
    //         'ruc' => 'required|digits:11',
    //     ]);

    //     $ruc = $request->input('ruc');

    //     try {   
    //         $response = Http::withHeaders([
    //             'Content-Type' => 'application/json',
    //             'Authorization' => "Bearer {$this->rucToken}",
    //         ])->get("https://api.codart.cgrt.net/api/v1/consultas/sunat/ruc/{$ruc}");

    //         if ($response->successful()) {
    //             $data = $response->json();
    //             return response()->json([
    //                 'success' => true,
    //                 'data' => $data
    //             ]); 
    //         } else {
    //             return response()->json([
    //                 'success' => false,
    //                 'error' => 'No se pudo consultar el RUC.'
    //             ], 400);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Error al consultar el RUC. Por favor, intente nuevamente.'
    //         ], 500);
    //     }
    // }
}