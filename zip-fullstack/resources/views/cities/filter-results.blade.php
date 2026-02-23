<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cities in ' . $county['name'] . ' starting with ' . $selectedLetter) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4 flex justify-between items-center">
                        <a href="{{ route('cities.filter') }}" class="text-blue-500 hover:underline">← Back to Filter</a>
                        @if(count($cities) > 0 && $auth->check)
                        <div class="flex gap-2">
                            <a href="{{ route('export.cities.filtered.csv', ['countyId' => $countyId, 'letter' => $selectedLetter]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Export to CSV
                            </a>
                            <a href="{{ route('export.cities.filtered.pdf', ['countyId' => $countyId, 'letter' => $selectedLetter]) }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Export to PDF
                            </a>
                            <button
                                type="button"
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'email-filtered-cities-export')"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Email PDF
                            </button>
                        </div>
                        @endif
                    </div>

                    @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-600 px-4 py-3 rounded mb-4">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-600 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(count($cities) > 0)
                    <table class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700">
                                <th class="border px-4 py-2 text-left">City Name</th>
                                <th class="border px-4 py-2 text-left">Zip Code</th>
                                <th class="border px-4 py-2 text-left">County</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cities as $city)
                            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="border px-4 py-2">
                                    <a href="{{ route('cities.show', ['countyId' => $countyId, 'cityId' => $city['id']]) }}" class="text-blue-500 hover:underline">
                                        {{ $city['name'] }}
                                    </a>
                                </td>
                                <td class="border px-4 py-2">{{ $city['zip'] }}</td>
                                <td class="border px-4 py-2">{{ $city['county'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-center py-4 text-gray-600 dark:text-gray-400">
                        No cities found starting with "{{ $selectedLetter }}" in {{ $county['name'] }}.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-modal name="email-filtered-cities-export" :show="$errors->emailFilteredCitiesExport->isNotEmpty()" focusable>
        <form method="POST" action="{{ route('export.cities.filtered.email', ['countyId' => $countyId, 'letter' => $selectedLetter]) }}" class="p-6">
            @csrf

            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Send Filtered Cities PDF</h3>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 text-xl leading-none">×</button>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Enter the destination email address. The filtered PDF export will be attached.
            </p>

            <div>
                <x-input-label for="filtered_cities_export_email" :value="__('Email Address')" />
                <x-text-input id="filtered_cities_export_email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->emailFilteredCitiesExport->get('email')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                <x-primary-button>Send Email</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>