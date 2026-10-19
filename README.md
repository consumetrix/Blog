# Consumetrix Blog (blog)

A standalone Laravel 13 blog extracted from the Consumetrix website's Insights section (now just the blog) and its dashboard.

**Public:** `/` (list, `?type=` category filter, pagination), `/blog/{slug}`, `/feed.xml`, `/sitemap.xml`, `/robots.txt`.

**Dashboard (`/dashboard`):** Posts (rich-text editor, draft / scheduled / live, image upload), Media library,
Users and Activity log (admins), sign-in with optional authenticator-app 2FA. Roles: admin, editor, viewer.

A post is live when `status = published` and `published_at <= now` (a future date schedules it).

## Setup

```sh
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed        # seeds 4 sample posts
php artisan storage:link
npm install && npm run build
php artisan blog:admin you@example.com --name="Your Name"
php artisan serve
```

Settings live in `config/blog.php` / `.env` (`BLOG_*`, `LOGIN_TWO_FACTOR`). Set `LOGIN_TWO_FACTOR=false` locally.
