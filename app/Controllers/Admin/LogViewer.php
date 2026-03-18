<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class LogViewer extends BaseController
{
    private $logPath = WRITEPATH . 'logs/';

    public function index()
    {
        // 1. Listar archivos de log disponibles
        $files = glob($this->logPath . 'log-*.php');
        rsort($files); // Más recientes primero

        $data = [
            'files' => array_map(fn($f) => basename($f), $files),
            'currentFile' => $this->request->getGet('file') ?? (count($files) > 0 ? basename($files[0]) : null),
            'filter' => $this->request->getGet('filter') ?? ''
        ];

        if ($data['currentFile']) {
            $content = file_get_contents($this->logPath . $data['currentFile']);
            // Limpiar cabecera de CI4
            $content = str_replace("<?php (defined('BASEPATH') OR exit('No direct script access allowed')); ?>", "", $content);

            // Aplicar filtros si existen (como en tu legacy)
            if ($data['filter']) {
                $lines = explode("\n", $content);
                $filtered = array_filter($lines, fn($line) => stripos($line, $data['filter']) !== false);
                $content = implode("\n", $filtered);
            }

            $data['content'] = $content;
        }

        return view('admin/log_viewer_view', $data);
    }
}