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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'usuario',
            'tienda_relacionada'
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
                'destino_nombre' => $item->destino->nombre ?? '',
                'tienda_relacionada_id' => $item->tienda_relacionada_id,
                'tienda_relacionada_nombre' => $item->tienda_relacionada->nombre ?? '',
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
                'user' => Auth::user(),
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
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $traslados = $data['traslados'] ?? 0;
            $tiendaRelacionadaId = $data['tienda_relacionada_id'] ?? null;

            // 1. Crear el movimiento original (Salida/Traslado desde origen)
            $movimientoOrigen = Movimiento::create($data);

            // 2. Si es un traslado a otra tienda, crear la entrada en la tienda destino
            if ($traslados > 0 && $tiendaRelacionadaId) {
                // Obtener datos necesarios
                $productoId = $data['producto_id'];
                $usuarioId = Auth::id(); // O el usuario que inició la acción

                // Buscar o crear el inventario en la tienda destino
                $inventarioDestino = InventarioTienda::firstOrCreate([
                    'tienda_id' => $tiendaRelacionadaId,
                    'producto_id' => $productoId,
                ], ['cantidad' => 0, 'cantidad_minima' => 0, 'cantidad_maxima' => 0]);

                // Crear el movimiento de entrada en la tienda destino
                Movimiento::create([
                    'inventario_tienda_id' => $inventarioDestino->id,
                    'producto_id' => $productoId,
                    'usuario_id' => $usuarioId,
                    'entradas' => $traslados,
                    'salidas' => 0,
                    'traslados' => 0,
                    'venta_diaria' => 0,
                    'tienda_relacionada_id' => $movimientoOrigen->inventario_tienda->tienda_id, // Referencia cruzada
                    'destino_id' => null,
                ]);
            }

            return to_route('movimientos.index');
        });
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
                'destino_id' => $movimiento->destino_id ?? '',
                'destino_nombre' => $movimiento->destino->nombre ?? '',
                'usuario_id' => $movimiento->usuario_id,
                'usuario_nombre' => $movimiento->usuario->name,
                'entradas' => (float) $movimiento->entradas,
                'salidas' => (float) $movimiento->salidas,
                'traslados' => (float) $movimiento->traslados,
                'venta_diaria' => (float) $movimiento->venta_diaria,
                'inventario_tienda' => [
                    'id' => $movimiento->inventario_tienda->id,
                    'tienda_id' => $movimiento->inventario_tienda->tienda_id,
                    'producto_id' => $movimiento->inventario_tienda->producto_id,
                    'cantidad' => $movimiento->inventario_tienda->cantidad,
                ],
                'created_at' => $movimiento->created_at?->format('d/m/Y'),
                'updated_at' => $movimiento->updated_at?->format('d/m/Y'),
            ],
            'tiendas' => $tiendas,
            'productos' => $productos,
            'destinos' => $destinos,
            'usuarios' => $usuarios,
            'inventariosTienda' => $inventariosTienda,
            'auth' => [
                'user' => Auth::user(),
            ],
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
        $movimiento->delete();

        return to_route('movimientos.index');
    }
    /**
     * Check stock availability in other stores
     */
    public function checkStockAvailability($productoId, Request $request)
    {
        $excludeTiendaId = $request->input('exclude_tienda_id');

        $stocks = InventarioTienda::where('producto_id', $productoId)
            ->where('tienda_id', '!=', $excludeTiendaId)
            ->where('cantidad', '>', 0)
            ->with('tienda:id,nombre')
            ->get(['tienda_id', 'cantidad'])
            ->map(function ($inv) {
                return [
                    'tienda_id' => $inv->tienda_id,
                    'tienda_nombre' => $inv->tienda->nombre,
                    'cantidad' => $inv->cantidad,
                ];
            });

        return response()->json($stocks);
    }

    /**
     * Transfer stock from one store to another and then use it.
     */
    public function transferAndUse(Request $request)
    {
        $request->validate([
            'source_tienda_id' => 'required|exists:tiendas,id',
            'target_tienda_id' => 'required|exists:tiendas,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad_transferir' => 'required|numeric|min:0.01',
            'movimiento_data' => 'required|array',
        ]);

        $sourceTiendaId = $request->input('source_tienda_id');
        $targetTiendaId = $request->input('target_tienda_id');
        $productoId = $request->input('producto_id');
        $cantidadTransferir = $request->input('cantidad_transferir');
        $movimientoData = $request->input('movimiento_data');

        return DB::transaction(function () use ($sourceTiendaId, $targetTiendaId, $productoId, $cantidadTransferir, $movimientoData) {
            // 1. Get Inventory IDs
            $sourceInventario = InventarioTienda::where('tienda_id', $sourceTiendaId)
                ->where('producto_id', $productoId)
                ->firstOrFail();

            $targetInventario = InventarioTienda::firstOrCreate([
                'tienda_id' => $targetTiendaId,
                'producto_id' => $productoId,
            ], ['cantidad' => 0, 'cantidad_minima' => 0, 'cantidad_maxima' => 0]);

            // Check if source has enough stock
            if ($sourceInventario->cantidad < $cantidadTransferir) {
                return response()->json([
                    'errors' => [
                        'cantidad_transferir' => ["La tienda de origen no tiene suficiente stock ({$sourceInventario->cantidad}) para transferir {$cantidadTransferir}."]
                    ]
                ], 422);
            }

            // Check if target has enough stock (current + transfer) for the requested usage
            $trasladosSolicitados = $movimientoData['traslados'] ?? 0;
            $ventaDiariaSolicitada = $movimientoData['venta_diaria'] ?? 0;
            $totalSalida = $trasladosSolicitados + $ventaDiariaSolicitada;
            $stockProyectado = $targetInventario->cantidad + $cantidadTransferir;

            if (round($totalSalida, 2) > round($stockProyectado, 2)) {
                return response()->json([
                    'errors' => [
                        'movimiento_error' => ["La cantidad disponible en la tienda destino ({$stockProyectado} después de transferencia) es insuficiente para realizar los traslados y ventas solicitados ({$totalSalida})."]
                    ]
                ], 422);
            }

            // 2. Create Transfer Out (Source Store)
            Movimiento::create([
                'inventario_tienda_id' => $sourceInventario->id,
                'producto_id' => $productoId,
                'usuario_id' => Auth::id(),
                'destino_id' => null,
                'entradas' => 0,
                'salidas' => 0,
                'traslados' => $cantidadTransferir,
                'venta_diaria' => 0,
                'tienda_relacionada_id' => $targetTiendaId,
            ]);

            // 3. Create Transfer In (Target Store)
            Movimiento::create([
                'inventario_tienda_id' => $targetInventario->id,
                'producto_id' => $productoId,
                'usuario_id' => Auth::id(),
                'destino_id' => null,
                'entradas' => $cantidadTransferir,
                'salidas' => 0,
                'traslados' => 0,
                'venta_diaria' => 0,
                'tienda_relacionada_id' => $sourceTiendaId,
            ]);

            // 4. Create the final requested usage Movement (Target Store)
            $movimientoData['inventario_tienda_id'] = $targetInventario->id;
            $movimientoData['usuario_id'] = Auth::id();

            Movimiento::create($movimientoData);

            return response()->json(['message' => 'Transferencia y movimiento realizados con éxito.']);
        });
    }
}
