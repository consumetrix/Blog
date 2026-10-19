<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('edit-content', fn (User $user) => $user->canEdit());
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());
        Gate::define('manage-settings', fn (User $user) => $user->isAdmin());

        View::composer('*', function ($view) {
            $view->with('site', [
                'name' => config('blog.name'),
                'company' => config('blog.company'),
                'tagline' => config('blog.tagline'),
                'main_site' => config('blog.main_site'),
                'contact_url' => config('blog.contact_url'),
            ]);
        });

        View::composer('components.layouts.dashboard', function ($view) {
            $view->with('navCounts', auth()->check() ? [
                'posts' => Post::count(),
                'categories' => Category::count(),
                'media' => Media::count(),
                'users' => User::count(),
            ] : []);
        });
    }
}
