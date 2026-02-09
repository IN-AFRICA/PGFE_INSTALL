<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Repechage;

use App\Http\Controllers\Controller;
use App\Models\Repechage;
use Illuminate\Http\Request;

final class ListRepechageController extends Controller
{
    public function index(Request $request)
    {
        $items = Repechage::all();

        return response()->json($items);
    }
}
