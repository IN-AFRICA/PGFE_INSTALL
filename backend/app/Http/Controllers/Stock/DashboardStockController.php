<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockArticle;
use App\Models\StockOperation;

class DashboardStockController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $schoolId = $user->school_id;
        $filters = [
            'category_id' => $request->input('category_id'),
            'article_id' => $request->input('article_id'),
            'type' => $request->input('type'), // entrée/sortie
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        // Stock total par article (avec filtres)
        $articlesQuery = StockArticle::where('school_id', $schoolId);
        
        if ($filters['category_id']) {
            $articlesQuery->where('category_id', $filters['category_id']);
        }
        
        if ($filters['article_id']) {
            $articlesQuery->where('id', $filters['article_id']);
        }

        // Filtre par type de mouvement (on ne garde que les articles ayant eu ce type de mouvement)
        if ($filters['type']) {
            $articlesQuery->whereHas('entries', function($q) use ($filters) {
                if ($filters['type'] !== 'entrée') $q->whereRaw('1=0'); // Force vide si type sortie
            })->orWhereHas('exits', function($q) use ($filters) {
                if ($filters['type'] !== 'sortie') $q->whereRaw('1=0');
            });
            
            // Correction de la logique pour le type
            $articlesQuery->whereHas('entries', function($q) use ($filters) {
                if ($filters['from']) $q->whereDate('entry_date', '>=', $filters['from']);
                if ($filters['to']) $q->whereDate('entry_date', '<=', $filters['to']);
            }, '>', 0, function($q) use ($filters) {
                 return $filters['type'] === 'entrée';
            })->union(
                StockArticle::where('school_id', $schoolId)
                ->whereHas('exits', function($q) use ($filters) {
                    if ($filters['from']) $q->whereDate('exit_date', '>=', $filters['from']);
                    if ($filters['to']) $q->whereDate('exit_date', '<=', $filters['to']);
                })
                ->where(function($q) use ($filters) {
                    return $filters['type'] === 'sortie';
                })
            );
        }
        
        // Version simplifiée et plus robuste pour le filtrage
        $articlesQuery = StockArticle::where('school_id', $schoolId);
        if ($filters['category_id']) $articlesQuery->where('category_id', $filters['category_id']);
        if ($filters['article_id']) $articlesQuery->where('id', $filters['article_id']);
        
        if ($filters['type']) {
            $articlesQuery->whereHas('entries', function($q) use ($filters) {
                if ($filters['from']) $q->whereDate('entry_date', '>=', $filters['from']);
                if ($filters['to']) $q->whereDate('entry_date', '<=', $filters['to']);
            }, '>', 0)->where(fn() => $filters['type'] === 'entrée')
            ->orWhereHas('exits', function($q) use ($filters) {
                if ($filters['from']) $q->whereDate('exit_date', '>=', $filters['from']);
                if ($filters['to']) $q->whereDate('exit_date', '<=', $filters['to']);
            }, '>', 0)->where(fn() => $filters['type'] === 'sortie');
        }

        $articles = $articlesQuery->with('category')->get();

        // Alertes seuil
        $alertes = $articles->filter(fn($a) => $a->quantity < $a->min_threshold);

        // Statistiques globales des mouvements
        $opsQuery = StockOperation::where('school_id', $schoolId);
        if ($filters['from']) $opsQuery->whereDate('created_at', '>=', $filters['from']);
        if ($filters['to']) $opsQuery->whereDate('created_at', '<=', $filters['to']);
        
        $stats = (clone $opsQuery)->selectRaw('type, count(*) as count, sum(quantite) as total_qty')
            ->groupBy('type')
            ->get();

        // Mouvements récents
        $operationsQuery = StockOperation::where('school_id', $schoolId);
        if ($filters['type']) $operationsQuery->where('type', $filters['type']);
        if ($filters['article_id']) $operationsQuery->where('article_id', $filters['article_id']);
        if ($filters['from']) $operationsQuery->whereDate('created_at', '>=', $filters['from']);
        if ($filters['to']) $operationsQuery->whereDate('created_at', '<=', $filters['to']);
        $operations = $operationsQuery->with('article')->orderByDesc('created_at')->limit(10)->get();

        return response()->json([
            'total_articles' => $articles->count(),
            'stock_total_quantity' => $articles->sum('quantity'),
            'total_entries' => $stats->where('type', 'entrée')->first()->count ?? 0,
            'total_exits' => $stats->where('type', 'sortie')->first()->count ?? 0,
            'qty_entries' => $stats->where('type', 'entrée')->first()->total_qty ?? 0,
            'qty_exits' => $stats->where('type', 'sortie')->first()->total_qty ?? 0,
            'articles' => $articles->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'category' => $a->category ? $a->category->name : null,
                'quantity' => $a->quantity,
                'min_threshold' => $a->min_threshold,
                'max_threshold' => $a->max_threshold,
            ]),
            'alertes' => $alertes->values(),
            'operations_recents' => $operations->map(fn($op) => [
                'date' => $op->created_at,
                'type' => $op->type,
                'article' => $op->article ? $op->article->name : null,
                'quantite' => $op->quantite,
                'reference' => $op->reference,
            ]),
        ]);
    }
}
