<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('product')->orderBy('created_at', 'desc')->get();
        return response()->json($sales);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible para este producto. Stock actual: ' . $product->stock
            ], 422);
        }

        try {
            $sale = DB::transaction(function () use ($product, $request) {
                // Reducir stock del producto
                $product->decrement('stock', $request->quantity);

                // Crear la venta
                return Sale::create([
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'total_price' => $product->price * $request->quantity,
                ]);
            });

            // Cargar la relación para la respuesta
            $sale->load('product');

            return response()->json([
                'message' => 'Venta registrada con éxito.',
                'sale' => $sale
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al procesar la venta. Por favor, inténtelo de nuevo.'
            ], 500);
        }
    }
}
