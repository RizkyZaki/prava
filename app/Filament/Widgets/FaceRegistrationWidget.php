<?php

namespace App\Filament\Widgets;

use App\Models\FaceData;
use App\Services\FaceRecognitionService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FaceRegistrationWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.widgets.face-registration-widget';

    public ?array $data = [];
    public ?FaceData $userFace = null;
    public bool $showForm = false;
    public ?string $previewImage = null;

    protected FaceRecognitionService $faceService;

    public function mount(): void
    {
        $this->faceService = app(FaceRecognitionService::class);
        $this->userFace = auth()->user()->faceData?->active()->first();
    }

    public function showRegisterForm(): void
    {
        $this->showForm = true;
        $this->previewImage = null;
    }

    public function hideRegisterForm(): void
    {
        $this->showForm = false;
        $this->previewImage = null;
        $this->data = [];
    }

    public function updatedDataFaceImage(): void
    {
        // Generate preview ketika file dipilih
        if ($this->data['face_image'] ?? null) {
            $file = $this->data['face_image'];

            // Jika file adalah temporary uploaded file (dari input)
            if ($file instanceof TemporaryUploadedFile) {
                $this->previewImage = $file->temporaryUrl();
            } elseif (is_string($file)) {
                // Jika string, assume it's base64 atau URL
                if (str_starts_with($file, 'data:')) {
                    $this->previewImage = $file;
                } else {
                    $this->previewImage = asset('storage/' . $file);
                }
            }
        }
    }

    public function registerFace(): void
    {
        try {
            $data = $this->form->getState();

            if (!isset($data['face_image']) || empty($data['face_image'])) {
                Notification::make()
                    ->title('Error')
                    ->body('Silakan pilih gambar wajah terlebih dahulu')
                    ->danger()
                    ->send();
                return;
            }

            $file = $data['face_image'];

            // Jika file adalah temporary uploaded file
            if ($file instanceof TemporaryUploadedFile) {
                // Ambil file asli dari temporary storage
                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $file->getRealPath(),
                    $file->getClientOriginalName(),
                    $file->getMimeType(),
                    null,
                    true
                );

                // Register face menggunakan service
                $faceData = $this->faceService->registerFace(auth()->user(), $uploadedFile);

                // Refresh user face data
                auth()->user()->refresh();
                $this->userFace = auth()->user()->faceData?->active()->first();

                Notification::make()
                    ->title('Success!')
                    ->body('Wajah Anda berhasil didaftarkan')
                    ->success()
                    ->send();

                $this->hideRegisterForm();
                $this->form->fill();
                $this->previewImage = null;
            } else {
                Notification::make()
                    ->title('Error')
                    ->body('Format file tidak valid')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Gagal mendaftarkan wajah: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteFace(): void
    {
        try {
            if (!$this->userFace) {
                return;
            }

            $this->faceService->deleteFace(auth()->user());
            auth()->user()->refresh();
            $this->userFace = null;

            Notification::make()
                ->title('Success!')
                ->body('Data wajah berhasil dihapus')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Gagal menghapus data wajah: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('face_image')
                ->label('Foto Wajah')
                ->image()
                ->imageResizeMode('cover')
                ->imageCropAspectRatio('1')
                ->imageResizeTargetWidth(300)
                ->imageResizeTargetHeight(300)
                ->maxSize(5120)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                ->required(),
        ];
    }
}
