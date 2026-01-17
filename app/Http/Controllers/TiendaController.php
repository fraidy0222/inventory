<?php

namespace App\Http\Controllers;

use App\Http\Requests\TiendaRequest;
use App\Http\Requests\TiendaStoreRequets;
use App\Http\Requests\TiendaUpdateRequets;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TiendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        // Build query
        $query = Tienda::query();

        // Apply search filter
        if ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        // Handle "all" records option
        if ($perPage == -1) {
            $totalTiendas = $query->count();
            $perPage = $totalTiendas > 0 ? $totalTiendas : 10;
        }

        return Inertia::render('tienda/index', [
            'tienda' => $query->paginate($perPage)->appends(['search' => $search, 'per_page' => $perPage]),
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('tienda/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TiendaStoreRequets $request)
    {
        Tienda::create($request->validated());

        return to_route('tiendas.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tienda $tienda)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tienda $tienda)
    {
        return Inertia::render('tienda/edit', [
            'tienda' => $tienda
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TiendaUpdateRequets $request, Tienda $tienda)
    {
        $tienda->update($request->validated());

        return to_route('tiendas.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tienda $tienda)
    {
        $tienda->delete();

        return to_route('tiendas.index');
    }
}
