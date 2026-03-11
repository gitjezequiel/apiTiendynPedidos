<?php

namespace App\Services;

use Kreait\Firebase\Contract\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FirebaseStorageService
{
    protected Storage $storage;
    protected ?string $bucketName;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        // Obtenemos el bucket configurado por defecto
        $this->bucketName = config('firebase.projects.app.storage.default_bucket') 
                            ?? env('FIREBASE_STORAGE_DEFAULT_BUCKET');
    }

    /**
     * Sube un archivo a Firebase Storage y retorna la URL pública.
     *
     * @param UploadedFile $file
     * @param string $folder Carpeta dentro de Firebase (ej: 'profiles', 'products')
     * @param string|null $oldFileUrl URL del archivo anterior para eliminarlo
     * @return string URL del archivo subido
     */
    public function upload(UploadedFile $file, string $folder, ?string $oldFileUrl = null): string
    {
        // Si hay un archivo anterior, lo eliminamos
        if ($oldFileUrl) {
            $this->delete($oldFileUrl);
        }

        $bucket = $this->storage->getBucket($this->bucketName);
        
        // Generamos un nombre único para el archivo
        $fileName = $folder . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Subimos el archivo
        $object = $bucket->upload(
            fopen($file->getRealPath(), 'r'),
            [
                'name' => $fileName,
                'predefinedAcl' => 'publicRead' // Hacer que el archivo sea público
            ]
        );

        // URL pública de Google Cloud Storage
        return "https://storage.googleapis.com/{$this->bucketName}/{$fileName}";
    }

    /**
     * Elimina un archivo de Firebase Storage.
     *
     * @param string $url URL completa del archivo
     * @return bool
     */
    public function delete(string $url): bool
    {
        try {
            // Extraemos el path del archivo desde la URL de Firebase
            // De: https://firebasestorage.googleapis.com/v0/b/.../o/folder%2Fuuid.ext?alt=media
            // A: folder/uuid.ext
            $pathStart = strpos($url, '/o/') + 3;
            $pathEnd = strpos($url, '?alt=media');
            
            if ($pathStart === false || $pathEnd === false) {
                return false;
            }

            $encodedPath = substr($url, $pathStart, $pathEnd - $pathStart);
            $filePath = urldecode($encodedPath);

            $bucket = $this->storage->getBucket($this->bucketName);
            $object = $bucket->object($filePath);

            if ($object->exists()) {
                $object->delete();
                return true;
            }
        } catch (\Exception $e) {
            \Log::error("Error eliminando archivo de Firebase Storage: " . $e->getMessage());
        }

        return false;
    }
}
