<?php

namespace App\Filament\Widgets;

use App\Models\Action;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class CostOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Koszty dzisiaj', $this->getCosts('day')),
            Stat::make('Koszty wczoraj', $this->getCosts('day', -1)),
            Stat::make('Koszty przedwczoraj', $this->getCosts('day', -2)),
            Stat::make('Koszty w tym tygodniu', $this->getCosts('week')),
            Stat::make('Koszty w ostatnim tygodniu', $this->getCosts('week', -1)),
            Stat::make('Koszty w przedostatnim tygodniu', $this->getCosts('week', -2)),
            Stat::make('Koszty w tym miesiącu', $this->getCosts('month')),
            Stat::make('Koszty w ostatnim miesiącu', $this->getCosts('month', -1)),
            Stat::make('Koszty w przedostatnim miesiącu', $this->getCosts('month', -2)),
            Stat::make('Koszty całkowite', Number::currency(Action::sum('cost'), in: "USD"))
        ];
    }

    public function getColumns() : int
    {
        return 3;
    }

    private function getCosts(string $time, int $offset = 0)
    {
        return Number::currency(
            Action::whereBetween('updated_at', [
                Carbon::now()->{'startOf' . strtoupper($time)}()->{'add' . strtoupper($time) . 's'}($offset),
                Carbon::now()->{'endOf' . strtoupper($time)}()->{'add' . strtoupper($time) . 's'}($offset)
            ])->sum('cost'),
            in: 'USD'
        );
    }
}
