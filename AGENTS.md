# AGENTS.md

## Commands
- **Run tests:** `php artisan test`
- **Start development server:** `composer dev` (runs artisan serve, queue listener, pail, and Vite concurrently)
- **Database / Setup:** `composer setup` or `php artisan migrate`

## Architecture & Code Conventions
- **Framework:** Laravel 13 with PHP 8.3, Tailwind v4 / Vite frontend.
- **Primary Entrypoints:**
  - Routing: `routes/web.php`
  - Admin Controllers: `app/Http/Controllers/Admin/`
  - Models: `app/Models/` (`Menu`, `Staf`, `User`)
- **Authentication & Sessions:** Custom manual session checks (`session('staf_id')`) in controllers and routes instead of standard Laravel guards for admin sections.
- **Database Schema Notes:** Custom primary keys (e.g., `id_menu` on `menus`, `id_staf` on `staf`) and disabled timestamps where appropriate (`public $timestamps = false;` on `Menu`). Plain-text password fallback supported in `LoginController`.
- **File Uploads:** Managed under `public/uploads/menu/`.
