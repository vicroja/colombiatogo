<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

<?php
$currencySymbol = session('currency_symbol') ?: ($tenant['currency_symbol'] ?? '$');
$publicUrl      = base_url('book/' . $tenant['slug']);
?>

    <style>
        /* ── Builder layout ──────────────────────────────────────────────────────── */
        .builder-wrap{display:grid;grid-template-columns:420px 1fr;gap:0;
            height:calc(100vh - 100px);overflow:hidden;
            border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);
            border:1px solid #e2e8f0}

        /* Panel izquierdo */
        .builder-panel{background:#fff;overflow-y:auto;border-right:1px solid #e2e8f0;
            display:flex;flex-direction:column}

        .panel-header{padding:1.25rem 1.5rem;border-bottom:1px solid #f1f5f9;
            background:#fff;position:sticky;top:0;z-index:10}

        .panel-header h5{font-size:1rem;font-weight:700;color:#0f172a;margin:0}

        /* Tabs del panel */
        .builder-tabs{display:flex;border-bottom:1px solid #e2e8f0;
            background:#f8fafc;flex-shrink:0}

        .btab{flex:1;padding:.65rem .5rem;font-size:.75rem;font-weight:600;
            text-align:center;cursor:pointer;color:#64748b;border:none;
            background:transparent;transition:all .2s;border-bottom:2px solid transparent}

        .btab.active{color:#3b82f6;border-bottom-color:#3b82f6;background:#fff}
        .btab:hover:not(.active){background:#f1f5f9;color:#374151}

        .tab-pane{display:none;padding:1.25rem 1.5rem;flex:1}
        .tab-pane.active{display:block}

        /* Secciones del panel */
        .panel-section{margin-bottom:1.5rem}
        .panel-section-title{font-size:.72rem;font-weight:700;text-transform:uppercase;
            letter-spacing:.08em;color:#94a3b8;margin-bottom:.75rem;
            display:flex;align-items:center;gap:.4rem}

        /* Campos del formulario */
        .builder-label{font-size:.75rem;font-weight:600;color:#374151;
            margin-bottom:.3rem;display:block}
        .builder-input{width:100%;border:1.5px solid #e2e8f0;border-radius:8px;
            padding:.55rem .8rem;font-size:.85rem;color:#0f172a;
            outline:none;transition:border-color .2s;font-family:inherit}
        .builder-input:focus{border-color:#3b82f6}
        .builder-textarea{resize:vertical;min-height:80px}

        /* Botón AI inline */
        .btn-ai-inline{display:inline-flex;align-items:center;gap:.35rem;
            background:linear-gradient(135deg,#6366f1,#8b5cf6);
            color:#fff;border:none;border-radius:6px;
            padding:.3rem .75rem;font-size:.72rem;font-weight:600;
            cursor:pointer;transition:opacity .2s;white-space:nowrap}
        .btn-ai-inline:hover{opacity:.85}
        .btn-ai-inline:disabled{opacity:.5;cursor:not-allowed}

        /* Theme cards */
        .theme-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem}
        .theme-card{border:2px solid #e2e8f0;border-radius:10px;overflow:hidden;
            cursor:pointer;transition:all .2s;position:relative}
        .theme-card:hover{border-color:#93c5fd;transform:translateY(-1px)}
        .theme-card.selected{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15)}
        .theme-card .tc-thumb{height:80px;background:#f1f5f9;
            display:flex;align-items:center;justify-content:center;
            font-size:.65rem;color:#94a3b8;text-align:center;padding:.5rem}
        .theme-card .tc-name{font-size:.72rem;font-weight:700;color:#0f172a;
            padding:.4rem .6rem .2rem}
        .theme-card .tc-desc{font-size:.65rem;color:#64748b;padding:0 .6rem .5rem;
            line-height:1.3}
        .theme-card .tc-check{position:absolute;top:6px;right:6px;width:18px;height:18px;
            border-radius:50%;background:#3b82f6;color:#fff;
            font-size:.6rem;display:none;align-items:center;
            justify-content:center}
        .theme-card.selected .tc-check{display:flex}

        /* Color picker row */
        .color-row{display:flex;align-items:center;gap:.75rem}
        .color-swatch{width:36px;height:36px;border-radius:8px;border:2px solid #e2e8f0;
            cursor:pointer;overflow:hidden}
        .color-swatch input[type=color]{width:100%;height:100%;border:none;
            cursor:pointer;padding:0;opacity:0;
            position:absolute}
        .color-swatch-wrap{position:relative;width:36px;height:36px}
        .color-display{width:36px;height:36px;border-radius:8px;
            border:2px solid #e2e8f0;pointer-events:none}

        /* Galería */
        .photo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem}
        .photo-item{position:relative;border-radius:8px;overflow:hidden;
            aspect-ratio:4/3;background:#f1f5f9;cursor:grab}
        .photo-item img{width:100%;height:100%;object-fit:cover}
        .photo-item .ph-actions{position:absolute;inset:0;background:rgba(0,0,0,0);
            display:flex;align-items:center;justify-content:center;
            gap:.4rem;transition:background .2s;opacity:0}
        .photo-item:hover .ph-actions{background:rgba(0,0,0,.45);opacity:1}
        .ph-btn{background:rgba(255,255,255,.9);border:none;border-radius:6px;
            padding:.25rem .45rem;font-size:.7rem;cursor:pointer;font-weight:600}
        .ph-btn.main{color:#2563eb}
        .ph-btn.del{color:#dc2626}
        .photo-item .main-badge{position:absolute;top:4px;left:4px;background:#2563eb;
            color:#fff;font-size:.6rem;font-weight:700;
            padding:.1rem .4rem;border-radius:4px}

        /* Upload zona */
        .upload-zone{border:2px dashed #c7d2fe;border-radius:10px;padding:1rem;
            text-align:center;cursor:pointer;transition:all .2s;background:#f8faff}
        .upload-zone:hover{border-color:#6366f1;background:#f0f4ff}

        /* Panel de preview */
        .preview-panel{background:#e2e8f0;display:flex;flex-direction:column;
            position:relative;overflow:hidden}

        .preview-toolbar{background:#fff;border-bottom:1px solid #e2e8f0;
            padding:.6rem 1rem;display:flex;align-items:center;
            justify-content:space-between;flex-shrink:0}

        .preview-toolbar .pt-url{font-size:.72rem;color:#64748b;
            background:#f1f5f9;padding:.3rem .75rem;
            border-radius:99px;font-family:monospace}

        .device-btns{display:flex;gap:.35rem}
        .device-btn{background:none;border:1px solid #e2e8f0;border-radius:6px;
            padding:.3rem .5rem;font-size:.75rem;cursor:pointer;color:#64748b;
            transition:all .15s}
        .device-btn.active{background:#f0f4ff;color:#3b82f6;border-color:#bfdbfe}

        .preview-frame-wrap{flex:1;overflow:hidden;padding:1rem;
            display:flex;align-items:flex-start;justify-content:center}

        #previewFrame{border:none;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.15);
            transition:all .4s ease;background:#fff}

        /* Publicar bar */
        .publish-bar{background:#fff;border-top:1px solid #e2e8f0;padding:.75rem 1.5rem;
            display:flex;align-items:center;justify-content:space-between;
            flex-shrink:0}

        .pub-status{display:flex;align-items:center;gap:.5rem;font-size:.82rem;font-weight:600}
        .pub-dot{width:8px;height:8px;border-radius:50%}
        .pub-dot.live{background:#22c55e}
        .pub-dot.draft{background:#94a3b8}

        .btn-save{background:#3b82f6;color:#fff;border:none;border-radius:8px;
            padding:.6rem 1.5rem;font-weight:600;font-size:.875rem;cursor:pointer;
            transition:all .2s;display:flex;align-items:center;gap:.5rem}
        .btn-save:hover{background:#2563eb;transform:translateY(-1px)}
        .btn-save:disabled{opacity:.6;cursor:not-allowed;transform:none}

        /* AI generador completo */
        .ai-full-card{background:linear-gradient(135deg,#f0f4ff,#faf5ff);
            border:1px solid #c7d2fe;border-radius:12px;padding:1rem;
            margin-bottom:1rem}

        /* Saving indicator */
        .saving-pill{position:fixed;bottom:1.5rem;right:1.5rem;
            background:#0f172a;color:#fff;padding:.5rem 1rem;
            border-radius:99px;font-size:.78rem;font-weight:600;
            display:none;align-items:center;gap:.5rem;z-index:9999;
            box-shadow:0 4px 12px rgba(0,0,0,.3)}
        .saving-pill.visible{display:flex}
    </style>

    <!-- ── Header ────────────────────────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-globe2 me-2 text-primary"></i>
                Constructor de Sitio Web
            </h4>
            <p class="text-muted small mb-0">
                Los cambios se previsializan en tiempo real
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="<?= $publicUrl ?>" target="_blank"
               class="btn btn-sm btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i>
                Ver sitio en vivo
            </a>
        </div>
    </div>

    <!-- ── Saving pill ───────────────────────────────────────────────────────── -->
    <div class="saving-pill" id="savingPill">
        <span class="spinner-border spinner-border-sm"></span>
        Guardando...
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════
         BUILDER LAYOUT
    ════════════════════════════════════════════════════════════════════════ -->
    <div class="builder-wrap">

        <!-- ════════════════════════════════
             PANEL IZQUIERDO
        ════════════════════════════════ -->
        <div class="builder-panel">

            <!-- Tabs -->
            <div class="builder-tabs">
                <button class="btab active" onclick="switchTab('design')">
                    <i class="bi bi-palette me-1"></i>Diseño
                </button>
                <button class="btab" onclick="switchTab('content')">
                    <i class="bi bi-type me-1"></i>Contenido
                </button>
                <button class="btab" onclick="switchTab('photos')">
                    <i class="bi bi-images me-1"></i>Fotos
                </button>
                <button class="btab" onclick="switchTab('contact')">
                    <i class="bi bi-share me-1"></i>Contacto
                </button>
            </div>

            <form id="builderForm" action="/website/update" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $website['id'] ?>">

                <!-- ══════════════════════════════
                     TAB: DISEÑO
                ══════════════════════════════ -->
                <div class="tab-pane active" id="tab-design">

                    <!-- Plantilla -->
                    <div class="panel-section">
                        <div class="panel-section-title">
                            <i class="bi bi-layout-wtf"></i> Plantilla
                        </div>
                        <input type="hidden" name="theme_slug"
                               id="theme_slug" value="<?= esc($website['theme_slug']) ?>">
                        <div class="theme-grid">
                            <?php foreach ($themes as $slug => $theme): ?>
                                <div class="theme-card <?= $website['theme_slug'] === $slug ? 'selected' : '' ?>"
                                     onclick="selectTheme('<?= $slug ?>')"
                                     id="themeCard_<?= $slug ?>">
                                    <div class="tc-check">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                    <div class="tc-thumb">
                                        <?php
                                        // Iconos representativos por tema
                                        $themeIcons = [
                                            'resort'    => '🏡',
                                            'boutique'  => '🏙️',
                                            'corporate' => '🏢',
                                        ];
                                        ?>
                                        <div style="font-size:2rem">
                                            <?= $themeIcons[$slug] ?? '🌐' ?>
                                        </div>
                                    </div>
                                    <div class="tc-name"><?= esc($theme['name']) ?></div>
                                    <div class="tc-desc"><?= esc($theme['desc']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Color principal -->
                    <div class="panel-section">
                        <div class="panel-section-title">
                            <i class="bi bi-droplet-half"></i> Color Principal
                        </div>
                        <div class="color-row">
                            <div class="color-swatch-wrap">
                                <div class="color-display"
                                     id="colorDisplay"
                                     style="background:<?= esc($website['primary_color']) ?>">
                                </div>
                                <input type="color" name="primary_color"
                                       id="primary_color"
                                       value="<?= esc($website['primary_color']) ?>"
                                       style="position:absolute;inset:0;
                                          opacity:0;cursor:pointer;width:100%;height:100%"
                                       oninput="onColorChange(this.value)">
                            </div>
                            <div>
                            <span id="colorHex"
                                  style="font-family:monospace;font-size:.82rem;
                                         color:#374151;font-weight:600">
                                <?= esc($website['primary_color']) ?>
                            </span>
                                <div style="font-size:.72rem;color:#94a3b8">
                                    Color de botones y acentos
                                </div>
                            </div>
                        </div>

                        <!-- Paletas rápidas -->
                        <div class="d-flex gap-1 flex-wrap mt-2">
                            <?php
                            $palettes = [
                                '#2E75B6','#1D9E75','#C9A84C','#D85A30',
                                '#7F77DD','#0F6E56','#185FA5','#993556',
                            ];
                            foreach ($palettes as $color):
                                ?>
                                <div onclick="onColorChange('<?= $color ?>')"
                                     style="width:22px;height:22px;background:<?= $color ?>;
                                             border-radius:4px;cursor:pointer;
                                             border:2px solid rgba(0,0,0,.1)"
                                     title="<?= $color ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div><!-- /tab-design -->

                <!-- ══════════════════════════════
                     TAB: CONTENIDO
                ══════════════════════════════ -->
                <div class="tab-pane" id="tab-content">

                    <!-- Generador AI completo -->
                    <div class="ai-full-card">
                        <div class="d-flex align-items-center gap-2 mb-2">
                        <span style="font-size:.8rem;font-weight:700;color:#4338ca">
                            <i class="bi bi-stars me-1"></i>Generar todo con IA
                        </span>
                            <span style="font-size:.68rem;background:#eef2ff;
                                     color:#4338ca;padding:.1rem .5rem;
                                     border-radius:4px">Recomendado</span>
                        </div>
                        <textarea class="builder-input builder-textarea"
                                  id="aiHints" rows="2"
                                  placeholder="Describe tu alojamiento en pocas palabras... (opcional)"
                                  style="margin-bottom:.6rem;font-size:.8rem"></textarea>
                        <button type="button" class="btn-ai-inline w-100"
                                id="btnAiFull" onclick="generateFullContent()"
                                style="justify-content:center;padding:.5rem">
                            <i class="bi bi-stars me-1"></i>
                            Generar Hero + Descripción + Políticas
                        </button>
                        <div id="aiFullLoading"
                             style="display:none;text-align:center;
                                padding:.5rem;font-size:.78rem;color:#6366f1">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                            Generando contenido...
                        </div>
                    </div>

                    <!-- Hero title -->
                    <div class="panel-section">
                        <div class="panel-section-title">
                            <i class="bi bi-type-h1"></i> Encabezado principal
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="builder-label mb-0">Título</label>
                            <button type="button" class="btn-ai-inline"
                                    onclick="generateHero()">
                                <i class="bi bi-stars"></i> IA
                            </button>
                        </div>
                        <input type="text" name="hero_title" id="hero_title"
                               class="builder-input mb-2"
                               value="<?= esc($website['hero_title']) ?>"
                               placeholder="Ej: Tu refugio en la naturaleza"
                               oninput="schedulePreview()">
                        <label class="builder-label">Subtítulo</label>
                        <input type="text" name="hero_subtitle"
                               id="hero_subtitle" class="builder-input"
                               value="<?= esc($website['hero_subtitle']) ?>"
                               placeholder="Ej: Reserva directo y ahorra"
                               oninput="schedulePreview()">
                    </div>

                    <!-- About -->
                    <div class="panel-section">
                        <div class="panel-section-title">
                            <i class="bi bi-chat-square-text"></i> Acerca de nosotros
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="builder-label mb-0">Descripción</label>
                            <button type="button" class="btn-ai-inline"
                                    onclick="generateAbout()">
                                <i class="bi bi-stars"></i> IA
                            </button>
                        </div>
                        <textarea name="about_text" id="about_text"
                                  class="builder-input builder-textarea"
                                  rows="4"
                                  placeholder="Cuéntale a tus huéspedes qué hace especial tu lugar..."
                                  oninput="schedulePreview()"><?= esc($website['about_text']) ?></textarea>
                    </div>

                    <!-- Políticas -->
                    <div class="panel-section">
                        <div class="panel-section-title">
                            <i class="bi bi-file-text"></i> Políticas de estadía
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="builder-label mb-0">Políticas</label>
                            <button type="button" class="btn-ai-inline"
                                    onclick="generatePolicies()">
                                <i class="bi bi-stars"></i> IA
                            </button>
                        </div>
                        <textarea name="policies_text" id="policies_text"
                                  class="builder-input builder-textarea"
                                  rows="4"
                                  placeholder="• Check-in: 15:00 / Check-out: 12:00&#10;• No mascotas&#10;• No fumar en espacios cerrados"
                                  oninput="schedulePreview()"><?= esc($website['policies_text']) ?></textarea>
                    </div>

                </div><!-- /tab-content -->

                <!-- ══════════════════════════════
                     TAB: FOTOS
                ══════════════════════════════ -->
                <div class="tab-pane" id="tab-photos">

                    <!-- Upload -->
                    <div class="panel-section">
                        <div class="panel-section-title">
                            <i class="bi bi-cloud-upload"></i> Subir fotos
                        </div>
                        <div class="upload-zone" id="uploadZone"
                             onclick="document.getElementById('photoFileInput').click()"
                             ondragover="handleDragOver(event)"
                             ondragleave="handleDragLeave(event)"
                             ondrop="handleDrop(event)">
                            <i class="bi bi-images"
                               style="font-size:1.5rem;color:#a5b4fc;display:block;
                                  margin-bottom:.35rem"></i>
                            <p style="font-size:.78rem;color:#6366f1;
                                  font-weight:600;margin:0">
                                Arrastra o haz clic para subir
                            </p>
                            <p style="font-size:.7rem;color:#94a3b8;margin:.2rem 0 0">
                                JPG, PNG, WEBP · Hasta 50 fotos
                            </p>
                        </div>
                        <input type="file" id="photoFileInput"
                               accept="image/*" multiple class="d-none"
                               onchange="uploadPhotos(this.files)">
                        <div id="uploadProgress"
                             style="display:none;margin-top:.5rem">
                            <div class="progress" style="height:4px">
                                <div class="progress-bar" id="uploadBar"
                                     style="width:0%;background:#6366f1">
                                </div>
                            </div>
                            <p style="font-size:.72rem;color:#6366f1;
                                  margin-top:.25rem;text-align:center"
                               id="uploadStatus">Subiendo...</p>
                        </div>
                    </div>

                    <!-- Galería con drag & drop -->
                    <div class="panel-section">
                        <div class="panel-section-title">
                            <i class="bi bi-grid-3x3-gap"></i>
                            Galería
                            <span style="font-size:.65rem;background:#e0f2fe;
                                     color:#0369a1;padding:.1rem .4rem;
                                     border-radius:4px;margin-left:.25rem">
                            Arrastra para reordenar
                        </span>
                        </div>
                        <div class="photo-grid" id="photoGrid">
                            <?php foreach ($media as $m): ?>
                                <div class="photo-item"
                                     data-id="<?= $m['id'] ?>"
                                     draggable="true"
                                     ondragstart="dragStart(event)"
                                     ondragover="dragOver(event)"
                                     ondrop="dropPhoto(event)">
                                    <?php if ($m['is_main']): ?>
                                        <span class="main-badge">Portada</span>
                                    <?php endif; ?>
                                    <img src="<?= base_url($m['file_path']) ?>"
                                         alt="">
                                    <div class="ph-actions">
                                        <button type="button"
                                                class="ph-btn main"
                                                onclick="setMainPhoto(<?= $m['id'] ?>)">
                                            <i class="bi bi-star-fill"></i>
                                            Portada
                                        </button>
                                        <button type="button"
                                                class="ph-btn del"
                                                onclick="deletePhoto(<?= $m['id'] ?>, this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (empty($media)): ?>
                            <p style="text-align:center;color:#94a3b8;
                                  font-size:.82rem;padding:1.5rem 0">
                                Aún no has subido fotos del hotel
                            </p>
                        <?php endif; ?>
                    </div>

                </div><!-- /tab-photos -->

                <!-- ══════════════════════════════
                     TAB: CONTACTO
                ══════════════════════════════ -->
                <div class="tab-pane" id="tab-contact">

                    <div class="panel-section">
                        <div class="panel-section-title">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </div>
                        <label class="builder-label">Número de WhatsApp</label>
                        <input type="text" name="whatsapp_number"
                               id="whatsapp_number" class="builder-input mb-1"
                               value="<?= esc($website['whatsapp_number']) ?>"
                               placeholder="573001234567"
                               oninput="schedulePreview()">
                        <p style="font-size:.72rem;color:#94a3b8">
                            Formato internacional sin + ni espacios
                        </p>
                    </div>

                    <div class="panel-section">
                        <div class="panel-section-title">
                            <i class="bi bi-instagram"></i> Redes sociales
                        </div>
                        <label class="builder-label">Instagram</label>
                        <input type="url" name="instagram_url"
                               id="instagram_url" class="builder-input mb-3"
                               value="<?= esc($website['instagram_url']) ?>"
                               placeholder="https://instagram.com/tu_hotel"
                               oninput="schedulePreview()">
                        <label class="builder-label">Facebook</label>
                        <input type="url" name="facebook_url"
                               id="facebook_url" class="builder-input"
                               value="<?= esc($website['facebook_url'] ?? '') ?>"
                               placeholder="https://facebook.com/tu_hotel"
                               oninput="schedulePreview()">
                    </div>

                </div><!-- /tab-contact -->

            </form><!-- /builderForm -->

            <!-- Barra de publicar (sticky bottom) -->
            <div class="publish-bar">
                <div class="pub-status">
                    <div class="pub-dot <?= $website['is_published'] ? 'live' : 'draft' ?>"
                         id="pubDot"></div>
                    <span id="pubLabel">
                    <?= $website['is_published'] ? 'Publicado' : 'Borrador' ?>
                </span>
                    <label style="cursor:pointer;margin-left:.5rem">
                        <input type="checkbox" id="publishToggle"
                            <?= $website['is_published'] ? 'checked' : '' ?>
                               onchange="togglePublish(this.checked)"
                               style="accent-color:#22c55e;cursor:pointer">
                    </label>
                </div>
                <button type="button" class="btn-save"
                        id="btnSave" onclick="saveWebsite()">
                    <i class="bi bi-floppy"></i> Guardar
                </button>
            </div>

        </div><!-- /builder-panel -->

        <!-- ════════════════════════════════
             PANEL DERECHO — PREVIEW
        ════════════════════════════════ -->
        <div class="preview-panel">

            <!-- Toolbar de preview -->
            <div class="preview-toolbar">
                <span class="pt-url"><?= $publicUrl ?></span>
                <div class="d-flex align-items-center gap-2">
                    <!-- Device selector -->
                    <div class="device-btns">
                        <button class="device-btn active"
                                onclick="setDevice('desktop')"
                                id="devDesktop" title="Desktop">
                            <i class="bi bi-display"></i>
                        </button>
                        <button class="device-btn"
                                onclick="setDevice('tablet')"
                                id="devTablet" title="Tablet">
                            <i class="bi bi-tablet"></i>
                        </button>
                        <button class="device-btn"
                                onclick="setDevice('mobile')"
                                id="devMobile" title="Móvil">
                            <i class="bi bi-phone"></i>
                        </button>
                    </div>
                    <button class="device-btn"
                            onclick="refreshPreview()"
                            title="Refrescar">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>

            <!-- iframe de preview -->
            <div class="preview-frame-wrap" id="previewWrap">
                <iframe id="previewFrame"
                        src="/website/preview"
                        style="width:100%;height:100%">
                </iframe>
            </div>

        </div><!-- /preview-panel -->

    </div><!-- /builder-wrap -->

    <script>
        // ── Estado ────────────────────────────────────────────────────────────────
        let previewTimer    = null;
        let dragSrcEl       = null;
        let currentDevice   = 'desktop';
        let isPublished     = <?= $website['is_published'] ? 'true' : 'false' ?>;
        const currencySymbol = '<?= $currencySymbol ?>';

        // ── Tabs ──────────────────────────────────────────────────────────────────
        function switchTab(name) {
            document.querySelectorAll('.btab').forEach((t, i) => {
                const tabs = ['design','content','photos','contact'];
                t.classList.toggle('active', tabs[i] === name);
            });
            document.querySelectorAll('.tab-pane').forEach(p => {
                p.classList.toggle('active',
                    p.id === 'tab-' + name);
            });
        }

        // ── Selección de tema ─────────────────────────────────────────────────────
        function selectTheme(slug) {
            document.querySelectorAll('.theme-card')
                .forEach(c => c.classList.remove('selected'));
            document.getElementById('themeCard_' + slug)
                .classList.add('selected');
            document.getElementById('theme_slug').value = slug;
            schedulePreview();
        }

        // ── Color ─────────────────────────────────────────────────────────────────
        function onColorChange(hex) {
            document.getElementById('primary_color').value = hex;
            document.getElementById('colorDisplay').style.background = hex;
            document.getElementById('colorHex').textContent = hex;
            schedulePreview();
        }

        // ── Preview ───────────────────────────────────────────────────────────────

        /**
         * Programa un refresh del preview con debounce de 800ms
         * para no saturar mientras el usuario escribe
         */
        function schedulePreview() {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(refreshPreview, 800);
        }

        /**
         * Guarda el formulario silenciosamente vía fetch
         * y luego refresca el iframe con el nuevo estado
         */
        async function refreshPreview() {
            const form    = document.getElementById('builderForm');
            const data    = new FormData(form);
            data.set('is_published', isPublished ? '1' : '');

            // Guardar silenciosamente
            await fetch('/website/update', {
                method      : 'POST',
                body        : data,
                credentials : 'same-origin',
            });

            // Recargar iframe con cache-buster
            const frame = document.getElementById('previewFrame');
            frame.src   = '/website/preview?t=' + Date.now();
        }

        // ── Dispositivos ──────────────────────────────────────────────────────────
        function setDevice(device) {
            currentDevice = device;
            document.querySelectorAll('.device-btn').forEach(b =>
                b.classList.remove('active'));
            document.getElementById('dev' +
                device.charAt(0).toUpperCase() + device.slice(1))
                .classList.add('active');

            const frame = document.getElementById('previewFrame');
            const wrap  = document.getElementById('previewWrap');

            const sizes = {
                desktop: { w: '100%',  h: '100%', maxW: 'none' },
                tablet : { w: '768px', h: '95%',  maxW: '768px' },
                mobile : { w: '390px', h: '95%',  maxW: '390px' },
            };

            const s = sizes[device];
            frame.style.width    = s.w;
            frame.style.height   = s.h;
            frame.style.maxWidth = s.maxW;
        }

        // ── Guardar ───────────────────────────────────────────────────────────────
        async function saveWebsite() {
            const btn  = document.getElementById('btnSave');
            const pill = document.getElementById('savingPill');

            btn.disabled    = true;
            pill.classList.add('visible');

            const form = document.getElementById('builderForm');
            const data = new FormData(form);
            data.set('is_published', isPublished ? '1' : '');

            try {
                const res = await fetch('/website/update', {
                    method      : 'POST',
                    body        : data,
                    credentials : 'same-origin',
                });

                // Refrescar preview tras guardar
                document.getElementById('previewFrame').src =
                    '/website/preview?t=' + Date.now();

                // Flash success
                showBuilderToast('✅ Sitio guardado correctamente');

            } catch (err) {
                showBuilderToast('❌ Error al guardar', 'danger');
            } finally {
                btn.disabled = false;
                pill.classList.remove('visible');
            }
        }

        // ── Toggle publicar ───────────────────────────────────────────────────────
        function togglePublish(checked) {
            isPublished = checked;
            document.getElementById('pubDot').className =
                'pub-dot ' + (checked ? 'live' : 'draft');
            document.getElementById('pubLabel').textContent =
                checked ? 'Publicado' : 'Borrador';
        }

        // ── AI Generadores ────────────────────────────────────────────────────────

        async function aiRequest(action, extraData = {}) {
            const hints = document.getElementById('aiHints')?.value ?? '';
            const about = document.getElementById('about_text')?.value ?? '';

            const res = await fetch('/website/ai-generate', {
                method      : 'POST',
                credentials : 'same-origin',
                headers     : {
                    'Content-Type'     : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest',
                },
                body: JSON.stringify({ action, hints, about, ...extraData })
            });
            return await res.json();
        }

        async function generateHero() {
            const result = await aiRequest('hero');
            if (result.success && result.data) {
                typewriterSet('hero_title',    result.data.hero_title    ?? '');
                typewriterSet('hero_subtitle', result.data.hero_subtitle ?? '');
                schedulePreview();
                showBuilderToast('✅ Hero generado con IA');
            } else {
                showBuilderToast('❌ ' + (result.message || 'Error'), 'danger');
            }
        }

        async function generateAbout() {
            const result = await aiRequest('about');
            if (result.success && result.text) {
                typewriterSet('about_text', result.text);
                schedulePreview();
                showBuilderToast('✅ Descripción generada');
            } else {
                showBuilderToast('❌ ' + (result.message || 'Error'), 'danger');
            }
        }

        async function generatePolicies() {
            const result = await aiRequest('policies');
            if (result.success && result.text) {
                typewriterSet('policies_text', result.text);
                schedulePreview();
                showBuilderToast('✅ Políticas generadas');
            } else {
                showBuilderToast('❌ ' + (result.message || 'Error'), 'danger');
            }
        }

        async function generateFullContent() {
            const btn     = document.getElementById('btnAiFull');
            const loading = document.getElementById('aiFullLoading');
            btn.disabled  = true;
            loading.style.display = 'block';

            try {
                const result = await aiRequest('full_content');

                if (result.success) {
                    if (result.hero) {
                        typewriterSet('hero_title',    result.hero.hero_title    ?? '');
                        typewriterSet('hero_subtitle', result.hero.hero_subtitle ?? '');
                    }
                    if (result.about)    typewriterSet('about_text',    result.about);
                    if (result.policies) typewriterSet('policies_text', result.policies);
                    schedulePreview();
                    showBuilderToast('✅ Todo el contenido generado con IA');
                } else {
                    showBuilderToast('❌ ' + (result.message || 'Error'), 'danger');
                }
            } catch (err) {
                showBuilderToast('❌ Error de conexión', 'danger');
            } finally {
                btn.disabled          = false;
                loading.style.display = 'none';
            }
        }

        /**
         * Efecto typewriter para campos de texto
         */
        function typewriterSet(fieldId, text, delay = 12) {
            const field = document.getElementById(fieldId);
            if (!field) return;
            field.value = '';
            let i = 0;
            const interval = setInterval(() => {
                field.value += text[i++];
                if (i >= text.length) clearInterval(interval);
            }, delay);
        }

        // ── Fotos — Upload ────────────────────────────────────────────────────────

        function handleDragOver(e) {
            e.preventDefault();
            document.getElementById('uploadZone').style.borderColor = '#6366f1';
            document.getElementById('uploadZone').style.background  = '#f0f4ff';
        }

        function handleDragLeave(e) {
            document.getElementById('uploadZone').style.borderColor = '#c7d2fe';
            document.getElementById('uploadZone').style.background  = '#f8faff';
        }

        function handleDrop(e) {
            e.preventDefault();
            handleDragLeave(e);
            const files = Array.from(e.dataTransfer.files)
                .filter(f => f.type.startsWith('image/'));
            if (files.length) uploadPhotos(files);
        }

        async function uploadPhotos(files) {
            const progress = document.getElementById('uploadProgress');
            const bar      = document.getElementById('uploadBar');
            const status   = document.getElementById('uploadStatus');

            progress.style.display = 'block';
            let done = 0;

            for (const file of files) {
                const fd = new FormData();
                fd.append('media_file', file);
                // Necesitamos el CSRF token
                const csrfToken = document.cookie
                    .split('; ')
                    .find(r => r.startsWith('csrf_cookie_name='))
                    ?.split('=')[1] ?? '';

                try {
                    await fetch('/website/upload-media', {
                        method      : 'POST',
                        body        : fd,
                        credentials : 'same-origin',
                        headers     : { 'X-CSRF-TOKEN': csrfToken }
                    });
                } catch (e) {}

                done++;
                const pct = Math.round((done / files.length) * 100);
                bar.style.width         = pct + '%';
                status.textContent      = `Subiendo ${done}/${files.length}...`;
            }

            progress.style.display = 'none';
            bar.style.width        = '0%';
            showBuilderToast('✅ ' + files.length + ' foto(s) subida(s)');

            // Recargar la página para mostrar la galería actualizada
            setTimeout(() => location.reload(), 800);
        }

        // ── Fotos — Set portada ───────────────────────────────────────────────────
        async function setMainPhoto(id) {
            const csrfToken = document.cookie
                .split('; ')
                .find(r => r.startsWith('csrf_cookie_name='))
                ?.split('=')[1] ?? '';

            const res  = await fetch('/website/set-main-photo/' + id, {
                method      : 'POST',
                credentials : 'same-origin',
                headers     : {
                    'Content-Type'     : 'application/json',
                    'X-CSRF-TOKEN'     : csrfToken,
                    'X-Requested-With' : 'XMLHttpRequest',
                }
            });
            const data = await res.json();

            if (data.success) {
                // Actualizar badges en el grid
                document.querySelectorAll('.main-badge').forEach(b => b.remove());
                const item = document.querySelector(`[data-id="${id}"]`);
                if (item) {
                    const badge     = document.createElement('span');
                    badge.className = 'main-badge';
                    badge.textContent = 'Portada';
                    item.prepend(badge);
                }
                schedulePreview();
                showBuilderToast('✅ Foto de portada actualizada');
            }
        }

        // ── Fotos — Eliminar ──────────────────────────────────────────────────────
        async function deletePhoto(id, btn) {
            if (!confirm('¿Eliminar esta foto?')) return;
            btn.disabled = true;

            const res  = await fetch('/website/delete-media/' + id, {
                method      : 'GET',
                credentials : 'same-origin',
            });

            const item = document.querySelector(`[data-id="${id}"]`);
            if (item) {
                item.style.opacity    = '0';
                item.style.transform  = 'scale(.8)';
                item.style.transition = 'all .3s';
                setTimeout(() => item.remove(), 300);
            }
            schedulePreview();
            showBuilderToast('Foto eliminada');
        }

        // ── Fotos — Drag & drop para reordenar ───────────────────────────────────
        function dragStart(e) {
            dragSrcEl = e.currentTarget;
            e.dataTransfer.effectAllowed = 'move';
            e.currentTarget.style.opacity = '0.4';
        }

        function dragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            e.currentTarget.style.outline = '2px solid #6366f1';
            return false;
        }

        function dropPhoto(e) {
            e.preventDefault();
            e.currentTarget.style.outline = '';

            if (dragSrcEl !== e.currentTarget) {
                const grid   = document.getElementById('photoGrid');
                const items  = [...grid.querySelectorAll('.photo-item')];
                const srcIdx = items.indexOf(dragSrcEl);
                const dstIdx = items.indexOf(e.currentTarget);

                if (srcIdx < dstIdx) {
                    grid.insertBefore(dragSrcEl,
                        e.currentTarget.nextSibling);
                } else {
                    grid.insertBefore(dragSrcEl, e.currentTarget);
                }

                // Guardar nuevo orden vía AJAX
                const order = [...grid.querySelectorAll('.photo-item')]
                    .map(el => el.dataset.id);

                const csrfToken = document.cookie
                    .split('; ')
                    .find(r => r.startsWith('csrf_cookie_name='))
                    ?.split('=')[1] ?? '';

                fetch('/website/reorder-photos', {
                    method      : 'POST',
                    credentials : 'same-origin',
                    headers     : {
                        'Content-Type'     : 'application/json',
                        'X-CSRF-TOKEN'     : csrfToken,
                        'X-Requested-With' : 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ order })
                });

                schedulePreview();
            }

            dragSrcEl.style.opacity = '1';
            return false;
        }

        document.querySelectorAll('.photo-item').forEach(el => {
            el.addEventListener('dragend', () => {
                el.style.opacity = '1';
                document.querySelectorAll('.photo-item')
                    .forEach(i => i.style.outline = '');
            });
        });

        // ── Toast de notificaciones ───────────────────────────────────────────────
        function showBuilderToast(msg, type = 'success') {
            const existing = document.getElementById('builderToast');
            if (existing) existing.remove();

            const toast         = document.createElement('div');
            toast.id            = 'builderToast';
            toast.textContent   = msg;
            toast.style.cssText = `
        position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);
        background:${type === 'danger' ? '#dc2626' : '#0f172a'};
        color:#fff;padding:.6rem 1.25rem;border-radius:99px;
        font-size:.82rem;font-weight:600;z-index:9999;
        box-shadow:0 4px 12px rgba(0,0,0,.3);
        animation:fadeInUp .3s ease`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // ── CSS animation ─────────────────────────────────────────────────────────
        const style     = document.createElement('style');
        style.textContent = `
    @keyframes fadeInUp {
        from { opacity:0; transform:translateX(-50%) translateY(10px) }
        to   { opacity:1; transform:translateX(-50%) translateY(0) }
    }`;
        document.head.appendChild(style);
    </script>

<?= $this->endSection() ?>