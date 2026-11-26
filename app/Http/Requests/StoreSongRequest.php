<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'artist' => ['required', 'string', 'max:150'],
            'album' => ['nullable', 'string', 'max:150'],
            'genre' => ['nullable', 'string', 'max:80'],
            'duration' => ['nullable', 'numeric', 'min:0'],
            'audio' => ['required', 'file', 'mimetypes:audio/mpeg,audio/wav,audio/x-wav,audio/wave,audio/ogg,application/ogg', 'max:'.config('vybetify.uploads.audio_max_kb')],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('vybetify.uploads.cover_max_kb')],
        ];
    }

    public function messages(): array
    {
        return [
            'audio.required' => 'Pilih file audio yang ingin di-upload.',
            'audio.mimetypes' => 'Format audio yang didukung adalah MP3, WAV, dan OGG.',
            'audio.max' => 'Ukuran audio melebihi batas konfigurasi aplikasi.',
            'cover.image' => 'Cover harus berupa gambar yang valid.',
            'cover.max' => 'Ukuran cover melebihi batas konfigurasi aplikasi.',
        ];
    }
}
