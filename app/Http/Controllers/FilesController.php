<?php

namespace App\Http\Controllers;

use App\Http\Requests\Files\FilesRequest;
use App\Models\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FilesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        $query = Files::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%");
            });
        }

        // Handle "all" records option
        if ($perPage == -1) {
            $totalFiles = $query->count();
            $perPage = $totalFiles > 0 ? $totalFiles : 10;
        }

        return Inertia::render('files/index', [
            'archivos' => $query->paginate($perPage)->appends(['search' => $search, 'per_page' => $perPage]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('files/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FilesRequest $request)
    {
        $file = $request->file('archivo');
        $fileName = $file->getClientOriginalName();
        $file = Storage::disk('public')->putFileAs('archivos', $file, $fileName);

        Files::create([
            'nombre' => $request->nombre,
            'archivo' => $fileName,
        ]);

        return to_route('archivos.index');
    }

    public function download($filename)
    {
        if (!Storage::disk('public')->exists('archivos/' . $filename)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download('archivos/' . $filename);
    }

    /**
     * Display the specified resource.
     */
    public function show(Files $files)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Files $archivo)
    {
        return Inertia::render('files/edit', [
            'file' => $archivo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FilesRequest $request, Files $archivo)
    {
        $data = $request->validated();

        if ($request->hasFile('archivo')) {
            // Delete old file
            if (Storage::disk('public')->exists('archivos/' . $archivo->archivo)) {
                Storage::disk('public')->delete('archivos/' . $archivo->archivo);
            }

            // Upload new file
            $file = $request->file('archivo');
            $fileName = $file->getClientOriginalName();
            $file = Storage::disk('public')->putFileAs('archivos', $file, $fileName);

            $data['archivo'] = $fileName;
        } else {
            // Keep old file name if not updating
            unset($data['archivo']);
        }

        $archivo->update($data);

        return to_route('archivos.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Files $archivo)
    {
        if (Storage::disk('public')->exists('archivos/' . $archivo->archivo)) {
            Storage::disk('public')->delete('archivos/' . $archivo->archivo);
        }

        $archivo->delete();

        return to_route('archivos.index');
    }
}
