<?php

namespace App\Modules\ShuleYetu\Exams\Filament\Resources\TermResultsResource\Pages;

use App\Modules\ShuleYetu\Exams\Filament\Resources\TermResultsResource;
use App\Modules\ShuleYetu\Exams\Services\ExamService;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;

class ListTermResults extends ListRecords
{
    protected static string $resource = TermResultsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('calculate')
                ->label('Calculate Results')
                ->visible(fn () => auth()->user()?->hasPermission('exams.results.calculate') ?? false)
                ->form([
                    Forms\Components\Select::make('term_id')
                        ->label('Term')
                        ->options(ShuleTerm::query()->orderBy('name')->pluck('name', 'id'))
                        ->required(),
                    Forms\Components\Toggle::make('ranking_enabled')->label('Enable Ranking'),
                ])
                ->action(function (array $data): void {
                    app(ExamService::class)->calculateTermResults($data['term_id'], [
                        'ranking_enabled' => $data['ranking_enabled'] ?? false,
                    ]);
                }),
        ];
    }
}
