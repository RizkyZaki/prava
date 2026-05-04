<?php

namespace App\Services;

use App\Models\FaceData;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Imagine\Image\ImageInterface;
use Imagine\Gd\Imagine;
use Symfony\Component\HttpFoundation\File\File;

class FaceRecognitionService
{
    protected $imagine;
    protected $storagePath = 'faces';

    public function __construct()
    {
        $this->imagine = new Imagine();
    }

    /**
     * Register face untuk user
     * @param User $user
     * @param UploadedFile $imageFile
     * @return FaceData|null
     */
    public function registerFace(User $user, UploadedFile $imageFile): ?FaceData
    {
        try {
            // Validasi file adalah image
            if (!in_array($imageFile->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                throw new \Exception('File harus berupa image (JPEG, PNG, GIF, atau WebP)');
            }

            // Hapus face data lama jika ada
            $user->faceData?->delete();

            // Generate unique filename
            $filename = 'faces/' . $user->id . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

            // Simpan file ke storage
            Storage::disk('local')->putFileAs(
                'faces',
                $imageFile,
                $user->id . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension()
            );

            // Create FaceData record
            $faceData = FaceData::create([
                'user_id' => $user->id,
                'face_image' => $filename,
                'status' => 'active',
            ]);

            return $faceData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Recognize/Match face dengan registered face
     * @param User $user
     * @param UploadedFile $uploadedFile
     * @return bool
     */
    public function recognizeFace(User $user, UploadedFile $uploadedFile): bool
    {
        try {
            // Cek apakah user punya registered face
            $registeredFace = $user->faceData?->active()->first();
            if (!$registeredFace) {
                throw new \Exception('Wajah user belum terdaftar');
            }

            // Try menggunakan Imagine untuk advanced image comparison
            try {
                return $this->recognizeFaceWithImagine($uploadedFile, $registeredFace);
            } catch (\Exception $e) {
                // Fallback ke simple hash comparison
                return $this->recognizeFaceWithSimpleHash($uploadedFile, $registeredFace);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Recognize face menggunakan Imagine library (advanced)
     */
    protected function recognizeFaceWithImagine(UploadedFile $uploadedFile, \App\Models\FaceData $registeredFace): bool
    {
        // Baca kedua image
        $uploadedImage = $this->imagine->open($uploadedFile->getRealPath());
        $storedImage = $this->imagine->open($registeredFace->getFaceImagePath());

        // Resize kedua image ke ukuran sama untuk comparison
        $width = 200;
        $height = 200;
        $uploadedImage->resize(new \Imagine\Image\Box($width, $height));
        $storedImage->resize(new \Imagine\Image\Box($width, $height));

        // Hitung similarity antara 2 image menggunakan histogram
        $similarity = $this->calculateImageSimilarity(
            $uploadedImage,
            $storedImage
        );

        // Threshold untuk match (0-100), adjust sesuai kebutuhan
        $threshold = 70; // 70% similarity = match

        return $similarity >= $threshold;
    }

    /**
     * Recognize face menggunakan simple hash comparison (fallback)
     * Method: Compare file hashes dengan tolerance untuk lighting differences
     */
    protected function recognizeFaceWithSimpleHash(UploadedFile $uploadedFile, \App\Models\FaceData $registeredFace): bool
    {
        try {
            // Baca content dari kedua file
            $uploadedContent = file_get_contents($uploadedFile->getRealPath());
            $storedContent = file_get_contents($registeredFace->getFaceImagePath());

            // Hitung perceptual hash
            $uploadedHash = $this->getPerceptualHash($uploadedContent);
            $storedHash = $this->getPerceptualHash($storedContent);

            // Hitung hamming distance (similarity)
            $distance = $this->hammingDistance($uploadedHash, $storedHash);

            // Normalize ke percentage (0-100)
            // Hash length * 4 bits per hex char = max distance
            $maxDistance = strlen($uploadedHash) * 4;
            $similarity = max(0, 100 - ($distance / $maxDistance * 100));

            // Threshold untuk match (60% similarity untuk simple method)
            $threshold = 60;

            return $similarity >= $threshold;
        } catch (\Exception $e) {
            // Jika error, default false
            return false;
        }
    }

    /**
     * Hitung perceptual hash dari image content
     * Simple implementation: hash pixels
     */
    protected function getPerceptualHash(string $imageContent): string
    {
        try {
            $image = $this->imagine->load($imageContent);
            $size = $image->getSize();

            // Resize ke 8x8 untuk simple hash
            $image->resize(new \Imagine\Image\Box(8, 8));

            $hash = '';
            $avgColor = 0;
            $pixelCount = 0;

            // Calculate average color value
            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $color = $image->getColorAt(new \Imagine\Image\Point($x, $y));
                    $gray = (int) ($color->getRed() * 0.299 + $color->getGreen() * 0.587 + $color->getBlue() * 0.114);
                    $avgColor += $gray;
                    $pixelCount++;
                }
            }

            $avgColor = $pixelCount > 0 ? $avgColor / $pixelCount : 128;

            // Generate hash bits based on average
            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $color = $image->getColorAt(new \Imagine\Image\Point($x, $y));
                    $gray = (int) ($color->getRed() * 0.299 + $color->getGreen() * 0.587 + $color->getBlue() * 0.114);
                    $hash .= ($gray >= $avgColor) ? '1' : '0';
                }
            }

            // Convert binary string to hex
            return base_convert($hash, 2, 16);
        } catch (\Exception $e) {
            // Fallback: return MD5 hash
            return md5($imageContent);
        }
    }

    /**
     * Hitung Hamming distance antara 2 hash
     */
    protected function hammingDistance(string $hash1, string $hash2): int
    {
        // Pad ke panjang yang sama
        $maxLen = max(strlen($hash1), strlen($hash2));
        $hash1 = str_pad($hash1, $maxLen, '0', STR_PAD_LEFT);
        $hash2 = str_pad($hash2, $maxLen, '0', STR_PAD_LEFT);

        $distance = 0;
        for ($i = 0; $i < $maxLen; $i++) {
            if ($hash1[$i] !== $hash2[$i]) {
                $distance++;
            }
        }

        return $distance;
    }

    /**
     * Calculate similarity antara 2 image menggunakan histogram comparison
     * Simple approach: compare histogram dari kedua image
     * @param ImageInterface $image1
     * @param ImageInterface $image2
     * @return float (0-100)
     */
    protected function calculateImageSimilarity(ImageInterface $image1, ImageInterface $image2): float
    {
        try {
            // Convert ke grayscale untuk simplicity
            $image1 = $image1->effects()->grayscale();
            $image2 = $image2->effects()->grayscale();

            // Get histogram dari kedua image
            $hist1 = $this->getImageHistogram($image1);
            $hist2 = $this->getImageHistogram($image2);

            // Hitung chi-square distance antara histograms
            $distance = $this->chiSquareDistance($hist1, $hist2);

            // Convert distance ke similarity score (0-100)
            // Semakin kecil distance, semakin tinggi similarity
            $similarity = max(0, 100 - ($distance * 10)); // Normalize

            return $similarity;
        } catch (\Exception $e) {
            // Default false jika ada error
            return 0;
        }
    }

    /**
     * Get histogram dari image
     */
    protected function getImageHistogram(ImageInterface $image): array
    {
        $histogram = array_fill(0, 256, 0);

        try {
            // Iterate melalui setiap pixel
            $size = $image->getSize();
            for ($y = 0; $y < $size->getHeight(); $y += 5) { // Sample setiap 5 pixel untuk performance
                for ($x = 0; $x < $size->getWidth(); $x += 5) {
                    $color = $image->getColorAt(new \Imagine\Image\Point($x, $y));
                    $gray = (int) ($color->getRed() * 0.299 + $color->getGreen() * 0.587 + $color->getBlue() * 0.114);
                    $histogram[$gray]++;
                }
            }

            // Normalize
            $total = array_sum($histogram);
            if ($total > 0) {
                $histogram = array_map(function ($value) use ($total) {
                    return $value / $total;
                }, $histogram);
            }
        } catch (\Exception $e) {
            // Return empty histogram jika error
            return array_fill(0, 256, 0);
        }

        return $histogram;
    }

    /**
     * Chi-square distance antara 2 histogram
     */
    protected function chiSquareDistance(array $hist1, array $hist2): float
    {
        $distance = 0;

        for ($i = 0; $i < count($hist1); $i++) {
            $a = $hist1[$i];
            $b = $hist2[$i];

            if (($a + $b) > 0) {
                $distance += (($a - $b) ** 2) / ($a + $b);
            }
        }

        return $distance / 2;
    }

    /**
     * Delete face data dari user
     */
    public function deleteFace(User $user): bool
    {
        try {
            $faceData = $user->faceData;
            if ($faceData) {
                // Hapus file dari storage
                if (Storage::disk('local')->exists($faceData->face_image)) {
                    Storage::disk('local')->delete($faceData->face_image);
                }

                // Hapus record dari database
                $faceData->delete();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Check apakah user punya registered face
     */
    public function userHasFace(User $user): bool
    {
        return $user->faceData()?->active()->exists() ?? false;
    }
}
