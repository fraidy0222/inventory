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
                    'tienda_id' => $tiendaId,
                    'tienda_nombre' => $item->tienda->nombre,
                    'tienda_is_active' => $item->tienda->is_active,
                    'productos' => []
                ];
            }

            $tiendasMap[$tiendaId]['productos'][] = [
                'id' => $item->id,
                'id' => $item->producto_id,
                'nombre' => $item->producto->nombre,
                'categoria' => $item->producto->categoria,
                'cantidad' => $item->cantidad,
                'cantidad_minima' => $item->cantidad_minima,
                'cantidad_maxima' => $item->cantidad_maxima,
                'ultima_actualizacion' => $item->ultima_actualizacion,
                'created_at' => $item->created_at?->format('d/m/Y H:i'),
            ];
        }

        // Los datos transformados van en 'data'
        $tiendasAgrupadas['data'] = array_values($tiendasMap);

        // Obtener tiendas para el filtro
        $tiendas = Tienda::where('is_active', true)
            ->orderBy('nombre')
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
        $productos = Producto::all();
        $tiendas = Tienda::all();

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
        $inventarioTienda = InventarioTienda::create($request->validated());

        // $inventarioTienda->producto()->attach($request->producto_id);
        // $inventarioTienda->tienda()->attach($request->tienda_id);

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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventarioTienda $inventarioTienda)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventarioTienda $inventarioTienda)
    {
        //
    }
}
