@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Stock', 'url' => route('admin.stock-articles.index')]]" current="Inventaire & Stock" />

    <div class="space-y-6 animate-in fade-in duration-500">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-zinc-200 pb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="h-10 w-10 rounded bg-zinc-900 flex items-center justify-center text-white shadow-sm">
                        <iconify-icon icon="lucide:package" width="20"></iconify-icon>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tighter text-zinc-950">Stock & Inventaire</h1>
                </div>
                <p class="text-sm text-zinc-500 font-medium italic">Suivi en temps réel des ressources et fournitures scolaires.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <button class="h-9 px-4 rounded-md border border-zinc-200 bg-white text-[11px] font-bold text-zinc-600 hover:bg-zinc-50 transition-all flex items-center gap-2 shadow-sm">
                    <iconify-icon icon="lucide:file-text" width="14"></iconify-icon>
                    RAPPORT
                </button>
                <a href="#" class="h-9 px-4 rounded-md bg-zinc-900 text-[11px] font-bold text-white uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2 shadow-lg shadow-zinc-100">
                    <iconify-icon icon="lucide:plus" width="14"></iconify-icon>
                    Ajouter un article
                </a>
            </div>
        </div>

        <div class="flex items-center justify-between bg-zinc-50/50 p-2 rounded-lg border border-zinc-200/60">
            <div class="flex items-center gap-4 px-2">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Catégorie :</span>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 h-8 px-3 rounded border border-zinc-200 bg-white text-xs font-bold text-zinc-900 hover:border-zinc-400 transition-all shadow-sm">
                            Toutes les fournitures
                            <iconify-icon icon="lucide:chevron-down" width="12" class="text-zinc-400"></iconify-icon>
                        </button>
                        </div>
                </div>
                <div class="h-4 w-px bg-zinc-200"></div>
                <div class="relative group">
                    <iconify-icon icon="lucide:search" class="absolute left-2 top-1/2 -translate-y-1/2 text-zinc-400 group-focus-within:text-zinc-900 transition-colors" width="14"></iconify-icon>
                    <input type="text" placeholder="Filtrer les articles..." class="h-8 pl-8 pr-3 rounded border-transparent bg-transparent text-xs font-medium focus:border-zinc-200 focus:bg-white transition-all outline-none w-48">
                </div>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-zinc-50/50 border-b border-zinc-200">
                        <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] w-20">Réf.</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Désignation de l'article</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Quantité</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Statut</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    {{-- Simulation d'une ligne pour le rendu visuel --}}
                    <tr class="group hover:bg-zinc-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-[10px] font-mono font-bold text-zinc-400">#STK-082</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-zinc-900 tracking-tight">Rames de papier A4 (Double A)</span>
                                <span class="text-[10px] text-zinc-400 font-medium italic">Fournitures administratives</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-zinc-900">45</span>
                                <span class="text-[10px] text-zinc-400 font-bold uppercase">Unités</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded border border-emerald-100 bg-emerald-50 text-[10px] font-bold text-emerald-700 uppercase tracking-widest">
                                En Stock
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1">
                                <button class="h-8 w-8 flex items-center justify-center rounded text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-all">
                                    <iconify-icon icon="lucide:edit-3" width="14"></iconify-icon>
                                </button>
                                <button class="h-8 w-8 flex items-center justify-center rounded text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-all">
                                    <iconify-icon icon="lucide:trash-2" width="14"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    {{-- Fin simulation --}}
                </tbody>
            </table>
            
            {{--
            <div class="py-24 border-t border-zinc-100 flex flex-col items-center text-center">
                <iconify-icon icon="lucide:box-select" class="text-zinc-100 mb-4" width="48"></iconify-icon>
                <h3 class="text-sm font-bold text-zinc-900">Aucun article enregistré</h3>
                <p class="text-xs text-zinc-400 mt-1 max-w-[200px]">Commencez par ajouter un produit pour suivre votre inventaire.</p>
            </div>
            --}}
        </div>
    </div>
@endsection