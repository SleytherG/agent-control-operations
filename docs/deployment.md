# Procedimiento de Despliegue

## Apache

```apache
<VirtualHost *:443>
    DocumentRoot /var/www/public
    <Directory /var/www/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Nginx

```nginx
server {
    listen 443 ssl;
    root /var/www/public;
    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Configuración requerida

- `APP_ENV=production`
- `APP_DEBUG=false`
- Compilar assets: `npm run build`
- Asegurar que `public/build/manifest.json` existe después del build.

## Rollback

Revertir migraciones con `php artisan migrate:rollback` y restaurar código anterior desde el sistema de control de versiones.
