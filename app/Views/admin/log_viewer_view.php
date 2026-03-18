<!DOCTYPE html>
<html>
<head>
    <title>Log Viewer - PMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #121212;
            color: #e0e0e0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .log-container {
            background: #000000;
            color: #ffffff; /* Letra blanca pura */
            padding: 20px;
            font-family: 'Fira Code', 'Consolas', 'Monaco', monospace;
            font-size: 14px;
            line-height: 1.6; /* Mayor espacio entre líneas para no cansar la vista */
            height: 75vh;
            overflow-y: auto;
            border: 1px solid #333;
            border-radius: 8px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
        }

        /* Esto ayuda a que el scrollbar también sea oscuro */
        .log-container::-webkit-scrollbar { width: 10px; }
        .log-container::-webkit-scrollbar-track { background: #1a1a1a; }
        .log-container::-webkit-scrollbar-thumb { background: #444; border-radius: 5px; }
        .log-container::-webkit-scrollbar-thumb:hover { background: #555; }

        pre {
            color: inherit; /* Fuerza a que el pre herede el blanco */
            margin-bottom: 0;
            white-space: pre-wrap; /* Evita el scroll horizontal infinito */
            word-wrap: break-word;
        }
    </style>

</head>
<body class="p-4">
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-terminal"></i> Terminal de Logs</h2>
        <div>
            <button onclick="location.reload()" class="btn btn-outline-light btn-sm">Refrescar Manual</button>
            <div class="custom-control custom-switch d-inline ml-3">
                <input type="checkbox" class="custom-control-input" id="autoRefresh">
                <label class="custom-control-label" for="autoRefresh">Auto-refresh (2s)</label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <?php foreach ($files as $file): ?>
                    <a href="?file=<?= $file ?>" class="list-group-item list-group-item-action <?= $currentFile == $file ? 'active' : '' ?> bg-dark text-white border-secondary">
                        <?= str_replace(['log-', '.php'], '', $file) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-9">
            <form class="mb-2 d-flex">
                <input type="hidden" name="file" value="<?= $currentFile ?>">
                <input type="text" name="filter" class="form-control form-control-sm bg-dark text-white mr-2" placeholder="Filtrar (ej: OPENAI, ERROR...)" value="<?= $filter ?>">
                <button class="btn btn-primary btn-sm">Filtrar</button>
            </form>
            <div class="log-container" id="logBox">
                <pre><?= htmlspecialchars($content ?? 'Seleccione un archivo') ?></pre>
            </div>
        </div>
    </div>
</div>

<script>
    const logBox = document.getElementById('logBox');
    logBox.scrollTop = logBox.scrollHeight;

    let interval;
    document.getElementById('autoRefresh').addEventListener('change', function() {
        if(this.checked) {
            interval = setInterval(() => {
                location.reload();
            }, 2000);
        } else {
            clearInterval(interval);
        }
    });
</script>
</body>
</html>