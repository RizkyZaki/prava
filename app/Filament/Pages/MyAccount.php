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
use Filament\Forms\Components\Repeater;
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
            'employeeProfile' => array_merge(
                $profile->toArray(),
                [
                    'education_histories' => $profile->educationHistories->toArray(),
                    'employment_histories' => $profile->employmentHistories->toArray(),
                ]
            ),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

                /* ================= USER ================= */
                Section::make('User Information')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextInput::make('user.name')
                            ->label('Name')
                            ->required(),

                        TextInput::make('user.email')
                            ->label('Email')
                            ->email()
                            ->required(),
                    ]),

                /* ================= BASIC PROFILE ================= */
                Section::make('Basic Information')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([

                        TextInput::make('employeeProfile.national_id_number')
                            ->label('NIK'),

                        TextInput::make('employeeProfile.position_title')
                            ->label('Jabatan'),

                        Select::make('employeeProfile.marital_status')
                            ->label('Status Pernikahan')
                            ->options([
                                'single' => 'Belum Menikah',
                                'married' => 'Menikah',
                            ]),

                        TextInput::make('employeeProfile.birth_city')
                            ->label('Kota Lahir'),

                        DatePicker::make('employeeProfile.birth_date')
                            ->label('Tanggal Lahir'),

                        DatePicker::make('employeeProfile.hire_date')
                            ->label('Tanggal Masuk'),

                        TextInput::make('employeeProfile.phone_number')
                            ->label('No HP'),

                        TextInput::make('employeeProfile.personal_email')
                            ->label('Email Pribadi')
                            ->email(),

                        TextInput::make('employeeProfile.last_education')
                            ->label('Pendidikan Terakhir'),

                        TextInput::make('employeeProfile.bjb_bank_account_number')
                            ->label('Rekening BJB'),

                        TextInput::make('employeeProfile.tax_identification_number')
                            ->label('NPWP'),

                        Textarea::make('employeeProfile.address')
                            ->label('Alamat')
                            ->columnSpanFull(),
                    ]),

                /* ================= PHOTO ================= */
                Section::make('Profile Photo')
                    ->columns(1)
                    ->schema([
                        FileUpload::make('employeeProfile.profile_photo')
                            ->label('Foto Profil')
                            ->image()
                            ->directory('profile-photos')
                            ->imagePreviewHeight('150')
                            ->columnSpanFull(),
                    ]),

                /* ================= EDUCATION ================= */
                Section::make('Riwayat Pendidikan')
                    ->schema([
                        Repeater::make('employeeProfile.education_histories')
                            ->schema([
                                TextInput::make('institution')
                                    ->label('Institusi')
                                    ->required(),

                                TextInput::make('degree')
                                    ->label('Gelar'),

                                TextInput::make('field_of_study')
                                    ->label('Jurusan'),

                                DatePicker::make('start_date')
                                    ->label('Mulai')
                                    ->native(false),

                                DatePicker::make('end_date')
                                    ->label('Selesai')
                                    ->native(false),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),

                /* ================= EMPLOYMENT ================= */
                Section::make('Riwayat Pekerjaan')
                    ->schema([
                        Repeater::make('employeeProfile.employment_histories')
                            ->schema([
                                TextInput::make('company_name')
                                    ->label('Perusahaan')
                                    ->required(),

                                TextInput::make('position_title')
                                    ->label('Jabatan'),

                                DatePicker::make('start_date')
                                    ->label('Mulai')
                                    ->native(false),

                                DatePicker::make('end_date')
                                    ->label('Selesai')
                                    ->native(false),

                                Textarea::make('responsibilities')
                                    ->label('Tanggung Jawab')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),

                /* ================= ATTACHMENTS ================= */
                Section::make('Attachments')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
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

        $histories = [
            'education_histories' => $state['employeeProfile']['education_histories'] ?? [],
            'employment_histories' => $state['employeeProfile']['employment_histories'] ?? [],
        ];
        unset($state['employeeProfile']['education_histories'], $state['employeeProfile']['employment_histories']);

        $profile = EmployeeProfile::updateOrCreate(
            ['user_id' => $user->id],
            $state['employeeProfile']
        );

        $profile->educationHistories()->delete();
        foreach ($histories['education_histories'] as $edu) {
            $profile->educationHistories()->create($edu);
        }

        $profile->employmentHistories()->delete();
        foreach ($histories['employment_histories'] as $job) {
            $profile->employmentHistories()->create($job);
        }

        Notification::make()
            ->title('Account updated successfully!')
            ->success()
            ->send();
    }
}
