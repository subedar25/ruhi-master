<?php

namespace App\Core\File\Services;

use App\Support\CurrentOrganization;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class FileManagementService
{
    /**
     * Upload a file to a specified path in the public directory.
     *
     * @param UploadedFile $file
     * @param string $path
     * @return string
     */
    public function upload(UploadedFile $file, string $path, ?int $organizationId = null): string
    {
        $resolvedPath = trim($this->organizationPath($path, $organizationId), '/');
        $destinationDir = public_path($resolvedPath);

        $this->ensureWritableUploadDirectory($destinationDir);

        $fileName = time().'_'.basename($file->getClientOriginalName());
        $destinationFile = $destinationDir.DIRECTORY_SEPARATOR.$fileName;

        $sourcePath = $file->getRealPath();
        if (! $sourcePath || ! is_file($sourcePath)) {
            $sourcePath = $file->getPathname();
        }
        if (! is_readable($sourcePath)) {
            throw new \RuntimeException('Uploaded file is missing or not readable (Livewire temp path). Try again.');
        }

        // We use copy() instead of $file->move() because move() internally uses rename(),
        // which often fails in Docker environments when the source (storage/app/livewire-tmp)
        // and the destination (public/...) are on different filesystems or mount points.
        if (! File::copy($sourcePath, $destinationFile)) {
            throw new \RuntimeException("Could not copy file to: {$destinationFile}");
        }

        return $resolvedPath.'/'.$fileName;
    }

    /**
     * Ensure destination exists and is a directory (recursive). Servers/Docker often lack organization/* dirs until first upload.
     */
    private function ensureWritableUploadDirectory(string $absoluteDir): void
    {
        if (File::isDirectory($absoluteDir)) {
            return;
        }

        File::makeDirectory($absoluteDir, 0775, true, true);

        if (! File::isDirectory($absoluteDir)) {
            throw new \RuntimeException(
                'Cannot create upload directory '.$absoluteDir.'. Ensure the web user can write to public/ (e.g. mkdir public/organization && chown -R www-data:www-data public/organization).'
            );
        }
    }

    /**
     * Delete a file from the public directory.
     *
     * @param string|null $path
     * @return bool
     */
    public function delete(?string $path): bool
    {
        if ($path && File::exists(public_path($path))) {
            return File::delete(public_path($path));
        }
        return false;
    }

    public function organizationPath(string $path, ?int $organizationId = null): string
    {
        $path = trim($path, '/');
        if ($path === '') {
            return $path;
        }

        if (str_starts_with($path, 'organization/')) {
            return $path;
        }

        $orgId = $organizationId ?: CurrentOrganization::id();
        if (! $orgId) {
            return $path;
        }

        return "organization/{$orgId}/{$path}";
    }
}