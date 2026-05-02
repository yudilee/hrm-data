<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    protected $channels = ['laravel', 'api', 'security', 'import'];

    public function index()
    {
        $files = [];

        foreach ($this->channels as $channel) {
            $dir = storage_path('logs');
            $pattern = $channel === 'laravel' ? 'laravel-*.log' : "{$channel}-*.log";
            $found = collect(File::glob("{$dir}/{$pattern}"))
                ->sortByDesc(fn ($f) => $f)
                ->take(7)
                ->map(fn ($f) => [
                    'channel' => $channel,
                    'filename' => basename($f),
                    'size' => File::size($f),
                    'modified' => date('Y-m-d H:i', File::lastModified($f)),
                ]);
            $files[$channel] = $found->values();
        }

        return view('admin.log-viewer', compact('files'));
    }

    public function show(Request $request, string $channel)
    {
        abort_unless(in_array($channel, $this->channels), 404);

        $filename = $request->get('file');
        $dir = storage_path('logs');

        if ($filename) {
            $path = $dir.'/'.basename($filename);
        } else {
            // Latest file for this channel
            $pattern = $channel === 'laravel' ? 'laravel-*.log' : "{$channel}-*.log";
            $files = File::glob("{$dir}/{$pattern}");
            rsort($files);
            $path = $files[0] ?? null;
        }

        if (! $path || ! File::exists($path)) {
            return response()->json(['lines' => [], 'file' => null]);
        }

        // Read last 300 lines
        $lines = $this->tailFile($path, 300);
        $parsed = array_map(fn ($l) => $this->parseLine($l), $lines);

        return response()->json([
            'file' => basename($path),
            'lines' => $parsed,
        ]);
    }

    protected function tailFile(string $path, int $lines): array
    {
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $total = $file->key();
        $start = max(0, $total - $lines);
        $result = [];

        $file->seek($start);
        while (! $file->eof()) {
            $line = rtrim($file->current());
            if ($line !== '') {
                $result[] = $line;
            }
            $file->next();
        }

        return $result;
    }

    protected function parseLine(string $line): array
    {
        $level = 'info';
        if (str_contains($line, '.ERROR') || str_contains($line, '.CRITICAL')) {
            $level = 'error';
        } elseif (str_contains($line, '.WARNING')) {
            $level = 'warning';
        } elseif (str_contains($line, '.DEBUG')) {
            $level = 'debug';
        }

        return ['text' => $line, 'level' => $level];
    }
}
