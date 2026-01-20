<?php

namespace App\Http\Controllers;

use App\Http\Requests\Productos\ProductosStoreRequest;
use App\Http\Requests\Productos\ProductosUpdateRequest;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        // Build query
        $query = Producto::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhere('precio_venta', 'like', "%{$search}%")
                    ->orWhere('costo_promedio', 'like', "%{$search}%")
                    ->orWhere('categoria', 'like', "%{$search}%");
            });
        }

        // Handle "all" records option
        if ($perPage == -1) {
            $totalProductos = $query->count();
            $perPage = $totalProductos > 0 ? $totalProductos : 10;
        }

        return Inertia::render('productos/index', [
            'producto' => $query->paginate($perPage)->appends(['search' => $search, 'per_page' => $perPage]),
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('productos/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductosStoreRequest $request)
    {
        Producto::create($request->validated());

        return to_route('productos.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        return Inertia::render('productos/edit', [
            'producto' => $producto
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductosUpdateRequest $request, Producto $producto)
    {
        $producto->update($request->validated());

        return to_route('productos.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return to_route('productos.index');
    }
}
