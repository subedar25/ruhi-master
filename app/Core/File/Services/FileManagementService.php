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
        $resolvedPath = $this->organizationPath($path, $organizationId);
        $destinationPath = public_path($resolvedPath);
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0775, true, true);
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        $destinationFile = $destinationPath . '/' . $fileName;

        // We use copy() instead of $file->move() because move() internally uses rename(),
        // which often fails in Docker environments when the source (storage/app/livewire-tmp)
        // and destination (public/organization) are on different partitions or mount points.
        if (!File::copy($file->getRealPath(), $destinationFile)) {
            throw new \RuntimeException("Could not move the file to destination: {$destinationFile}");
        }
        // @unlink($file->getRealPath()); // Removing primitive unlink to prevent Ignition file not found errors when an exception is thrown. PHP/Livewire garbage collects automatically.

        return $resolvedPath . '/' . $fileName;
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