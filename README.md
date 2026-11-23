# ☁️ Nube NAS Personal en Línea

**Universidad de El Salvador**  
**Facultad Multidisciplinaria Oriental (FMO)**  

### 👥 Autores
* **Angélica Amaya**
* **Cristian Ventura**
* **Luis Galeas**

---

## 📋 Descripción del Proyecto

Sistema de almacenamiento en red (**NAS**) autohospedado sobre **Raspberry Pi 5** con **OpenMediaVault**, diseñado como una nube privada accesible desde cualquier lugar del mundo mediante **Tailscale Funnel**.

El sistema proporciona una interfaz web personalizada inspirada en Google Drive que permite a usuarios autenticados gestionar sus archivos privados y compartidos por departamentos, garantizando el aislamiento estricto de datos y el control de permisos por rol.

---

## 🌐 Acceso a la Plataforma

* **Enlace Público (HTTPS):** `https://raspberry-nas.tail5a60a6.ts.net`
* **Disponibilidad:** Accesible desde cualquier navegador web (escritorio o dispositivos móviles) y desde redes externas sin necesidad de instalar clientes VPN en el equipo usuario ni exponer puertos en el enrutador local.

---

## ✨ Funcionalidades Principales

### 👤 Módulo de Usuarios
* **Autenticación Segura:** Registro e inicio de sesión con cifrado de contraseñas mediante Bcrypt.
* **Unidad Privada:** Espacio de almacenamiento exclusivo (`usuario_<id>`) donde únicamente el propietario puede visualizar, cargar, descargar y eliminar sus documentos.
* **Espacios Colaborativos por Grupo:** Acceso controlado a la carpeta compartida del departamento asignado por un administrador.
* **Control Granular de Permisos:** Aplicación estricta de niveles de acceso (**Solo lectura** vs. **Lectura y escritura**) tanto en la interfaz como en la capa de servidor.

### 🛡️ Módulo de Administración
* **Gestión de Departamentos (Grupos):** Creación de grupos de trabajo que generan automáticamente su directorio físico dentro del almacenamiento NAS.
* **Administración de Cuentas y Roles:** Asignación de usuarios a departamentos, configuración de permisos de lectura/escritura, promoción de administradores y eliminación de cuentas junto con su almacenamiento asociado.

---

## 🛠️ Arquitectura y Stack Tecnológico

| Capa del Sistema | Tecnología | Responsabilidad |
| :--- | :--- | :--- |
| **Hardware** | Raspberry Pi 5 + Almacenamiento externo | Servidor físico centralizado de bajo consumo energético. |
| **Almacenamiento NAS** | OpenMediaVault (OMV) | Administración del sistema de archivos físico y puntos de montaje. |
| **Backend (MVC)** | PHP / Laravel | Enrutamiento, middlewares de seguridad, aislamiento de rutas y operaciones de archivo. |
| **Base de Datos** | SQLite | Persistencia relacional ligera de usuarios, roles, grupos y permisos. |
| **Frontend** | Blade + Bootstrap 5 + JavaScript (`fetch`) | Interfaz responsiva y transferencia segura de archivos en memoria desde escritorio y móvil. |
| **Acceso Remoto** | Tailscale Funnel | Túnel inverso cifrado con certificado SSL/TLS automático hacia Internet. |

---

## 📂 Estructura del Código Fuente

```text
nube_nas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php      # Gestión administrativa de grupos, usuarios y permisos
│   │   │   ├── ArchivoController.php    # Carga, descarga, listado y eliminación en el NAS
│   │   │   └── AuthController.php       # Registro, autenticación y cierre de sesión
│   │   └── Middleware/
│   │       └── EsAdmin.php              # Control de acceso para rutas administrativas
│   └── Models/
│       ├── Grupo.php                    # Modelo de departamentos y relación con usuarios
│       └── User.php                     # Modelo de usuarios, roles y permisos
├── database/
│   ├── migrations/                      # Definición estructural de tablas (users, grupos, permisos)
│   └── database.sqlite                  # Base de datos del sistema
├── resources/views/
│   ├── admin/index.blade.php            # Panel de administración
│   ├── auth/                            # Vistas de inicio de sesión y registro
│   ├── inicio.blade.php                 # Portada pública del sistema
│   └── panel.blade.php                  # Explorador y gestor de archivos web
└── routes/
    └── web.php                          # Definición de rutas HTTP y middlewares de seguridad
🔒 Mecanismos de Seguridad
Aislamiento de Directorios (Prevención de Path Traversal): El servidor construye internamente las rutas de destino a partir de la sesión autenticada (auth()->user()), sanitizando los nombres de archivo e impidiendo el acceso a directorios fuera del ámbito autorizado.

Validación en Backend: Toda operación de escritura o borrado verifica en la base de datos que el usuario disponga del permiso escritura antes de ejecutar cambios sobre el disco físico.

Cifrado de Extremo a Extremo: Todo el tráfico web y las cookies de sesión viajan protegidos bajo protocolo HTTPS mediante Tailscale Funnel.
