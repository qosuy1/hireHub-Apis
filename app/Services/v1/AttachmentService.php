<?php
namespace App\Services\v1;

use App\Models\Offer;
use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{

    public function upload(Project|Offer $object, array $attachments, string $storename = ""): void
    { 
        DB::transaction(function () use ($object, $attachments, $storename) {

            foreach ($attachments as $fileData) {
                $file = ($fileData instanceof UploadedFile) ? $fileData : ($fileData['file'] ?? null);
                $attachmentId = is_array($fileData) ? ($fileData['id'] ?? null) : null;

               
                if (!$file)
                    continue;

                 
                $path = $file->store($storename . '/attachments', 'public');
                $attributes = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ];

                // (Update)
                if ($attachmentId) {
                    $attachment = $object->attachments()->find($attachmentId);

                    if ($attachment) {
                        $oldPath = $attachment->file_path;

                        if ($attachment->update($attributes)) {
                            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                                Storage::disk('public')->delete($oldPath);
                            }
                        }
                    }
                }
                // 4. منطق الإنشاء (Create)
                else {
                    $object->attachments()->create($attributes);
                }
            }
        });
    }


    public function delete(Project|Offer $object, int $attachment_id)
    {
        $attachment = $object->attachments()->find($attachment_id);
        if (!$attachment)
            return false;
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        $attachment->delete();
        return true;
    }
}