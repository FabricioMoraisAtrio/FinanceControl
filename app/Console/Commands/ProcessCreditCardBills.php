<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\CreditCardBillService;
use Illuminate\Console\Command;

class ProcessCreditCardBills extends Command
{
    protected $signature   = 'bills:process';
    protected $description = 'Fecha faturas de cartão cujo ciclo encerrou e debita na conta de pagamento no vencimento.';

    public function __construct(private CreditCardBillService $billService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Processando faturas — ' . now()->format('d/m/Y'));

        Account::where('type', 'credit_card')
            ->where('active', true)
            ->each(function (Account $card) {
                $this->billService->processAccount($card);
                $this->line("  [{$card->name}] processado.");
            });

        $this->info('Concluído.');
        return self::SUCCESS;
    }
}
