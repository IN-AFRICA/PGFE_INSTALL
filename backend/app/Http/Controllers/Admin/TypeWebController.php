<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\SchoolTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;

final class TypeWebController extends Controller
{
    public function index()
    {
        $types = Type::query()->orderBy('title')->paginate(20, ['id', 'title']);

        return view('backend.pages.types.index', compact('types'));
    }

    public function create()
    {
        $enumValues = SchoolTypeEnum::values();

        return view('backend.pages.types.create', compact('enumValues'));
    }

    public function store(Request $request)
    {
        $values = implode(',', SchoolTypeEnum::values());
        $data = $request->validate([
            'title' => ['required', 'string', 'in:'.$values, 'unique:types,title'],
        ]);
        Type::create($data);

        return redirect()->route('admin.types.index')->with('success', 'Type créé.');
    }

    public function edit(Type $type)
    {
        $enumValues = SchoolTypeEnum::values();

        return view('backend.pages.types.edit', compact('type', 'enumValues'));
    }

    public function update(Request $request, Type $type)
    {
        $values = implode(',', SchoolTypeEnum::values());
        $data = $request->validate([
            'title' => ['required', 'string', 'in:'.$values, 'unique:types,title,'.$type->id],
        ]);
        $type->update($data);

        return redirect()->route('admin.types.index')->with('success', 'Type mis à jour.');
    }

    public function destroy(Type $type)
    {
        $type->delete();

        return redirect()->route('admin.types.index')->with('success', 'Type supprimé.');
    }
}
