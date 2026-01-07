<?php

namespace App\Filament\Resources\PermittedAbsences\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PermittedAbsenceForm
{
    public static function configure(Schema $schema): Schema
    {
        $isAdmin = Auth::user()?->hasRole('super_admin');

        return $schema
            ->components([
                ComponentsSection::make('Informasi Perizinan')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn() => Auth::id())
                            ->disabled(fn() => !$isAdmin)
                            ->dehydrated()
                            ->label('Karyawan'),

                        Select::make('absence_type')
                            ->options([
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'remote' => 'Remote (Work From Home)',
                            ])
                            ->required()
                            ->live()
                            ->label('Jenis Perizinan')
                            ->helperText('Remote tidak memerlukan bukti lampiran'),

                        DatePicker::make('start_date')
                            ->required()
                            ->label('Tanggal Mulai')
                            ->native(false)
                            ->minDate(now()->subDays(7))
                            ->default(now()),

                        DatePicker::make('end_date')
                            ->required()
                            ->label('Tanggal Selesai')
                            ->native(false)
                            ->minDate(fn($get) => $get('start_date') ?? now())
                            ->default(now()),

                        Textarea::make('reason')
                            ->required()
                            ->rows(4)
                            ->label('Alasan')
                            ->placeholder('Jelaskan alasan perizinan Anda...')
                            ->columnSpanFull(),

                        FileUpload::make('attachment')
                            ->label('Lampiran Bukti')
                            ->image()
                            ->maxSize(2048)
                            ->directory('permitted-absences')
                            ->visibility('private')
                            ->helperText('Upload surat dokter/keterangan (max 2MB). Tidak wajib untuk Remote.')
                            ->hidden(fn($get) => $get('absence_type') === 'remote')
                            ->required(fn($get) => in_array($get('absence_type'), ['izin', 'sakit']))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                ComponentsSection::make('Status Approval')
                    ->schema([
                        Hidden::make('status')
                            ->default('pending'),

                        Hidden::make('approved_by'),

                        Hidden::make('approved_at'),

                        Hidden::make('rejection_reason'),
                    ])
                    ->hidden(fn($context) => $context === 'create'),
            ]);
    }
}
