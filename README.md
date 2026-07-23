# Control de Operaciones de Agentes Bancarios

Aplicación web interna para digitalizar el registro manual de operaciones efectuadas en una red de cajeros corresponsales o agentes bancarios.

## Requisitos

- PHP 8.3+
- Composer
- MySQL 8.0 o MariaDB compatible
- Node.js (solo para compilar assets; no requerido en producción)

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configurar JWT_SIGNING_KEY y REFRESH_PEPPER en .env
php artisan migrate --seed
npm ci && npm run build
```

## Despliegue

El servidor web DEBE exponer **únicamente** el directorio `public/` como document root. No se deben servir archivos del directorio raíz del proyecto.

Procedimiento documentado en `docs/deployment.md`.

## Rutas Demo (Revision Visual)

Las siguientes rutas muestran las maquetas visuales con datos de demostracion:

| Ruta | Descripcion |
|---|---|
| `/demo/login` | Login (normal) |
| `/demo/login?state=error` | Login con error de credenciales |
| `/demo/login?state=disabled` | Login con usuario desactivado |
| `/demo/login?state=throttled` | Login con limite de intentos |
| `/demo/login?state=network-error` | Login con error de red |
| `/demo/login?state=loading` | Login en carga |
| `/demo/expiry?expiry=warning` | Modal de expiracion (30s) |
| `/demo/expiry?expiry=renewing` | Modal renovando |
| `/demo/expiry?expiry=expired` | Modal sesion expirada |
| `/demo/expiry?expiry=revoked` | Modal sesion revocada |
| `/demo/operator/dashboard` | Dashboard del operador |
| `/demo/operator/register` | Registro de operacion |
| `/demo/operator/history` | Historial de operaciones |
| `/demo/admin/dashboard` | Dashboard administrativo |
| `/demo/daily-closing/1` | Cierre diario ACTIVO |
| `/demo/daily-closing/1?status=confirmed` | Cierre diario CONFIRMADO |
| `/demo/daily-closing/1?status=reopened` | Cierre diario REABIERTO |

## Documentacion

- [Product Brief](docs/product-brief.md)
- [Backup y Restauracion](docs/backup-restore.md)
- [Guia de Despliegue](docs/deployment.md)
