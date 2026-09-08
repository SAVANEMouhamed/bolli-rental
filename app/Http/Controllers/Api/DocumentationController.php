<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\OpenApiDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    /**
     * Interface Swagger UI de l'API interne.
     */
    public function index(): View
    {
        return view('api.documentation');
    }

    /**
     * Document OpenAPI 3.1 brut, consommable par tout client (Swagger, Postman, Insomnia).
     */
    public function schema(OpenApiDocument $document): JsonResponse
    {
        return response()->json($document->toArray());
    }
}
