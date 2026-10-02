<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;

class Login extends BaseLogin
{
    protected function getFormActions(): array
    {
        return [
            parent::getFormActions()[0],
            Action::make('sso')
                ->label('Login dengan SSO Perusahaan')
                ->url(route('sso.redirect'))
                ->color('gray'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return parent::form($schema);
    }
}
