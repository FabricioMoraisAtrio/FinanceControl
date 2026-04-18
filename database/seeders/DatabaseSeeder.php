<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::factory()->create([
            'name'  => 'Usuário Demo',
            'email' => 'demo@financecontrol.com',
        ]);

        // Contas padrão
        $accounts = [
            ['name' => 'Conta Corrente', 'type' => 'checking',    'balance' => 2500.00, 'color' => '#3B82F6'],
            ['name' => 'Poupança',        'type' => 'savings',     'balance' => 8000.00, 'color' => '#10B981'],
            ['name' => 'Carteira',        'type' => 'cash',        'balance' => 150.00,  'color' => '#F59E0B'],
        ];

        foreach ($accounts as $account) {
            $user->accounts()->create(array_merge($account, ['icon' => 'bank']));
        }

        // Categorias padrão de Despesas
        $expenseCategories = [
            ['name' => 'Alimentação',       'color' => '#EF4444'],
            ['name' => 'Moradia',           'color' => '#8B5CF6'],
            ['name' => 'Transporte',        'color' => '#F59E0B'],
            ['name' => 'Saúde',             'color' => '#EC4899'],
            ['name' => 'Educação',          'color' => '#06B6D4'],
            ['name' => 'Lazer',             'color' => '#84CC16'],
            ['name' => 'Vestuário',         'color' => '#F97316'],
            ['name' => 'Contas e Serviços', 'color' => '#6B7280'],
        ];

        foreach ($expenseCategories as $cat) {
            $user->categories()->create(array_merge($cat, [
                'type'       => 'expense',
                'icon'       => 'tag',
                'is_default' => true,
            ]));
        }

        // Categorias padrão de Receitas
        $incomeCategories = [
            ['name' => 'Salário',      'color' => '#10B981'],
            ['name' => 'Freelance',    'color' => '#3B82F6'],
            ['name' => 'Investimento', 'color' => '#8B5CF6'],
            ['name' => 'Outros',       'color' => '#6B7280'],
        ];

        foreach ($incomeCategories as $cat) {
            $user->categories()->create(array_merge($cat, [
                'type'       => 'income',
                'icon'       => 'tag',
                'is_default' => true,
            ]));
        }
    }
}
