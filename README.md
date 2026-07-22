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

## Documentación

- [Product Brief](docs/product-brief.md)
- [Backup y Restauración](docs/backup-restore.md)
- [Guía de Despliegue](docs/deployment.md)
