<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringIncomes extends Command
{
    protected $signature = 'incomes:process-recurring';
    protected $description = 'Duplicate recurring income records when their next interval is due';

    public function handle(): int
    {
        $today = Carbon::today();
        $count = 0;

        $recurring = Income::where('recurring', true)->whereNotNull('recurrence_interval')->get();

        foreach ($recurring as $income) {
            $lastOccurrence = Income::where('user_id', $income->user_id)
                ->where('title', $income->title)
                ->where('source', $income->source)
                ->orderByDesc('received_on')
                ->first();

            $basis = $lastOccurrence?->received_on ?? $income->received_on;

            $next = match ($income->recurrence_interval) {
                'weekly'  => Carbon::parse($basis)->addWeek(),
                'yearly'  => Carbon::parse($basis)->addYear(),
                default   => Carbon::parse($basis)->addMonth(),
            };

            if ($next->lte($today)) {
                Income::create([
                    'user_id'             => $income->user_id,
                    'family_id'           => $income->family_id,
                    'source'              => $income->source,
                    'title'               => $income->title,
                    'amount'              => $income->amount,
                    'note'                => $income->note,
                    'received_on'         => $next->toDateString(),
                    'recurring'           => false,
                    'recurrence_interval' => null,
                ]);
                $count++;
            }
        }

        $this->info("Processed {$count} recurring income occurrences.");
        return self::SUCCESS;
    }
}
