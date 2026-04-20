<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'file_url' => asset('storage/' . $this->file_path),
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'formatted_size' => $this->getFormattedSize(),
            'extension' => pathinfo($this->file_name, PATHINFO_EXTENSION),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }

    /**
     * Get human-readable file size.
     */
    protected function getFormattedSize(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
