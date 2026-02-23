<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\County;

class CityFilterController extends Controller
{
    /**
     * Show the ABC filter page
     */
    public function index()
    {
        $counties = County::query()->orderBy('name')->get(['id', 'name']);

        return view('cities.filter', ['counties' => $counties]);
    }

    /**
     * Get available letters for a county (AJAX)
     */
    public function getLetters(int $countyId)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return response()->json(['error' => 'County not found'], 404);
        }

        $letters = City::query()
            ->where('county_id', $countyId)
            ->pluck('name')
            ->map(fn(string $name) => mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8'))
            ->unique()
            ->values()
            ->all();

        $this->sortHungarianLetters($letters);

        return response()->json(['letters' => $letters]);
    }

    /**
     * Get cities by letter for a county
     */
    public function getCitiesByLetter(int $countyId, string $letter)
    {
        $county = County::query()->find($countyId);
        $counties = County::query()->orderBy('name')->get(['id', 'name']);
        $selectedLetter = mb_strtoupper($letter, 'UTF-8');

        if (!$county) {
            return redirect()->back()->withErrors(['error' => 'County not found']);
        }

        $cities = City::query()
            ->where('county_id', $countyId)
            ->orderBy('name')
            ->get()
            ->map(fn(City $city) => [
                'id' => (int) $city->id,
                'name' => $city->name,
                'zip' => (string) $city->zip_code,
                'county' => $county->name,
            ])
            ->filter(fn(array $city) => mb_strtoupper(mb_substr($city['name'], 0, 1, 'UTF-8'), 'UTF-8') === $selectedLetter)
            ->values()
            ->all();

        return view('cities.filter-results', [
            'counties' => $counties,
            'county' => $county,
            'countyId' => $countyId,
            'selectedLetter' => $selectedLetter,
            'cities' => $cities,
        ]);
    }

    private function sortHungarianLetters(array &$letters): void
    {
        $hungarianOrder = [
            'A' => 0,
            'Á' => 0.5,
            'B' => 1,
            'C' => 2,
            'D' => 3,
            'E' => 4,
            'É' => 4.5,
            'F' => 5,
            'G' => 6,
            'H' => 7,
            'I' => 8,
            'Í' => 8.5,
            'J' => 9,
            'K' => 10,
            'L' => 11,
            'M' => 12,
            'N' => 13,
            'O' => 14,
            'Ó' => 14.5,
            'Ö' => 15,
            'Ő' => 15.5,
            'P' => 16,
            'Q' => 17,
            'R' => 18,
            'S' => 19,
            'T' => 20,
            'U' => 21,
            'Ú' => 21.5,
            'Ü' => 22,
            'Ű' => 22.5,
            'V' => 23,
            'W' => 24,
            'X' => 25,
            'Y' => 26,
            'Z' => 27,
        ];

        usort($letters, function (string $left, string $right) use ($hungarianOrder) {
            $leftOrder = $hungarianOrder[$left] ?? 999;
            $rightOrder = $hungarianOrder[$right] ?? 999;

            return $leftOrder <=> $rightOrder;
        });
    }
}
