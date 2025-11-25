<?php

namespace TomatoPHP\FilamentPayments\Filament\Resources;

use Illuminate\Support\Carbon;
use TomatoPHP\FilamentPayments\Filament\Resources\PaymentResource\Pages;
use TomatoPHP\FilamentPayments\Filament\Resources\PaymentResource\RelationManagers;
use TomatoPHP\FilamentPayments\Models\Payment;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return trans('filament-payments::messages.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('filament-payments::messages.payments.title');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('filament-payments::messages.payments.title');
    }

    public static function getLabel(): ?string
    {
        return trans('filament-payments::messages.payments.title');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trx')
                    ->label(trans('filament-payments::messages.payments.columns.transaction_id'))
                    ->sortable(),
                TextColumn::make('method_name')
                    ->label(trans('filament-payments::messages.payments.columns.method_name'))
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(trans('filament-payments::messages.payments.columns.amount'))
                    ->formatStateUsing(function (Payment $record) {
                        return  Number::currency($record->amount, in: $record->method_currency) . " + " . Number::currency($record->charge, in: $record->method_currency) . '<br>' . Number::currency(($record->amount + $record->charge), in: $record->method_currency);
                    })->html(),

                TextColumn::make('rate')
                    ->label(trans('filament-payments::messages.payments.columns.conversion'))
                    ->formatStateUsing(function (Payment $record) {
                        return  Number::currency(1, in: 'USD') . " = " . Number::currency($record->rate, in: $record->method_currency) . '<br>' . Number::currency($record->final_amount, in: 'USD');
                    })->html(),

                TextColumn::make('status')
                    ->label(trans('filament-payments::messages.payments.columns.status'))
                    ->badge()
                    ->state(fn($record) => match ($record->status) {
                        0 => trans('filament-payments::messages.payments.columns.processing'),
                        1 => trans('filament-payments::messages.payments.columns.completed'),
                        2 => trans('filament-payments::messages.payments.columns.cancelled'),
                        default => trans('filament-payments::messages.payments.columns.initiated'),
                    })
                    ->icon(fn($record) => match ($record->status) {
                        0 => 'heroicon-o-clock',
                        1 => 'heroicon-s-check-circle',
                        2 => 'heroicon-s-x-circle',
                        default => 'heroicon-s-x-circle',
                    })
                    ->color(fn($record) => match ($record->status) {
                        0 => 'info',
                        1 => 'success',
                        2 => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('filament-payments::messages.payments.columns.date'))
                    ->dateTime('d/m/Y h:iA')
                    ->description(fn ($record): string => Carbon::parse($record->created_at)->diffForHumans()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('filament-payments::messages.payments.columns.status'))
                    ->options([
                        0 => trans('filament-payments::messages.payments.columns.processing'),
                        1 => trans('filament-payments::messages.payments.columns.completed'),
                        2 => trans('filament-payments::messages.payments.columns.cancelled'),
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('status')
                    ->label(trans('filament-payments::messages.payments.columns.status')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\ViewAction::make(),
            ])
            ->searchable();
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
