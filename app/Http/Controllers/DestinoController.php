<?php

namespace App\Http\Controllers;

use App\Http\Requests\Destino\DestinoRequest;
use App\Models\Destino;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DestinoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        // Build query
        $query = Destino::query();

        // Apply search filter
        if ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        // Handle "all" records option
        if ($perPage == -1) {
            $totalDestinos = $query->count();
            $perPage = $totalDestinos > 0 ? $totalDestinos : 10;
        }
        return Inertia::render('destinos/index', [
            'destinoData' => $query->paginate($perPage)->appends(['search' => $search, 'per_page' => $perPage]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('destinos/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DestinoRequest $request)
    {
        Destino::create($request->validated());
        return to_route('destinos.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Destino $destino)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Destino $destino)
    {
        return Inertia::render('destinos/edit', [
            'destino' => $destino,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DestinoRequest $request, Destino $destino)
    {
        $destino->update($request->validated());
        return to_route('destinos.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Destino $destino)
    {
        $destino->delete();
        return to_route('destinos.index');
    }
}
