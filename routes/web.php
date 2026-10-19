<?php

use App\Http\Controllers\Dashboard\ActivityController;
use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\MediaController;
use App\Http\Controllers\Dashboard\PostController as DashboardPostController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RobotsController;
use Illuminate\Support\Facades\Route;

// Public blog.
Route::get('/', [PostController::class, 'index'])->name('home');
Route::get('/feed.xml', [PostController::class, 'feed'])->name('feed');
Route::get('/blog/{slug}', [PostController::class, 'show'])->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('posts.show');
Route::get('/sitemap.xml', [PostController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::permanentRedirect('/blog', '/');

// Dashboard. The first admin is created with `php artisan blog:admin`; admins add the rest under Users.
// Viewers can look around; changing anything needs the editor or admin role ('edit-content').
Route::middleware('guest')->group(function () {
    Route::get('/dashboard/login', [AuthController::class, 'show'])->name('login');
    Route::post('/dashboard/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');
    // Step 2: the code from the authenticator app, or setting the app up the first time.
    Route::get('/dashboard/login/two-factor', [AuthController::class, 'showChallenge'])->name('login.two-factor');
    Route::post('/dashboard/login/two-factor', [AuthController::class, 'verifyChallenge'])->middleware('throttle:10,1')->name('login.two-factor.verify');
    Route::get('/dashboard/login/two-factor/setup', [AuthController::class, 'showSetup'])->name('login.two-factor.setup');
    Route::post('/dashboard/login/two-factor/setup', [AuthController::class, 'confirmSetup'])->middleware('throttle:10,1')->name('login.two-factor.confirm');
});

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardPostController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/recovery-codes', [AuthController::class, 'recoveryCodes'])->name('dashboard.recovery-codes');

    Route::get('/posts', [DashboardPostController::class, 'index'])->name('dashboard.posts.index');
    Route::get('/media', [MediaController::class, 'index'])->name('dashboard.media.index');
    Route::get('/media/{media}/download', [MediaController::class, 'download'])->whereNumber('media')->name('dashboard.media.download');

    Route::middleware('can:edit-content')->group(function () {
        Route::post('/media', [MediaController::class, 'store'])->name('dashboard.media.store');
        Route::put('/media/{media}', [MediaController::class, 'update'])->name('dashboard.media.update');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('dashboard.media.destroy');

        Route::resource('posts', DashboardPostController::class)->except(['index', 'show'])->names('dashboard.posts');
    });

    // Settings change what every visitor sees, so they are for admins.
    Route::middleware('can:manage-settings')->group(function () {
        Route::get('/settings', [SettingsController::class, 'edit'])->name('dashboard.settings');
        Route::put('/settings', [SettingsController::class, 'update'])->name('dashboard.settings.update');
    });

    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class)->except(['show'])->names('dashboard.users');
        Route::post('/users/{user}/two-factor/reset', [UserController::class, 'resetTwoFactor'])->name('dashboard.users.two-factor.reset');
        Route::get('/activity', ActivityController::class)->name('dashboard.activity');
    });
});
