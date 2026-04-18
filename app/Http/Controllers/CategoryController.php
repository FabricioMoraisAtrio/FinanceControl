<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Auth::user()->categories()->orderBy('type')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'type'  => 'required|in:income,expense',
            'color' => 'required|string|max:7',
            'icon'  => 'required|string|max:50',
        ]);

        Auth::user()->categories()->create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(Category $category)
    {
        abort_if($category->user_id !== Auth::id() && $category->user_id !== null, 403);
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        abort_if($category->user_id !== Auth::id() && $category->user_id !== null, 403);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'type'  => 'required|in:income,expense',
            'color' => 'required|string|max:7',
            'icon'  => 'required|string|max:50',
        ]);

        $oldType = $category->type;
        $category->update($validated);

        $synced = 0;
        if ($request->boolean('sync_transactions') && $oldType !== $validated['type']) {
            $synced = \App\Models\Transaction::where('category_id', $category->id)
                ->where('type', $oldType)
                ->update(['type' => $validated['type']]);
        }

        $msg = 'Categoria atualizada com sucesso!';
        if ($synced > 0) {
            $typeLabel = $validated['type'] === 'expense' ? 'saída' : 'entrada';
            $msg .= " {$synced} lançamento(s) atualizado(s) para \"{$typeLabel}\".";
        }

        return redirect()->route('categories.index')->with('success', $msg);
    }

    public function destroy(Category $category)
    {
        abort_if($category->user_id !== Auth::id() && $category->user_id !== null, 403);
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Categoria removida com sucesso!');
    }
}
