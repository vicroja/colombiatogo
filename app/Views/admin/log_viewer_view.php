<!DOCTYPE html>
<html>
<head>
    <title>Log Viewer - PMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #1a1a1a; color: #ccc; }
        .log-container { background: #000; color: #fff; padding: 15px; font-family: monospace; height: 70vh; overflow-y: scroll; border: 1px solid #333; }
        .level-ERROR { color: #ffffff; font-weight: bold; } /* Rojo claro */
        .level-INFO  { color: #ffffff; }                   /* Azul claro */
        .level-DEBUG { color: #ffffff; }                   /* Gris */
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