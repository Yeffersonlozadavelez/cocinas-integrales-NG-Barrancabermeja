# cocinas-integrales-NG-Barrancabermeja
Sitio web de Cocinas Integrales NG con PHP y MySQL.

## Instalacion local

1. Coloca el proyecto en la carpeta `htdocs` de XAMPP.
2. Inicia Apache y MySQL e importa `database.sql` desde phpMyAdmin.
3. Si tu MySQL no usa el usuario `root` sin contrasena, actualiza los datos de conexion en `conexion.php`.
4. Crea el primer administrador en la base `cocinas_ng`, reemplazando el texto de ejemplo por una contrasena propia:

```sql
INSERT INTO administradores (email, password_hash)
VALUES ('yefferson.lozada.velez@ngbca.com', SHA2('REEMPLAZA_CON_UNA_CLAVE_SEGURA', 256));
```

5. Abre `http://localhost/proyectoNG/index.html`.

Las fotos que los clientes adjuntan a sus valoraciones se guardan en `img/testimonios/` y no se incluyen en Git.
