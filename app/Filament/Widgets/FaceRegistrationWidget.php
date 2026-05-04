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
    public bool $showCamera = false;
    public bool $cameraActive = false;
    public ?string $capturedImage = null;

    protected FaceRecognitionService $faceService;

    public function mount(): void
    {
        $this->faceService = app(FaceRecognitionService::class);
        $this->userFace = auth()->user()->faceData?->active()->first();
    }

    public function showRegisterForm(): void
    {
        $this->showForm = true;
        $this->showCamera = true;
        $this->previewImage = null;
        $this->capturedImage = null;
        // Dispatch event to activate camera
        $this->dispatch('opening-camera-form');
    }

    public function hideRegisterForm(): void
    {
        $this->showForm = false;
        $this->showCamera = false;
        $this->cameraActive = false;
        $this->previewImage = null;
        $this->capturedImage = null;
        $this->data = [];
        // Dispatch event to cleanup camera
        $this->dispatch('closing-camera-form');
    }

    public function capturePhoto(): void
    {
        // This will be called from JavaScript via Livewire
        // Just a placeholder - actual capture happens in JavaScript
    }

    public function activateCamera(): void
    {
        $this->cameraActive = true;
    }

    public function saveCapturedImage(string $imageData): void
    {
        try {
            if (!$imageData) {
                Notification::make()
                    ->title('Error')
                    ->body('Gagal mengambil foto')
                    ->danger()
                    ->send();
                return;
            }

            // Save base64 image to data property
            $this->capturedImage = $imageData;
            $this->previewImage = $imageData;
            $this->showCamera = false;
            $this->cameraActive = false;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function retakePhoto(): void
    {
        $this->showCamera = true;
        $this->cameraActive = true;
        $this->capturedImage = null;
        $this->previewImage = null;
        // Re-open camera after retake
        $this->dispatch('opening-camera-form');
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
            if (!$this->capturedImage) {
                Notification::make()
                    ->title('Error')
                    ->body('Silakan ambil foto wajah Anda terlebih dahulu')
                    ->danger()
                    ->send();
                return;
            }

            // Decode base64 image
            $imageData = $this->capturedImage;
            if (str_starts_with($imageData, 'data:image')) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            }

            $imageBinary = base64_decode($imageData);

            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'face_');
            file_put_contents($tempFile, $imageBinary);

            // Create UploadedFile instance
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempFile,
                'face_' . time() . '.jpg',
                'image/jpeg',
                null,
                true
            );

            // Register face using service
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
