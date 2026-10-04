# Panel de citas

![Tests](https://github.com/JoseBenitezAlba/panel-citas/actions/workflows/tests.yml/badge.svg)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)

Aplicación web de gestión de citas hecha con **Laravel 13** y Blade. Los clientes piden y cancelan sus citas; los administradores gestionan servicios y estados y ven un dashboard con gráficas.

## Funcionalidades

- Registro, login y logout (autenticación de sesión de Laravel).
- Dos roles: `user` y `admin`. El rol no se puede elegir al registrarse. Un middleware protege toda la zona `/admin`.
- Clientes: ver sus citas, pedir una cita (servicio, fecha y hora) y cancelarla. No pueden ver ni cancelar las de otros.
- Validaciones: la fecha debe ser futura y no se puede reservar una hora ya ocupada para el mismo servicio (las canceladas liberan el hueco).
- Administración: CRUD básico de servicios (no se borra un servicio con citas), listado de todas las citas y cambio de estado (`pending`, `confirmed`, `cancelled`).
- Dashboard con KPIs (citas totales, ingresos confirmados) y gráficas con Chart.js: citas por estado y por servicio.
- 18 tests de feature y CI con GitHub Actions.

## Puesta en marcha

Requisitos: PHP 8.3 o superior, Composer y SQLite.

```bash
git clone https://github.com/JoseBenitezAlba/panel-citas.git
cd panel-citas
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Usuarios de prueba que crea el seeder (solo para desarrollo local):

| Rol | Email | Contraseña |
|---|---|---|
| admin | admin@example.com | password |
| user | cliente@example.com | password |

## Tests

```bash
php artisan test
```

## Estructura

```
app/Http/Controllers/            Auth y citas del cliente
app/Http/Controllers/Admin/      dashboard, servicios y gestión de citas
app/Http/Middleware/EnsureAdmin  control de acceso por rol
resources/views/                 plantillas Blade
tests/Feature/                   AuthTest, AppointmentTest, AdminTest
```

## Autor

José Manuel Benítez Alba, desarrollador web junior (PHP/Laravel), Cádiz. [GitHub](https://github.com/JoseBenitezAlba)
