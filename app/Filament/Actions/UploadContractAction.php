<?php
namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeProfile;

class UploadContractAction
{
    public static function make(): Action
    {
        return Action::make('upload_contract')
            ->label(fn ($record) => ($record->employeeProfile && $record->employeeProfile->attachment_kontrak)
                ? 'Edit Kontrak'
                : 'Upload Kontrak')
            ->icon('heroicon-o-document-arrow-up')
            ->visible(fn ($record) =>
                ($user = Auth::user()) &&
                method_exists($user, 'hasRole') &&
                $user->hasRole('super_admin')
            )
            ->form([
                Forms\Components\FileUpload::make('attachment_kontrak')
                    ->label('Kontrak (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->required()
                    ->directory('contracts'),
            ])
            ->action(function (array $data, $record) {
                // ensure there is an EmployeeProfile for the user
                $profile = $record->employeeProfile;
                if (! $profile) {
                    $profile = EmployeeProfile::firstOrCreate([
                        'user_id' => $record->id,
                    ]);
                }

                // FileUpload returns a string path; assign and persist
                $profile->attachment_kontrak = $data['attachment_kontrak'];
                $profile->save();

                Notification::make()
                    ->title('Kontrak berhasil diunggah')
                    ->success()
                    ->send();
            });
    }
}
