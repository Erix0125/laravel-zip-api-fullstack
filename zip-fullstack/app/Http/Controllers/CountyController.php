<?php

namespace App\Http\Controllers;

use App\Models\County;
use Illuminate\Http\Request;

class CountyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $counties = County::query()->orderBy('name')->get(['id', 'name']);

        return view('counties.index', ['counties' => $counties]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('counties.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties,name',
        ]);

        County::query()->create([
            'name' => $validated['name'],
        ]);

        return redirect()->route('counties.index')->with('success', 'County created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $countyId)
    {
        $county = County::query()->find((int) $countyId);

        if (!$county) {
            return redirect()->route('counties.index')->withErrors(['error' => 'County not found']);
        }

        return view('counties.show', ['county' => $county]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $countyId)
    {
        $county = County::query()->find((int) $countyId);

        if (!$county) {
            return redirect()->route('counties.index')->withErrors(['error' => 'County not found']);
        }

        return view('counties.edit', ['county' => $county]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $countyId)
    {
        $county = County::query()->find((int) $countyId);

        if (!$county) {
            return redirect()->route('counties.index')->withErrors(['error' => 'County not found']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties,name,' . $county->id,
        ]);

        $county->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('counties.index')->with('success', 'County updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $countyId)
    {
        $county = County::query()->find((int) $countyId);

        if (!$county) {
            return redirect()->route('counties.index')->withErrors(['error' => 'County not found']);
        }

        $county->delete();

        return redirect()->route('counties.index')->with('success', 'County deleted successfully!');
    }
}
