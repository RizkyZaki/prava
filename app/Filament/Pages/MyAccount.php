<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;

use Filament\Notifications\Notification;

use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeProfile;

class MyAccount extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'My Account';
    protected static ?int $navigationSort = 9999;

    protected string $view = 'filament.pages.my-account';

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

        $profile = EmployeeProfile::firstOrNew([
            'user_id' => $user->id,
        ]);

        $this->form->fill([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'employeeProfile' => $profile->toArray(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

                Section::make('User Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('user.name')
                            ->label('Name')
                            ->required(),

                        TextInput::make('user.email')
                            ->label('Email')
                            ->email()
                            ->required(),
                    ]),

                Section::make('Employee Profile')
                    ->columns(3)
                    ->schema([
                        TextInput::make('employeeProfile.national_id_number')
                            ->label('NIK'),

                        DatePicker::make('employeeProfile.birth_date')
                            ->label('Tanggal Lahir'),

                        DatePicker::make('employeeProfile.hire_date')
                            ->label('Tanggal Masuk'),

                        TextInput::make('employeeProfile.bjb_bank_account_number')
                            ->label('Rekening BJB'),

                        TextInput::make('employeeProfile.tax_identification_number')
                            ->label('NPWP'),

                        TextInput::make('employeeProfile.position_title')
                            ->label('Jabatan'),

                        Textarea::make('employeeProfile.address')
                            ->label('Alamat')
                            ->columnSpanFull(),

                        TextInput::make('employeeProfile.phone_number')
                            ->label('No HP'),

                        TextInput::make('employeeProfile.personal_email')
                            ->email()
                            ->label('Email Pribadi'),

                        Select::make('employeeProfile.marital_status')
                            ->label('Status Pernikahan')
                            ->options([
                                'single' => 'Belum Menikah',
                                'married' => 'Menikah',
                            ]),

                        TextInput::make('employeeProfile.last_education')
                            ->label('Pendidikan Terakhir'),

                        FileUpload::make('employeeProfile.profile_photo')
                            ->label('Foto Profil')
                            ->image()
                            ->directory('profile-photos'),

                        FileUpload::make('employeeProfile.id_card_attachment')
                            ->label('Attachment KTP')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->directory('ktp-attachments'),

                        FileUpload::make('employeeProfile.tax_attachment')
                            ->label('Attachment NPWP')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->directory('npwp-attachments'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $user = Auth::user();
        $user->update($state['user']);

        EmployeeProfile::updateOrCreate(
            ['user_id' => $user->id],
            $state['employeeProfile']
        );

        Notification::make()
            ->title('Account updated successfully!')
            ->success()
            ->send();
    }
}
