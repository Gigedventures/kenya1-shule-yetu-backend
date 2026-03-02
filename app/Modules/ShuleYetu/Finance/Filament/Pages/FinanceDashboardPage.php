<?php

namespace App\Modules\ShuleYetu\Finance\Filament\Pages;

use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class FinanceDashboardPage extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Shule Finance';

    protected static ?string $navigationLabel = 'Finance Dashboard';

    protected static string $view = 'filament.pages.finance-dashboard';

    protected static ?string $slug = 'finance-dashboard';

    public ?string $term_id = null;

    public array $summary = [
        'total_billed' => 0,
        'total_collected' => 0,
        'outstanding' => 0,
        'collection_percentage' => 0,
    ];

    public function mount(): void
    {
        if (!$this->term_id) {
            $this->term_id = ShuleTerm::query()->orderByDesc('start_date')->value('id');
        }
        $this->form->fill([
            'term_id' => $this->term_id,
        ]);
        $this->loadSummary();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('term_id')
                ->label('Term')
                ->options(ShuleTerm::query()->orderBy('name')->pluck('name', 'id'))
                ->live()
                ->afterStateUpdated(function ($state): void {
                    $this->term_id = $state;
                    $this->loadSummary();
                }),
        ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('finance.reports.view') ?? false;
    }

    private function loadSummary(): void
    {
        if (!$this->term_id) {
            $this->summary = [
                'total_billed' => 0,
                'total_collected' => 0,
                'outstanding' => 0,
                'collection_percentage' => 0,
            ];
            return;
        }

        $this->summary = app(FeeService::class)->getFeeSummaryReport($this->term_id);
    }
}
