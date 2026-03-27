<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessUploadedImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $maxWidth;
    protected $maxHeight;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $maxWidth = 1200, int $maxHeight = 1200)
    {
        $this->filePath = $filePath;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = Storage::disk('public');
        if (!$disk->exists($this->filePath)) {
            Log::warning("File not found for optimization: {$this->filePath}");
            return;
        }

        $fullPath = $disk->path($this->filePath);
        $mime = mime_content_type($fullPath);

        if (!str_contains($mime, 'image/')) {
            return;
        }

        // Optimization logic using GD
        try {
            $imageResource = $this->createImageResource($fullPath, $mime);
            if (!$imageResource) return;

            $width = imagesx($imageResource);
            $height = imagesy($imageResource);

            // Resizing
            if ($width > $this->maxWidth || $height > $this->maxHeight) {
                $ratio = min($this->maxWidth / $width, $this->maxHeight / $height);
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);
                
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preserve transparency for PNG/WebP
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                
                imagecopyresampled($newImage, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($imageResource);
                $imageResource = $newImage;
            }

            // Convert to WebP and save back to the same path
            // Note: If we want to change extension, we should update the DB.
            // But for 'instant' UI, we might want to keep the same filename but serve better content.
            // Actually, imagewebp() is fine.
            
            // We'll overwrite the file with optimized WebP or JPEG depending on original but let's stick to WebP
            imagewebp($imageResource, $fullPath, 80);
            imagedestroy($imageResource);
            
            Log::info("Image optimized: {$this->filePath}");
            
        } catch (\Exception $e) {
            Log::error("Image optimization failed: " . $e->getMessage());
        }
    }

    protected function createImageResource($path, $mime)
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/gif' => imagecreatefromgif($path),
            default => null,
        };
    }
}
