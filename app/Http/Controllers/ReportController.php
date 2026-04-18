<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function extrato(Request $request)
    {
        $user  = Auth::user();
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $transactions = $user->transactions()
            ->with(['account', 'category'])
            ->ofMonth($month, $year)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        // Group by day
        $byDay = $transactions->groupBy(fn ($t) => $t->date->format('Y-m-d'));

        $totalIncome   = $transactions->where('type', 'income')->sum('amount');
        $totalExpense  = $transactions->where('type', 'expense')->sum('amount');
        $totalTransfer = $transactions->where('type', 'transfer')->sum('amount');
        $balance       = $totalIncome - $totalExpense;

        $monthNames = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

        return view('reports.extrato', compact(
            'month', 'year', 'byDay',
            'totalIncome', 'totalExpense', 'totalTransfer', 'balance',
            'monthNames'
        ));
    }

    public function index(Request $request)
    {
        $year  = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        $user = Auth::user();

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[$m] = [
                'income'  => $user->transactions()->income()->whereYear('date', $year)->whereMonth('date', $m)->sum('amount'),
                'expense' => $user->transactions()->expense()->whereYear('date', $year)->whereMonth('date', $m)->sum('amount'),
            ];
        }

        $expenseByCategory = $user->transactions()
            ->expense()
            ->ofMonth($month, $year)
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($group) => [
                'name'  => $group->first()->category?->name ?? 'Sem categoria',
                'color' => $group->first()->category?->color ?? '#6B7280',
                'total' => $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $incomeByCategory = $user->transactions()
            ->income()
            ->ofMonth($month, $year)
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($group) => [
                'name'  => $group->first()->category?->name ?? 'Sem categoria',
                'color' => $group->first()->category?->color ?? '#10B981',
                'total' => $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $totalIncome  = $user->transactions()->income()->ofMonth($month, $year)->sum('amount');
        $totalExpense = $user->transactions()->expense()->ofMonth($month, $year)->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        return view('reports.index', compact(
            'year', 'month', 'monthlyData',
            'expenseByCategory', 'incomeByCategory',
            'totalIncome', 'totalExpense', 'balance'
        ));
    }
}
