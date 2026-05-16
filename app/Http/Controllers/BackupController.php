<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function download()
    {
        // Run backup
        Artisan::call('backup:run');

        // Get all backup files
        $files = Storage::disk('local')->allFiles();

        // Find latest zip backup
        $backupFile = collect($files)
            ->filter(fn ($file) => str_ends_with($file, '.zip'))
            ->last();

        if (!$backupFile) {
            return back()->with('error', 'No backup file found.');
        }

        return Storage::disk('local')->download($backupFile);
    }
}