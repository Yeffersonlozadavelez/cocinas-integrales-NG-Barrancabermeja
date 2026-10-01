<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Valorar nuestro trabajo - Cocinas Integrales NG</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    .valoracion-page {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .valoracion-header {
      background: var(--ng-negro);
      padding: 20px 0;
      text-align: center;
    }
    .valoracion-main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 20px;
    }
    .valoracion-card {
      background: var(--ng-blanco);
      padding: 40px;
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow);
      max-width: 600px;
      width: 100%;
    }
    .estrellas {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      font-size: 30px;
      cursor: pointer;
      color: #ccc;
    }
    .estrellas span:hover,
    .estrellas span.active {
      color: var(--ng-dorado);
    }

    /* Upload de imagen */
    .upload-area {
      border: 2px dashed rgba(194,155,99,0.4);
      border-radius: 10px;
      padding: 28px 20px;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.3s, background 0.3s;
      position: relative;
      background: rgba(194,155,99,0.03);
    }
    .upload-area:hover,
    .upload-area.drag-over {
      border-color: #C29B63;
      background: rgba(194,155,99,0.07);
    }
    .upload-area input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      width: 100%;
      height: 100%;
    }
    .upload-icon { font-size: 36px; margin-bottom: 8px; }
    .upload-text {
      font-size: 14px;
      color: #888;
      margin: 0;
    }
    .upload-text strong { color: #C29B63; }
    .upload-text small { display: block; margin-top: 4px; font-size: 12px; color: #aaa; }

    /* Preview */
    .img-preview-wrap {
      display: none;
      margin-top: 14px;
      position: relative;
    }
    .img-preview-wrap.show { display: block; }
    .img-preview {
      width: 100%;
      max-height: 220px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid rgba(194,155,99,0.3);
    }
    .img-remove {
      position: absolute;
      top: 8px; right: 8px;
      background: rgba(0,0,0,0.65);
      color: #fff;
      border: none;
      border-radius: 50%;
      width: 28px; height: 28px;
      font-size: 16px;
      cursor: pointer;
      line-height: 28px;
      text-align: center;
      transition: background 0.2s;
    }
    .img-remove:hover { background: #e05555; }
  </style>
</head>
<body>
  <div class="valoracion-page">
    <header class="valoracion-header">
      <a href="index.html" class="nav__logo-main" style="text-decoration: none;">COCINAS INTEGRALES NG</a>
    </header>
    
    <main class="valoracion-main">
      <div class="valoracion-card">
        <div class="section__header" style="margin-bottom: 30px;">
          <p class="eyebrow eyebrow--gold">Tu opinión es importante</p>
          <h2 class="section__title">Valora nuestro trabajo</h2>
        </div>
        
        <form id="valoracionForm" class="contacto__form" enctype="multipart/form-data">
          <div class="form__row">
            <div class="form__group">
              <label class="form__label" for="nombre">Nombre o Iniciales</label>
              <input class="form__input" type="text" id="nombre" name="nombre" placeholder="Ej. María Pérez o MP" required>
            </div>
            <div class="form__group">
              <label class="form__label" for="ciudad">Ciudad</label>
              <input class="form__input" type="text" id="ciudad" name="ciudad" value="Barrancabermeja" required>
            </div>
          </div>
          
          <div class="form__group">
            <label class="form__label">Calificación</label>
            <div class="estrellas" id="estrellas">
              <span data-val="1">★</span>
              <span data-val="2">★</span>
              <span data-val="3">★</span>
              <span data-val="4">★</span>
              <span data-val="5">★</span>
            </div>
            <input type="hidden" id="calificacion" name="calificacion" value="5">
          </div>
          
          <div class="form__group">
            <label class="form__label" for="mensaje">Testimonio</label>
            <textarea class="form__input form__textarea" id="mensaje" name="mensaje" placeholder="Cuéntanos qué te pareció nuestra cocina y servicio..." required></textarea>
          </div>

          <!-- Campo de imagen -->
          <div class="form__group">
            <label class="form__label">Foto de tu cocina <span style="color:#aaa;font-weight:400;">(opcional)</span></label>
            <div class="upload-area" id="uploadArea">
              <input type="file" id="imagenFile" name="imagen" accept="image/jpeg,image/png,image/webp">
              <div class="upload-icon">📷</div>
              <p class="upload-text">
                <strong>Haz clic o arrastra</strong> una foto aquí
                <small>JPG, PNG o WebP · máximo 5 MB</small>
              </p>
            </div>
            <div class="img-preview-wrap" id="previewWrap">
              <img class="img-preview" id="imgPreview" src="" alt="Vista previa">
              <button type="button" class="img-remove" id="removeImg" title="Quitar imagen">✕</button>
            </div>
          </div>
          
          <button type="submit" class="btn btn--gold btn--full">Enviar valoración</button>
          <div style="margin-top: 15px; text-align: center;">
            <a href="index.html" style="font-size: 14px; text-decoration: underline;">Volver al inicio</a>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script>
    // Estrellas
    const estrellas = document.querySelectorAll('#estrellas span');
    const inputCalif = document.getElementById('calificacion');
    
    function setEstrellas(val) {
      estrellas.forEach(e => {
        if(parseInt(e.dataset.val) <= val) {
          e.classList.add('active');
        } else {
          e.classList.remove('active');
        }
      });
    }
    
    // Inicializar en 5
    setEstrellas(5);
    
    estrellas.forEach(estrella => {
      estrella.addEventListener('click', function() {
        const val = this.dataset.val;
        inputCalif.value = val;
        setEstrellas(val);
      });
    });

    // Preview de imagen
    const fileInput   = document.getElementById('imagenFile');
    const previewWrap = document.getElementById('previewWrap');
    const imgPreview  = document.getElementById('imgPreview');
    const removeBtn   = document.getElementById('removeImg');
    const uploadArea  = document.getElementById('uploadArea');

    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
          imgPreview.src = e.target.result;
          previewWrap.classList.add('show');
          uploadArea.style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
      }
    });

    removeBtn.addEventListener('click', function() {
      fileInput.value = '';
      imgPreview.src = '';
      previewWrap.classList.remove('show');
      uploadArea.style.display = '';
    });

    // Drag & Drop
    uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('drag-over'); });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('drag-over'));
    uploadArea.addEventListener('drop', e => {
      e.preventDefault();
      uploadArea.classList.remove('drag-over');
      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        fileInput.dispatchEvent(new Event('change'));
      }
    });

    // Submit — usa FormData para enviar archivos
    document.getElementById('valoracionForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);

      const btn = this.querySelector('button[type="submit"]');
      const originalText = btn.innerText;
      btn.innerText = "Enviando...";
      btn.disabled = true;

      fetch('api_testimonios.php', {
        method: 'POST',
        body: formData   // Sin Content-Type header — el browser lo pone automáticamente con boundary
      })
      .then(r => r.json())
      .then(data => {
        if(data.status === 'success') {
          alert(data.message);
          window.location.href = 'index.html';
        } else {
          alert('Error: ' + data.message);
          btn.innerText = originalText;
          btn.disabled = false;
        }
      })
      .catch(e => {
        console.error(e);
        alert('Ocurrió un error');
        btn.innerText = originalText;
        btn.disabled = false;
      });
    });
  </script>
</body>
</html>
