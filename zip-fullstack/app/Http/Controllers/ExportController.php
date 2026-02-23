<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\County;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export counties to CSV
     */
    public function countiesCSV()
    {
        $counties = County::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn(County $county) => [
                'id' => (int) $county->id,
                'name' => $county->name,
            ])
            ->values()
            ->all();

        $filename = 'counties-' . date('Y-m-d-His') . '.csv';

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ];

        $callback = function () use ($counties) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name']);

            foreach ($counties as $county) {
                fputcsv($file, [$county['id'], $county['name']]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export counties to PDF
     */
    public function countiesPDF()
    {
        $counties = County::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn(County $county) => [
                'id' => (int) $county->id,
                'name' => $county->name,
            ])
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.counties-pdf', [
            'counties' => $counties,
            'title' => 'Counties List',
            'date' => date('Y-m-d H:i:s'),
        ]);

        return $pdf->download('counties-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Export cities in a county to CSV
     */
    public function citiesCSV(int $countyId)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return redirect()->back()->withErrors(['error' => 'County not found']);
        }

        $cities = $this->citiesForCounty($county);

        $filename = 'cities-' . $county->name . '-' . date('Y-m-d-His') . '.csv';

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ];

        $callback = function () use ($cities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Zip Code', 'County']);

            foreach ($cities as $city) {
                fputcsv($file, [
                    $city['id'],
                    $city['name'],
                    $city['zip'],
                    $city['county'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export cities in a county to PDF
     */
    public function citiesPDF(int $countyId)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return redirect()->back()->withErrors(['error' => 'County not found']);
        }

        $cities = $this->citiesForCounty($county);

        $countyData = [
            'id' => (int) $county->id,
            'name' => $county->name,
        ];

        $pdf = Pdf::loadView('exports.cities-pdf', [
            'county' => $countyData,
            'cities' => $cities,
            'title' => 'Cities in ' . $county->name,
            'date' => date('Y-m-d H:i:s'),
        ]);

        return $pdf->download('cities-' . $county->name . '-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Export filtered cities to CSV
     */
    public function filteredCitiesCSV(int $countyId, string $letter)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return redirect()->back()->withErrors(['error' => 'County not found']);
        }

        $cities = $this->citiesForCounty($county);
        $targetLetter = mb_strtoupper($letter, 'UTF-8');
        $filteredCities = array_values(array_filter($cities, function (array $city) use ($targetLetter) {
            return mb_strtoupper(mb_substr($city['name'], 0, 1, 'UTF-8'), 'UTF-8') === $targetLetter;
        }));

        $filename = 'cities-' . $county->name . '-' . $targetLetter . '-' . date('Y-m-d-His') . '.csv';

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ];

        $callback = function () use ($filteredCities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Zip Code', 'County']);

            foreach ($filteredCities as $city) {
                fputcsv($file, [
                    $city['id'],
                    $city['name'],
                    $city['zip'],
                    $city['county'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export filtered cities to PDF
     */
    public function filteredCitiesPDF(int $countyId, string $letter)
    {
        $county = County::query()->find($countyId);

        if (!$county) {
            return redirect()->back()->withErrors(['error' => 'County not found']);
        }

        $cities = $this->citiesForCounty($county);
        $targetLetter = mb_strtoupper($letter, 'UTF-8');
        $filteredCities = array_values(array_filter($cities, function (array $city) use ($targetLetter) {
            return mb_strtoupper(mb_substr($city['name'], 0, 1, 'UTF-8'), 'UTF-8') === $targetLetter;
        }));

        $countyData = [
            'id' => (int) $county->id,
            'name' => $county->name,
        ];

        $pdf = Pdf::loadView('exports.cities-pdf', [
            'county' => $countyData,
            'cities' => $filteredCities,
            'title' => 'Cities in ' . $county->name . ' starting with ' . $targetLetter,
            'date' => date('Y-m-d H:i:s'),
        ]);

        return $pdf->download('cities-' . $county->name . '-' . $targetLetter . '-' . date('Y-m-d-His') . '.pdf');
    }

    private function citiesForCounty(County $county): array
    {
        return City::query()
            ->where('county_id', $county->id)
            ->orderBy('name')
            ->get()
            ->map(fn(City $city) => [
                'id' => (int) $city->id,
                'name' => $city->name,
                'zip' => (string) $city->zip_code,
                'county' => $county->name,
            ])
            ->values()
            ->all();
    }
}
