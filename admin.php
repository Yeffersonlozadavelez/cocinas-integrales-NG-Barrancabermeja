<?php
session_start();
require_once 'conexion.php';

// Verificar sesión
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['admin_email'] !== 'yefferson.lozada.velez@ngbca.com') {
    die("Acceso denegado. Este usuario no tiene privilegios de administrador.");
}

// Acción sobre testimonio (aprobar/rechazar)
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $accion = $_GET['accion'];
    $id = intval($_GET['id']);
    if ($accion === 'aprobar') {
        $stmt = $conn->prepare("UPDATE testimonios SET estado = 'aprobado' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($accion === 'rechazar') {
        $stmt = $conn->prepare("DELETE FROM testimonios WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: admin.php#testimonios");
    exit();
}

// Obtener cotizaciones
try {
    $stmt = $conn->query("SELECT * FROM cotizaciones ORDER BY fecha_registro DESC");
    $cotizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    die("Error al consultar cotizaciones: " . $e->getMessage());
}

// Obtener testimonios pendientes
try {
    $stmtPend = $conn->query("SELECT * FROM testimonios WHERE estado = 'pendiente' ORDER BY fecha_registro DESC");
    $pendientes = $stmtPend->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $pendientes = [];
}

// Obtener testimonios aprobados
try {
    $stmtApro = $conn->query("SELECT * FROM testimonios WHERE estado = 'aprobado' ORDER BY fecha_registro DESC");
    $aprobados = $stmtApro->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $aprobados = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Cocinas NG</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #111111;
            color: #E8E3DA;
            padding: 2rem;
            font-family: 'Montserrat', sans-serif;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(194,155,99,0.3);
            padding-bottom: 1rem;
        }
        .admin-title {
            font-family: 'Playfair Display', serif;
            color: #C29B63;
            margin: 0;
            font-size: 24px;
        }
        .tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 28px;
            border-bottom: 1px solid #2a2a2a;
        }
        .tab-btn {
            padding: 10px 24px;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            color: #999490;
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.3s;
            margin-bottom: -1px;
        }
        .tab-btn.active {
            color: #C29B63;
            border-bottom-color: #C29B63;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .table-wrapper {
            overflow-x: auto;
            background: #1A1A1A;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; }
        th { color: #C29B63; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; }
        tr:hover { background: rgba(194,155,99,0.05); }
        .msg-cell { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .badge-pend {
            display: inline-block;
            background: #C29B63;
            color: #111;
            border-radius: 12px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 8px;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: #1A1A1A;
            border: 1px solid rgba(194,155,99,0.25);
            border-radius: 14px;
            padding: 2rem;
            width: 90%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            animation: modalIn 0.25s ease;
        }
        @keyframes modalIn {
            from { transform: translateY(-20px); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }
        .modal-close {
            position: absolute;
            top: 14px; right: 18px;
            background: none;
            border: none;
            color: #888;
            font-size: 22px;
            cursor: pointer;
            line-height: 1;
            transition: color 0.2s;
        }
        .modal-close:hover { color: #C29B63; }
        .modal-title {
            font-family: 'Playfair Display', serif;
            color: #C29B63;
            font-size: 20px;
            margin: 0 0 1.2rem;
        }
        .modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 20px;
            margin-bottom: 1.2rem;
        }
        .modal-field label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #C29B63;
            margin-bottom: 4px;
        }
        .modal-field span {
            font-size: 14px;
            color: #E8E3DA;
            font-weight: 500;
        }
        .modal-mensaje-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #C29B63;
            margin-bottom: 6px;
            display: block;
        }
        .modal-mensaje {
            background: #111;
            border-radius: 8px;
            padding: 14px;
            font-size: 14px;
            color: #E8E3DA;
            line-height: 1.65;
            border: 1px solid rgba(194,155,99,0.15);
            margin-bottom: 1.4rem;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .modal-imagen-wrap { margin-bottom: 1.4rem; }
        .modal-imagen {
            display: block;
            max-width: 100%;
            max-height: 320px;
            margin: 0 auto;
            border: 1px solid rgba(194,155,99,0.3);
            border-radius: 8px;
            object-fit: contain;
        }
        .modal-imagen-trigger {
            display: block;
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
            border: 0;
            background: none;
            cursor: zoom-in;
        }
        .modal-imagen-trigger:focus-visible,
        .imagen-ampliada__cerrar:focus-visible {
            outline: 2px solid #C29B63;
            outline-offset: 4px;
        }
        .imagen-ampliada {
            position: fixed;
            inset: 0;
            z-index: 1100;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(0,0,0,0.94);
        }
        .imagen-ampliada[hidden] { display: none; }
        .imagen-ampliada__foto {
            max-width: 96vw;
            max-height: 90vh;
            object-fit: contain;
        }
        .imagen-ampliada__cerrar {
            position: absolute;
            top: 16px;
            right: 20px;
            width: 44px;
            height: 44px;
            border: 1px solid rgba(255,255,255,0.45);
            border-radius: 50%;
            background: rgba(0,0,0,0.45);
            color: #fff;
            font-size: 26px;
            cursor: pointer;
        }
        .modal-actions { display: flex; gap: 12px; justify-content: flex-end; }
        .btn-ver {
            display: inline-block;
            padding: 5px 13px;
            background: rgba(194,155,99,0.15);
            color: #C29B63;
            border: 1px solid rgba(194,155,99,0.4);
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            margin-right: 6px;
        }
        .btn-ver:hover { background: rgba(194,155,99,0.3); }
        .stars { color: #C29B63; font-size: 16px; letter-spacing: 2px; }
        .btn-aprobar {
            display: inline-block;
            padding: 6px 16px;
            background: #C29B63;
            color: #111;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.3s;
            margin-right: 6px;
        }
        .btn-aprobar:hover { background: #9B7C4F; }
        .btn-rechazar {
            display: inline-block;
            padding: 6px 16px;
            background: transparent;
            color: #e05555;
            border: 1px solid #e05555;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-rechazar:hover { background: #e05555; color: #fff; }
        .btn-logout {
            padding: 8px 16px;
            background: transparent;
            border: 1px solid #C29B63;
            color: #C29B63;
            border-radius: 4px;
            text-decoration: none;
            transition: 0.3s;
            font-size: 13px;
        }
        .btn-logout:hover { background: #C29B63; color: #111; }
        .empty-state { text-align: center; padding: 3rem; color: #888; }
        .empty-state h3 { color: #C29B63; margin-bottom: 8px; }
        .section-title {
            font-family: 'Playfair Display', serif;
            color: #C29B63;
            font-size: 18px;
            margin: 28px 0 14px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1400px; padding: 0;">
        <div class="admin-header">
            <h1 class="admin-title">Panel de Administración — Cocinas NG</h1>
            <div>
                <span style="margin-right: 15px; color: #aaa; font-size: 13px;">
                    <?php echo htmlspecialchars($_SESSION['admin_email']); ?>
                </span>
                <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>

        <!-- TABS -->
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('cotizaciones')">
                Cotizaciones
            </button>
            <button class="tab-btn" onclick="showTab('testimonios')" id="btn-tab-testimonios">
                Valoraciones
                <?php if(count($pendientes) > 0): ?>
                    <span class="badge-pend"><?php echo count($pendientes); ?></span>
                <?php endif; ?>
            </button>
        </div>

        <!-- TAB: COTIZACIONES -->
        <div class="tab-content active" id="tab-cotizaciones">
            <div class="table-wrapper">
                <?php if (count($cotizaciones) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Nombre</th>
                                <th>Celular</th>
                                <th>Email</th>
                                <th>Servicio</th>
                                <th>Mensaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cotizaciones as $c): ?>
                            <tr>
                                <td>#<?php echo $c['id']; ?></td>
                                <td><?php echo date('d/m/Y h:i A', strtotime($c['fecha_registro'])); ?></td>
                                <td style="font-weight:600;"><?php echo htmlspecialchars($c['nombre']); ?></td>
                                <td>
                                    <a href="https://wa.me/57<?php echo preg_replace('/[^0-9]/', '', $c['telefono']); ?>" target="_blank" style="color:#C29B63; text-decoration:none;">
                                        <?php echo htmlspecialchars($c['telefono']); ?> ↗
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                                <td><?php echo htmlspecialchars($c['servicio']); ?></td>
                                <td class="msg-cell" title="<?php echo htmlspecialchars($c['mensaje']); ?>">
                                    <?php echo htmlspecialchars($c['mensaje']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>Aún no hay cotizaciones registradas.</h3>
                        <p>Las solicitudes del formulario aparecerán aquí.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TAB: VALORACIONES -->
        <div class="tab-content" id="tab-testimonios">

            <!-- Pendientes -->
            <h3 class="section-title">⏳ Pendientes de revisión (<?php echo count($pendientes); ?>)</h3>
            <div class="table-wrapper">
                <?php if (count($pendientes) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Nombre</th>
                                <th>Ciudad</th>
                                <th>Calificación</th>
                                <th>Testimonio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pendientes as $t): ?>
                            <tr>
                                <td>#<?php echo $t['id']; ?></td>
                                <td><?php echo date('d/m/Y h:i A', strtotime($t['fecha_registro'])); ?></td>
                                <td style="font-weight:600;"><?php echo htmlspecialchars($t['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($t['ciudad']); ?></td>
                                <td>
                                    <span class="stars">
                                        <?php echo str_repeat('★', $t['calificacion']) . str_repeat('☆', 5 - $t['calificacion']); ?>
                                    </span>
                                    (<?php echo $t['calificacion']; ?>/5)
                                </td>
                                <td class="msg-cell" title="<?php echo htmlspecialchars($t['mensaje']); ?>">
                                    <?php echo htmlspecialchars($t['mensaje']); ?>
                                </td>
                                <td style="white-space:nowrap;">
                                    <button class="btn-ver" onclick="abrirModal(
                                        '<?php echo $t['id']; ?>',
                                        '<?php echo addslashes(htmlspecialchars($t['nombre'])); ?>',
                                        '<?php echo addslashes(htmlspecialchars($t['ciudad'])); ?>',
                                        <?php echo $t['calificacion']; ?>,
                                        '<?php echo addslashes(htmlspecialchars($t['mensaje'])); ?>',
                                        '<?php echo htmlspecialchars($t['imagen'] ?? '', ENT_QUOTES, 'UTF-8'); ?>'
                                    )">👁 Ver</button>
                                    <a href="admin.php?accion=aprobar&id=<?php echo $t['id']; ?>" class="btn-aprobar"
                                       onclick="return confirm('¿Aprobar este testimonio?')">✓ Aprobar</a>
                                    <a href="admin.php?accion=rechazar&id=<?php echo $t['id']; ?>" class="btn-rechazar"
                                       onclick="return confirm('¿Eliminar este testimonio?')">✕ Rechazar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No hay valoraciones pendientes.</h3>
                        <p>Cuando un cliente envíe una valoración aparecerá aquí para revisión.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Aprobados -->
            <h3 class="section-title">✅ Publicados en el sitio (<?php echo count($aprobados); ?>)</h3>
            <div class="table-wrapper">
                <?php if (count($aprobados) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Nombre</th>
                                <th>Ciudad</th>
                                <th>Calificación</th>
                                <th>Testimonio</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($aprobados as $t): ?>
                            <tr>
                                <td>#<?php echo $t['id']; ?></td>
                                <td><?php echo date('d/m/Y h:i A', strtotime($t['fecha_registro'])); ?></td>
                                <td style="font-weight:600;"><?php echo htmlspecialchars($t['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($t['ciudad']); ?></td>
                                <td>
                                    <span class="stars">
                                        <?php echo str_repeat('★', $t['calificacion']) . str_repeat('☆', 5 - $t['calificacion']); ?>
                                    </span>
                                </td>
                                <td class="msg-cell" title="<?php echo htmlspecialchars($t['mensaje']); ?>">
                                    <?php echo htmlspecialchars($t['mensaje']); ?>
                                </td>
                                <td>
                                    <a href="admin.php?accion=rechazar&id=<?php echo $t['id']; ?>" class="btn-rechazar"
                                       onclick="return confirm('¿Eliminar este testimonio publicado?')">✕ Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No hay testimonios publicados aún.</h3>
                        <p>Aprueba una valoración para que aparezca en el sitio.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Modal de detalle de valoración -->
    <div class="modal-overlay" id="modalDetalle">
        <div class="modal-box">
            <button class="modal-close" onclick="cerrarModal()">✕</button>
            <h2 class="modal-title">Detalle de Valoración</h2>
            <div class="modal-grid">
                <div class="modal-field">
                    <label>ID</label>
                    <span id="modal-id"></span>
                </div>
                <div class="modal-field">
                    <label>Calificación</label>
                    <span id="modal-estrellas" style="font-size:18px; color:#C29B63;"></span>
                </div>
                <div class="modal-field">
                    <label>Nombre</label>
                    <span id="modal-nombre"></span>
                </div>
                <div class="modal-field">
                    <label>Ciudad</label>
                    <span id="modal-ciudad"></span>
                </div>
            </div>
            <div class="modal-imagen-wrap" id="modal-imagen-wrap" hidden>
                <span class="modal-mensaje-label">Foto adjunta · Haz clic para ampliar</span>
                <button class="modal-imagen-trigger" id="modal-imagen-trigger" type="button" aria-label="Ampliar imagen adjunta">
                    <img class="modal-imagen" id="modal-imagen" alt="Imagen adjunta a la valoración">
                </button>
            </div>
            <label class="modal-mensaje-label">Mensaje del cliente</label>
            <div class="modal-mensaje" id="modal-mensaje"></div>
            <div class="modal-actions">
                <a id="modal-btn-rechazar" href="#" class="btn-rechazar"
                   onclick="return confirm('¿Eliminar esta valoración?')">✕ Rechazar</a>
                <a id="modal-btn-aprobar" href="#" class="btn-aprobar"
                   onclick="return confirm('¿Aprobar esta valoración?')">✓ Aprobar</a>
            </div>
        </div>
    </div>
    <div class="imagen-ampliada" id="imagen-ampliada" role="dialog" aria-modal="true" aria-label="Imagen ampliada de la valoración" aria-hidden="true" hidden>
        <button class="imagen-ampliada__cerrar" id="imagen-ampliada-cerrar" type="button" aria-label="Cerrar imagen ampliada">×</button>
        <img class="imagen-ampliada__foto" id="imagen-ampliada-foto" alt="Imagen ampliada de la valoración">
    </div>

    <script>
        function showTab(name) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById('tab-' + name).classList.add('active');
            event.target.closest('.tab-btn').classList.add('active');
        }

        // Si la URL tiene #testimonios, abrir esa pestaña al volver
        if (window.location.hash === '#testimonios') {
            document.querySelectorAll('.tab-btn')[1].click();
        }

        function abrirModal(id, nombre, ciudad, calificacion, mensaje, imagen) {
            document.getElementById('modal-id').textContent = '#' + id;
            document.getElementById('modal-nombre').textContent = nombre;
            document.getElementById('modal-ciudad').textContent = ciudad;
            document.getElementById('modal-mensaje').textContent = mensaje;

            const imagenWrap = document.getElementById('modal-imagen-wrap');
            const imagenPreview = document.getElementById('modal-imagen');
            imagenPreview.src = imagen;
            imagenWrap.hidden = !imagen;

            const estrellas = '★'.repeat(calificacion) + '☆'.repeat(5 - calificacion);
            document.getElementById('modal-estrellas').textContent = estrellas + ' (' + calificacion + '/5)';

            document.getElementById('modal-btn-aprobar').href = 'admin.php?accion=aprobar&id=' + id;
            document.getElementById('modal-btn-rechazar').href = 'admin.php?accion=rechazar&id=' + id;

            document.getElementById('modalDetalle').classList.add('open');
        }

        function cerrarModal() {
            document.getElementById('modalDetalle').classList.remove('open');
        }

        const vistaAmpliada = document.getElementById('imagen-ampliada');
        const fotoAmpliada = document.getElementById('imagen-ampliada-foto');
        const botonImagen = document.getElementById('modal-imagen-trigger');
        const botonCerrarImagen = document.getElementById('imagen-ampliada-cerrar');

        botonImagen.addEventListener('click', function() {
            fotoAmpliada.src = document.getElementById('modal-imagen').src;
            vistaAmpliada.hidden = false;
            vistaAmpliada.setAttribute('aria-hidden', 'false');
            botonCerrarImagen.focus();
        });

        function cerrarImagenAmpliada() {
            vistaAmpliada.hidden = true;
            vistaAmpliada.setAttribute('aria-hidden', 'true');
            fotoAmpliada.removeAttribute('src');
            botonImagen.focus();
        }

        botonCerrarImagen.addEventListener('click', cerrarImagenAmpliada);
        vistaAmpliada.addEventListener('click', function(e) {
            if (e.target === vistaAmpliada) cerrarImagenAmpliada();
        });

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalDetalle').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });

        // Cerrar con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (!vistaAmpliada.hidden) cerrarImagenAmpliada();
                else cerrarModal();
            }
        });
    </script>
</body>
</html>
