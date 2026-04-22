<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Auth::user()->accounts()->orderBy('name')->get();
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        $paymentAccounts = Auth::user()->accounts()
            ->whereNotIn('type', ['credit_card'])
            ->where('active', true)
            ->orderBy('name')
            ->get();
        return view('accounts.create', compact('paymentAccounts'));
    }

    public function store(Request $request)
    {
        $isCreditCard = $request->input('type') === 'credit_card';

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'type'               => 'required|in:checking,savings,cash,investment,credit_card',
            'initial_balance'    => $isCreditCard ? 'nullable|numeric' : 'required|numeric',
            'credit_limit'       => $isCreditCard ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'closing_day'        => $isCreditCard ? 'required|integer|min:1|max:28' : 'nullable|integer|min:1|max:28',
            'payment_day'        => $isCreditCard ? 'required|integer|min:1|max:28' : 'nullable|integer|min:1|max:28',
            'payment_account_id' => 'nullable|exists:accounts,id',
            'color'              => 'required|string|max:7',
            'icon'               => 'required|string|max:50',
        ]);

        if ($isCreditCard) {
            $validated['initial_balance'] = 0;
        }

        Auth::user()->accounts()->create($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Conta criada com sucesso!');
    }

    public function edit(Account $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $paymentAccounts = Auth::user()->accounts()
            ->whereNotIn('type', ['credit_card'])
            ->where('active', true)
            ->where('id', '!=', $account->id)
            ->orderBy('name')
            ->get();
        return view('accounts.edit', compact('account', 'paymentAccounts'));
    }

    public function update(Request $request, Account $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'type'               => 'required|in:checking,savings,cash,investment,credit_card',
            'initial_balance'    => 'required|numeric',
            'color'              => 'required|string|max:7',
            'icon'               => 'nullable|string|max:50',
            'active'             => 'boolean',
            'closing_day'        => 'nullable|integer|min:1|max:31',
            'payment_day'        => 'nullable|integer|min:1|max:31',
            'payment_account_id' => 'nullable|exists:accounts,id',
            'credit_limit'       => 'nullable|numeric|min:0',
        ]);

        $validated['active'] = $request->boolean('active', true);
        if (empty($validated['icon'])) {
            unset($validated['icon']);
        }
        $account->update($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Conta atualizada com sucesso!');
    }

    public function destroy(Account $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Conta removida com sucesso!');
    }
}
