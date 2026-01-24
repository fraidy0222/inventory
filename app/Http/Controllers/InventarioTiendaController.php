<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventarioTienda\InventarioTiendaRequest;
use App\Http\Resources\InventarioTiendaResource;
use App\Models\InventarioTienda;
use App\Models\Producto;
use App\Models\Tienda;
use Inertia\Inertia;
use Illuminate\Http\Request;

class InventarioTiendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $tiendaId = $request->input('tienda_id', '');

        // Query principal
        $query = InventarioTienda::with(['tienda', 'producto']);


        // Aplicar filtros
        if ($tiendaId) {
            $query->where('tienda_id', $tiendaId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('producto', function ($q2) use ($search) {
                    $q2->where('nombre', 'like', "%{$search}%");
                })->orWhereHas('tienda', function ($q2) use ($search) {
                    $q2->where('nombre', 'like', "%{$search}%");
                });
            });
        }

        // Ordenar
        $query->orderBy('tienda_id')->orderBy('producto_id');

        // Manejar opción "todos"
        if ($perPage == -1) {
            $totalInventario = $query->count();
            $perPage = $totalInventario > 0 ? $totalInventario : 10;
        }

        // Obtener paginación (igual que ProductoController)
        $paginator = $query->paginate($perPage)
            ->appends([
                'search' => $search,
                'per_page' => $perPage,
                'tienda_id' => $tiendaId
            ]);

        // Transformar los datos a la estructura PaginatedData que espera TypeScript
        $tiendasAgrupadas = [
            'data' => [],
            'current_page' => $paginator->currentPage(),
            'first_page_url' => $paginator->url(1),
            'from' => $paginator->firstItem() ?? 0,
            'last_page' => $paginator->lastPage(),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'links' => $paginator->linkCollection()->toArray(),
            'next_page_url' => $paginator->nextPageUrl(),
            'path' => $paginator->path(),
            'per_page' => $paginator->perPage(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'to' => $paginator->lastItem() ?? 0,
            'total' => $paginator->total(),
        ];

        // Agrupar los datos de cada página
        $tiendasMap = [];
        foreach ($paginator->items() as $item) {
            $tiendaId = $item->tienda_id;

            if (!isset($tiendasMap[$tiendaId])) {
                $tiendasMap[$tiendaId] = [
                    'id' => $item->id,
                    'tienda_id' => $tiendaId,
                    'tienda_nombre' => $item->tienda->nombre,
                    'tienda_is_active' => $item->tienda->is_active,
                    'productos' => []
                ];
            }

            $tiendasMap[$tiendaId]['productos'][] = [
                'id' => $item->id,
                'producto_id' => $item->producto_id,
                'producto_nombre' => $item->producto->nombre,
                'producto_categoria' => $item->producto->categoria,
                'cantidad' => $item->cantidad,
                'cantidad_minima' => $item->cantidad_minima,
                'cantidad_maxima' => $item->cantidad_maxima,
                'ultima_actualizacion' => $item->ultima_actualizacion?->format('d/m/Y'),
                'created_at' => $item->created_at?->format('d/m/Y'),
            ];
        }

        // Los datos transformados van en 'data'
        $tiendasAgrupadas['data'] = array_values($tiendasMap);

        // Obtener tiendas para el filtro
        $tiendas = Tienda::where('is_active', true)
            // ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return Inertia::render('inventarioTienda/index', [
            // Estructura EXACTA que espera TypeScript
            'inventariosTienda' => $tiendasAgrupadas,

            // Datos para filtros (opcional, puedes ponerlos en tiendasAgrupadas si prefieres)
            'tiendas' => $tiendas,
            'filters' => [
                'search' => $search,
                'tienda_id' => $tiendaId,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tiendas = Tienda::where('is_active', true)
            // ->orderBy('nombre')
            ->get(['id', 'nombre']);

        // Obtener TODOS los productos activos inicialmente
        $productos = Producto::where('activo', true)
            // ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return Inertia::render('inventarioTienda/create', [
            'productos' => $productos,
            'tiendas' => $tiendas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InventarioTiendaRequest $request)
    {
        InventarioTienda::create($request->validated());

        return to_route('inventarioTienda.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventarioTienda $inventarioTienda)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventarioTienda $inventarioTienda)
    {
        // Encuentra el registro específico del inventario
        $inventario = InventarioTienda::with(['tienda', 'producto'])
            ->findOrFail($inventarioTienda->id);


        // Obtener todas las tiendas activas
        $tiendas = Tienda::where('is_active', true)
            // ->orderBy('nombre')
            ->get(['id', 'nombre', 'is_active']);

        // Obtener todos los productos activos
        $productos = Producto::where('activo', true)
            // ->orderBy('nombre')
            ->get(['id', 'nombre', 'categoria']);

        // Obtener productos que YA están en esta tienda (para el filtro)
        $productosEnTienda = InventarioTienda::where('tienda_id', $inventario->tienda_id)
            ->where('id', '!=', $inventario->id) // Excluir el producto actual
            ->pluck('producto_id')
            ->toArray();

        // Filtrar productos disponibles (todos menos los que ya están en la tienda)
        $productosDisponibles = $productos->filter(function ($producto) use ($productosEnTienda) {
            return !in_array($producto->id, $productosEnTienda);
        })->values();

        return Inertia::render('inventarioTienda/edit', [
            'inventario' => [
                'id' => $inventario->id,
                'tienda_id' => $inventario->tienda->id,
                'tienda_nombre' => $inventario->tienda->nombre,
                'producto_id' => $inventario->producto->id,
                'producto_nombre' => $inventario->producto->nombre,
                'producto_categoria' => $inventario->producto->categoria,
                'cantidad' => (float) $inventario->cantidad,
                'cantidad_minima' => (float) $inventario->cantidad_minima,
                'cantidad_maxima' => (float) $inventario->cantidad_maxima,
                'ultima_actualizacion' => $inventario->ultima_actualizacion?->format('d/m/Y'),
                'created_at' => $inventario->created_at?->format('d/m/Y'),
            ],
            'tiendas' => $tiendas,
            'productos' => $productos,
            'productosDisponibles' => $productosDisponibles,
            'productosEnTienda' => $productosEnTienda, // Para validación en frontend
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InventarioTiendaRequest $request, InventarioTienda $inventarioTienda)
    {
        $inventarioTienda->update($request->validated());

        return to_route('inventarioTienda.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventarioTienda $inventarioTienda)
    {
        $inventarioTienda->delete();

        return to_route('inventarioTienda.index');
    }

    public function getProductosNoAsignados($tiendaId)
    {
        // Obtener IDs de productos que YA están en la tienda
        $productosAsignadosIds = InventarioTienda::where('tienda_id', $tiendaId)
            ->pluck('producto_id')
            ->toArray();

        // Obtener productos que NO están en la tienda
        $productos = Producto::where('activo', true)
            ->whereNotIn('id', $productosAsignadosIds)
            // ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($productos);
    }
}
