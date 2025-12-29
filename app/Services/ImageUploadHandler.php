<?php

namespace App\Services;

use Intervention\Image\ImageManagerStatic;
use Exception;
use Illuminate\Support\Str;
use Intervention\Image\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageUploadHandler
{
    protected string $disk = 'public';
    protected int $maxFileSize = 2048; // KB
    protected array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    protected int $maxFiles = 3; // 改为3个文件，与前端配置一致
    
    /**
     * Handle multiple image uploads with better validation
     */
    public function handleMultipleUpload(
        array $files, 
        string $directory, 
        ?array $existingFiles = null,
        array $options = []
    ): array {
        $uploadedFiles = [];
        $errors = [];
        $newFiles = [];

        // Merge options with defaults
        $options = array_merge([
            'resize' => true,
            'maxWidth' => 1920,
            'maxHeight' => 1080,
            'quality' => 85,
            'watermark' => false,
            'optimize' => true,
            'maxFiles' => $this->maxFiles
        ], $options);

        // Filter and separate new uploads from existing files
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $newFiles[] = $file;
            } elseif (is_string($file) && !empty($file)) {
                // This is an existing file path
                $uploadedFiles[] = $file;
            }
        }

        // Validate total file count (existing + new)
        $totalFiles = count($uploadedFiles) + count($newFiles);
        if ($totalFiles > $options['maxFiles']) {
            throw new Exception("Maksimal {$options['maxFiles']} file yang dapat diupload. Saat ini ada {$totalFiles} file.");
        }

        // Process only new file uploads
        foreach ($newFiles as $index => $file) {
            try {
                $result = $this->processSingleFile($file, $directory, $options);
                if ($result) {
                    $uploadedFiles[] = $result;
                }
            } catch (Exception $e) {
                $errors[] = "File " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        // Remove duplicates and ensure we don't exceed the limit
        $uploadedFiles = array_unique($uploadedFiles);
        if (count($uploadedFiles) > $options['maxFiles']) {
            $uploadedFiles = array_slice($uploadedFiles, 0, $options['maxFiles']);
        }

        return array_values($uploadedFiles); // Re-index array
    }

    /**
     * Process single file upload with better error handling
     */
    protected function processSingleFile(
        UploadedFile $file, 
        string $directory, 
        array $options
    ): ?string {
        try {
            // Validate file
            $this->validateFile($file);

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = Str::random(40) . '.' . $extension;
            $path = $directory . '/' . date('Y/m') . '/' . $filename;

            // Ensure directory exists
            $fullDirectory = dirname($path);
            if (!Storage::disk($this->disk)->exists($fullDirectory)) {
                Storage::disk($this->disk)->makeDirectory($fullDirectory);
            }

            // Process and store image
            if ($options['resize'] || $options['optimize']) {
                $image = ImageManagerStatic::make($file);
                
                if ($options['resize']) {
                    $image->resize($options['maxWidth'], $options['maxHeight'], function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
                
                if ($options['watermark']) {
                    // Add watermark logic here if needed
                }
                
                $processedImage = $image->encode(null, $options['quality']);
                Storage::disk($this->disk)->put($path, $processedImage);
            } else {
                Storage::disk($this->disk)->putFileAs(dirname($path), $file, basename($path));
            }

            return $path;
        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('Image upload failed: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'directory' => $directory
            ]);
            throw $e;
        }
    }

    /**
     * Enhanced file validation
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception("File {$file->getClientOriginalName()} tidak valid atau rusak");
        }

        // Check file size
        $fileSizeKB = round($file->getSize() / 1024, 2);
        if ($file->getSize() > ($this->maxFileSize * 1024)) {
            throw new Exception("File {$file->getClientOriginalName()} terlalu besar ({$fileSizeKB}KB). Maksimal {$this->maxFileSize}KB");
        }

        // Check mime type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            throw new Exception("Format file {$file->getClientOriginalName()} tidak didukung. Format yang diizinkan: " . implode(', ', $this->allowedMimeTypes));
        }

        // Check if file is actually an image
        try {
            $imageInfo = getimagesize($file->getPathname());
            if (!$imageInfo) {
                throw new Exception("File {$file->getClientOriginalName()} bukan file gambar yang valid");
            }
        } catch (Exception $e) {
            throw new Exception("File {$file->getClientOriginalName()} tidak dapat dibaca sebagai gambar");
        }
    }

    // ... (rest of the methods remain the same)
}
