<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SimuladorComercialPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationLabel = 'Simulador comercial';
    protected static ?string $title = 'Simulador comercial';
    protected static ?string $slug = 'simulador-comercial';
    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 999;

    protected string $view = 'filament.pages.simulador-comercial';

    protected static bool $shouldRegisterNavigation = false;
}
