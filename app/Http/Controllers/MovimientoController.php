<?php

namespace App\Http\Controllers;

use App\Http\Requests\Movimientos\MovimientoStoreRequest;
use App\Http\Requests\Movimientos\MovimientoUpdateRequest;
use App\Models\Destino;
use App\Models\InventarioTienda;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MovimientoController extends Controller
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
        $query = Movimiento::with([
            'inventario_tienda.tienda',
            'destino',
            'producto',
            'usuario'
        ]);

        // Aplicar filtros
        if ($tiendaId) {
            $query->whereHas('inventario_tienda', function ($q) use ($tiendaId) {
                $q->where('tienda_id', $tiendaId);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('producto', function ($q2) use ($search) {
                    $q2->where('nombre', 'like', "%{$search}%");
                })->orWhereHas('destino', function ($q2) use ($search) {
                    $q2->where('nombre', 'like', "%{$search}%");
                })->orWhereHas('usuario', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhereHas('inventario_tienda.tienda', function ($q2) use ($search) {
                    $q2->where('nombre', 'like', "%{$search}%");
                });
            });
        }

        // Ordenar por tienda y producto
        $query->orderBy('inventario_tienda_id')->orderBy('producto_id');

        // Manejar opción "todos"
        if ($perPage == -1) {
            $totalMovimientos = $query->count();
            $perPage = $totalMovimientos > 0 ? $totalMovimientos : 10;
        }

        // Obtener paginación
        $paginator = $query->paginate($perPage)
            ->appends([
                'search' => $search,
                'per_page' => $perPage,
                'tienda_id' => $tiendaId
            ]);

        // Transformar los datos a la estructura PaginatedData que espera TypeScript
        $tiendasMovimientos = [
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

        // Agrupar los datos de cada página por tienda
        $tiendasMap = [];
        foreach ($paginator->items() as $item) {
            $tiendaId = $item->inventario_tienda->tienda_id;

            if (!isset($tiendasMap[$tiendaId])) {
                $tiendasMap[$tiendaId] = [
                    'tienda_id' => $tiendaId,
                    'tienda_nombre' => $item->inventario_tienda->tienda->nombre,
                    'tienda_is_active' => $item->inventario_tienda->tienda->is_active,
                    'registros' => []
                ];
            }

            $tiendasMap[$tiendaId]['registros'][] = [
                'id' => $item->id,
                'producto_id' => $item->producto_id,
                'producto_nombre' => $item->producto->nombre,
                'producto_categoria' => $item->producto->categoria,
                'entradas' => $item->entradas,
                'salidas' => $item->salidas,
                'traslados' => $item->traslados,
                'venta_diaria' => $item->venta_diaria,
                'destino_id' => $item->destino_id,
                'destino_nombre' => $item->destino->nombre,
                'usuario_id' => $item->usuario_id,
                'usuario_nombre' => $item->usuario->name,
                'inventario_actual' => $item->inventario_tienda->cantidad,
                'created_at' => $item->created_at?->format('d/m/Y'),
                'updated_at' => $item->updated_at?->format('d/m/Y'),
            ];
        }

        // Los datos transformados van en 'data'
        $tiendasMovimientos['data'] = array_values($tiendasMap);

        return Inertia::render('movimientos/index', [
            'movimientos' => $tiendasMovimientos,
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

        $destinos = Destino::all(['id', 'nombre']);

        $usuarios = User::all(['id', 'name']);

        return Inertia::render('movimientos/create', [
            'tiendas' => $tiendas,
            'productos' => $productos,
            'destinos' => $destinos,
            'usuarios' => $usuarios,
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }

    /**
     * Get inventario_tienda_id based on tienda_id and producto_id
     */
    public function getInventarioTiendaId($tiendaId, $productoId)
    {
        $inventario = InventarioTienda::where('tienda_id', $tiendaId)
            ->where('producto_id', $productoId)
            ->first(['id', 'cantidad']);

        if (!$inventario) {
            return response()->json([
                'error' => 'No se encontró inventario para esta combinación de tienda y producto'
            ], 404);
        }

        return response()->json($inventario);
    }

    /**
     * Get products that exist in a store's inventory
     */
    public function getProductosEnTienda($tiendaId)
    {
        $productos = InventarioTienda::where('tienda_id', $tiendaId)
            ->with('producto:id,nombre,categoria')
            ->get()
            ->map(function ($inventario) {
                return [
                    'id' => $inventario->producto->id,
                    'nombre' => $inventario->producto->nombre,
                    'categoria' => $inventario->producto->categoria,
                ];
            });

        return response()->json($productos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MovimientoStoreRequest $request)
    {

        Movimiento::create($request->validated());

        return to_route('movimientos.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Movimiento $movimiento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Movimiento $movimiento)
    {
        // Cargar el movimiento con todas sus relaciones
        $movimiento = Movimiento::with([
            'inventario_tienda.tienda',
            'inventario_tienda.producto',
            'producto',
            'destino',
            'usuario'
        ])->findOrFail($movimiento->id);

        // Obtener todas las tiendas activas
        $tiendas = Tienda::where('is_active', true)
            ->get(['id', 'nombre']);

        // Obtener todos los productos activos
        $productos = Producto::where('activo', true)
            ->get(['id', 'nombre', 'categoria']);

        // Obtener todos los destinos
        $destinos = Destino::all(['id', 'nombre']);

        // Obtener todos los usuarios
        $usuarios = User::all(['id', 'name']);

        // Obtener inventarios de la tienda actual para el selector
        $inventariosTienda = InventarioTienda::where('tienda_id', $movimiento->inventario_tienda->tienda_id)
            ->with('producto')
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'producto_id' => $inv->producto_id,
                    'producto_nombre' => $inv->producto->nombre,
                    'cantidad' => $inv->cantidad,
                ];
            });

        return Inertia::render('movimientos/edit', [
            'movimiento' => [
                'id' => $movimiento->id,
                'inventario_tienda_id' => $movimiento->inventario_tienda_id,
                'tienda_id' => $movimiento->inventario_tienda->tienda_id,
                'tienda_nombre' => $movimiento->inventario_tienda->tienda->nombre,
                'producto_id' => $movimiento->producto_id,
                'producto_nombre' => $movimiento->producto->nombre,
                'destino_id' => $movimiento->destino_id,
                'destino_nombre' => $movimiento->destino->nombre,
                'usuario_id' => $movimiento->usuario_id,
                'usuario_nombre' => $movimiento->usuario->name,
                'entradas' => (float) $movimiento->entradas,
                'salidas' => (float) $movimiento->salidas,
                'traslados' => (float) $movimiento->traslados,
                'venta_diaria' => (float) $movimiento->venta_diaria,
                'inventario_actual' => $movimiento->inventario_actual,
                'created_at' => $movimiento->created_at?->format('d/m/Y'),
                'updated_at' => $movimiento->updated_at?->format('d/m/Y'),
            ],
            'tiendas' => $tiendas,
            'productos' => $productos,
            'destinos' => $destinos,
            'usuarios' => $usuarios,
            'inventariosTienda' => $inventariosTienda,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MovimientoUpdateRequest $request, Movimiento $movimiento)
    {
        $movimiento->update($request->validated());

        return to_route('movimientos.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movimiento $movimiento)
    {
        //
    }
}
