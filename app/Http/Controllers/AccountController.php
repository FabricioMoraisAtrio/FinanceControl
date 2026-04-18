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
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:checking,savings,cash,investment,credit_card',
            'initial_balance' => 'required|numeric',
            'color'           => 'required|string|max:7',
            'icon'            => 'required|string|max:50',
        ]);

        Auth::user()->accounts()->create($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Conta criada com sucesso!');
    }

    public function edit(Account $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        return view('accounts.edit', compact('account'));
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
