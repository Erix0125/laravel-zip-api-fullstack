<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\County;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(int $countyId)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return redirect()->route('counties.index')->withErrors(['error' => 'County not found']);
        }

        $cities = $this->citiesForCounty($county);

        return view('cities.index', ['county' => $county, 'cities' => $cities, 'countyId' => $countyId]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(int $countyId)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return redirect()->route('counties.index')->withErrors(['error' => 'County not found']);
        }

        return view('cities.create', ['county' => $county, 'countyId' => $countyId]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $countyId)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return redirect()->route('counties.index')->withErrors(['error' => 'County not found']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'zip_code' => 'required|integer|min:1000|max:9999',
        ]);

        City::query()->create([
            'name' => $validated['name'],
            'zip_code' => $validated['zip_code'],
            'county_id' => $countyId,
        ]);

        return redirect()->route('cities.index', ['countyId' => $countyId])->with('success', 'City created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $countyId, int $cityId)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return redirect()->route('counties.index')->withErrors(['error' => 'County not found']);
        }

        $city = City::query()
            ->where('id', $cityId)
            ->where('county_id', $countyId)
            ->first();

        if (!$city) {
            return redirect()->route('cities.index', ['countyId' => $countyId])->withErrors(['error' => 'City not found']);
        }

        return view('cities.show', [
            'county' => $county,
            'city' => $this->cityToArray($city, $county),
            'countyId' => $countyId,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $countyId, int $cityId)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return redirect()->route('counties.index')->withErrors(['error' => 'County not found']);
        }

        $city = City::query()
            ->where('id', $cityId)
            ->where('county_id', $countyId)
            ->first();

        if (!$city) {
            return redirect()->route('cities.index', ['countyId' => $countyId])->withErrors(['error' => 'City not found']);
        }

        return view('cities.edit', [
            'county' => $county,
            'city' => $this->cityToArray($city, $county),
            'countyId' => $countyId,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $countyId, int $cityId)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return redirect()->route('counties.index')->withErrors(['error' => 'County not found']);
        }

        $city = City::query()
            ->where('id', $cityId)
            ->where('county_id', $countyId)
            ->first();

        if (!$city) {
            return redirect()->route('cities.index', ['countyId' => $countyId])->withErrors(['error' => 'City not found']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'zip_code' => 'required|integer|min:1000|max:9999',
        ]);

        $city->update([
            'name' => $validated['name'],
            'zip_code' => $validated['zip_code'],
        ]);

        return redirect()->route('cities.index', ['countyId' => $countyId])->with('success', 'City updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $countyId, int $cityId)
    {
        $city = City::query()
            ->where('id', $cityId)
            ->where('county_id', $countyId)
            ->first();

        if (!$city) {
            return redirect()->route('cities.index', ['countyId' => $countyId])->withErrors(['error' => 'City not found']);
        }

        $city->delete();

        return redirect()->route('cities.index', ['countyId' => $countyId])->with('success', 'City deleted successfully!');
    }

    private function citiesForCounty(County $county): array
    {
        return City::query()
            ->where('county_id', $county->id)
            ->orderBy('name')
            ->get()
            ->map(fn(City $city) => $this->cityToArray($city, $county))
            ->values()
            ->all();
    }

    private function cityToArray(City $city, County $county): array
    {
        return [
            'id' => (int) $city->id,
            'name' => $city->name,
            'zip' => (string) $city->zip_code,
            'county' => $county->name,
        ];
    }
}
