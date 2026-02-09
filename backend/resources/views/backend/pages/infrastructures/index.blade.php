@extends('backend.layouts.app')
@section('admin-content')
    <div class="max-w-xl mx-7">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-lg font-semibold">Infrastructures</h1>
            <a href="#" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 font-medium text-sm shadow">Créer une infrastructure</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border rounded-lg bg-white dark:bg-gray-900 text-xs align-middle">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-2 py-1 font-semibold text-left w-10">ID</th>
                        <th class="px-2 py-1 font-semibold text-left">Nom</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- À remplir avec les infrastructures --}}
                </tbody>
            </table>
        </div>
    </div>
@endsection
