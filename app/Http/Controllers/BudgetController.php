<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $budgets = Auth::user()->budgets()
            ->with('category')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $categories = Auth::user()->categories()
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();

        return view('budgets.index', compact('budgets', 'categories', 'month', 'year'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount'      => 'required|numeric|min:0.01',
            'month'       => 'required|integer|between:1,12',
            'year'        => 'required|integer|min:2020',
        ]);

        Auth::user()->budgets()->updateOrCreate(
            [
                'category_id' => $validated['category_id'],
                'month'       => $validated['month'],
                'year'        => $validated['year'],
            ],
            ['amount' => $validated['amount']]
        );

        return redirect()->route('budgets.index', ['month' => $validated['month'], 'year' => $validated['year']])
            ->with('success', 'Orçamento salvo com sucesso!');
    }

    public function destroy(Budget $budget)
    {
        abort_if($budget->user_id !== Auth::id(), 403);
        $budget->delete();

        return back()->with('success', 'Orçamento removido com sucesso!');
    }
}
