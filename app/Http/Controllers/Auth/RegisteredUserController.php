<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $defaultCategories = [
            // Receitas
            ['name' => 'Salário',              'type' => 'income',  'color' => '#10B981', 'icon' => 'tag'],
            ['name' => 'Freelance',            'type' => 'income',  'color' => '#3B82F6', 'icon' => 'tag'],
            // Despesas
            ['name' => 'Alimentação',          'type' => 'expense', 'color' => '#EF4444', 'icon' => 'tag'],
            ['name' => 'Moradia',              'type' => 'expense', 'color' => '#8B5CF6', 'icon' => 'tag'],
            ['name' => 'Transporte',           'type' => 'expense', 'color' => '#F59E0B', 'icon' => 'tag'],
            ['name' => 'Saúde',                'type' => 'expense', 'color' => '#EC4899', 'icon' => 'tag'],
            ['name' => 'Educação',             'type' => 'expense', 'color' => '#06B6D4', 'icon' => 'tag'],
            ['name' => 'Lazer',                'type' => 'expense', 'color' => '#84CC16', 'icon' => 'tag'],
            ['name' => 'Vestuário',            'type' => 'expense', 'color' => '#F97316', 'icon' => 'tag'],
            ['name' => 'Contas e Serviços',    'type' => 'expense', 'color' => '#6B7280', 'icon' => 'tag'],
            ['name' => 'Investimento efetuado','type' => 'expense', 'color' => '#8B5CF6', 'icon' => 'tag'],
            ['name' => 'Outros - Despesas',    'type' => 'expense', 'color' => '#6B7280', 'icon' => 'tag'],
        ];

        foreach ($defaultCategories as $cat) {
            $user->categories()->create(array_merge($cat, ['is_default' => true]));
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
