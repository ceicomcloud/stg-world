<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Dashboard\Profile;

use App\Livewire\Game\Game;
use App\Livewire\Game\Empire;
use App\Livewire\Game\Relations;
use App\Livewire\Game\Building;
use App\Livewire\Game\Galaxy;
use App\Livewire\Game\Trade;
use App\Livewire\Game\Technology;
use App\Livewire\Game\ManagePlanet;
use App\Livewire\Game\ManagePlayers;
use App\Livewire\Game\Inventory;
use App\Livewire\Game\Ranking;
use App\Livewire\Game\Alliance;
use App\Livewire\Game\Chatbox;
use App\Livewire\Game\PrivateMessaging;
use App\Livewire\Game\Forum;
use App\Livewire\Game\ForumTopic;
use App\Livewire\Game\ForumPost;
use App\Livewire\Game\Bunker;
use App\Livewire\Game\Mission\Mission;
use App\Livewire\Game\Mission\MissionSpatial;
use App\Livewire\Game\Mission\MissionEarth;
use App\Livewire\Game\Mission\MissionTransport;
use App\Livewire\Game\Mission\MissionColonize;
use App\Livewire\Game\Mission\MissionSpy;
use App\Livewire\Game\Mission\MissionBasement;
use App\Livewire\Game\Mission\MissionExtract;
use App\Livewire\Game\Mission\MissionExplore;
use App\Livewire\Game\Customization;
use App\Livewire\Game\Rapport;
use App\Livewire\Game\EventRanking;

// Composants d'administration
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Http\Controllers\PaypalController;

// Routes d'authentification (middleware guest)
Route::middleware('guest')->group(function () {
    // Appliquer le middleware de limitation de tentatives de connexion à la route de login
    Route::get('/', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('forgot-password');
});

 // Logout route (accessible to authenticated users)
Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});

// Routes protégées avec préfixe dashboard
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', Dashboard::class)->name('index');
    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/settings', \App\Livewire\Dashboard\Setting::class)->name('settings');
});

// Routes de jeu
Route::middleware(['auth', 'game', 'game.vacation'])->prefix('game')->name('game.')->group(function () {
    Route::get('/', Game::class)->name('index');
    Route::get('/empire', Empire::class)->name('empire');
    Route::get('/relations', Relations::class)->name('relations');
    Route::get('/construction/{type?}', Building::class)
        ->defaults('type', 'building')
        ->where('type', 'building|unit|defense|ship|equip')
        ->name('construction.type');
    Route::get('/technology', Technology::class)->name('technology');
    Route::get('/galaxy', Galaxy::class)->name('galaxy');
    Route::get('/manage-planet', ManagePlanet::class)->name('manage-planet');
    Route::get('/manage-players', ManagePlayers::class)->name('manage-players');
    Route::get('/inventory', Inventory::class)->name('inventory');
    Route::get('/customization', Customization::class)->name('customization');
    Route::get('/rapport/{key}', Rapport::class)->name('rapport');

    // Les callbacks PayPal sont gérés via query sur manage-players
   
    Route::get('/trade', Trade::class)->name('trade');
    Route::get('/ranking', Ranking::class)->name('ranking');
    // Pages Alliance (routées par composants Livewire, menu persistant via layout)
    Route::prefix('alliance')->name('alliance.')->group(function () {
        Route::get('/', \App\Livewire\Game\Alliance\Overview::class)->name('overview');
        Route::get('/membres', \App\Livewire\Game\Alliance\Members::class)->name('members');
        Route::get('/banque', \App\Livewire\Game\Alliance\Bank::class)->name('bank');
        Route::get('/rangs', \App\Livewire\Game\Alliance\Rank::class)->name('ranks');
        Route::get('/gestion', \App\Livewire\Game\Alliance\Management::class)->name('management');
        Route::get('/candidatures', \App\Livewire\Game\Alliance\Application::class)->name('applications');
        Route::get('/guerres', \App\Livewire\Game\Alliance\Wars::class)->name('wars');
        Route::get('/technologies', \App\Livewire\Game\Alliance\Technology::class)->name('technologies');
        // Pour les utilisateurs sans alliance
        Route::get('/rechercher', \App\Livewire\Game\Alliance\Join::class)->name('search');
        Route::get('/creer', \App\Livewire\Game\Alliance\Create::class)->name('create');
    });
    Route::get('/chatbox', Chatbox::class)->name('chatbox');
    Route::get('/private', PrivateMessaging::class)->name('private');
    Route::get('/bunker', Bunker::class)->name('bunker');
    Route::get('/forum', Forum::class)->name('forum');
    Route::get('/forum/{categoryId}/{forumId}', ForumTopic::class)->name('forum.topics');
    Route::get('/forum/{categoryId}/{forumId}/{topicId}', ForumPost::class)->name('forum.topic');

    Route::prefix('mission')->name('mission.')->group(function () {
        Route::get('/', Mission::class)->name('index');
        Route::get('/spatial/{targetPlanetId}', MissionSpatial::class)->name('spatial');
        Route::get('/earth/{targetPlanetId}', MissionEarth::class)->name('earth');
        Route::get('/spy/{targetPlanetId}', MissionSpy::class)->name('spy');
        Route::get('/colonize/{templateId}', MissionColonize::class)->name('colonize');
        Route::get('/transport/{targetPlanetId}', MissionTransport::class)->name('transport');
        Route::get('/extract/{templateId}', MissionExtract::class)->name('extract');
        Route::get('/explore/{templateId}', MissionExplore::class)->name('explore');
        Route::get('/basement/{targetPlanetId}', MissionBasement::class)->name('basement');
    });

    // Endpoints PayPal
    Route::post('/paypal/create-order', [PaypalController::class, 'createOrder'])->name('paypal.create');
    Route::post('/paypal/capture-order', [PaypalController::class, 'captureOrder'])->name('paypal.capture');
});

// Pages succès / annulation PayPal
Route::get('/paypal/success', [PaypalController::class, 'success'])->name('paypal.success');
Route::get('/paypal/cancel', [PaypalController::class, 'cancel'])->name('paypal.cancel');

// Webhook PayPal (signature à vérifier en prod)
Route::post('/webhooks/paypal', [PaypalController::class, 'webhook'])->name('webhooks.paypal');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Tableau de bord d'administration
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('/users', \App\Livewire\Admin\Users::class)->name('users');
    Route::get('/planets/{id?}', \App\Livewire\Admin\Planets::class)->name('planets');
    Route::get('/alliances', \App\Livewire\Admin\Alliances::class)->name('alliances');
    Route::get('/factions', \App\Livewire\Admin\Factions::class)->name('factions');

    // Routes pour la gestion des templates
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/resources', \App\Livewire\Admin\Template\Resources::class)->name('resources');
        Route::get('/buildings', \App\Livewire\Admin\Template\Buildings::class)->name('buildings');
        Route::get('/planets', \App\Livewire\Admin\Template\Planets::class)->name('planets');
        Route::get('/badges', \App\Livewire\Admin\Template\Badges::class)->name('badges');
    });
    
    Route::get('/settings', \App\Livewire\Admin\Settings::class)->name('settings');
    Route::get('/options', \App\Livewire\Admin\Options::class)->name('options');
    Route::get('/news', \App\Livewire\Admin\News::class)->name('news');
    Route::get('/logs', \App\Livewire\Admin\Logs::class)->name('logs');
    Route::get('/forum', \App\Livewire\Admin\Forum::class)->name('forum');
    Route::get('/messaging', \App\Livewire\Admin\Messaging::class)->name('messaging');
    Route::get('/jobs', \App\Livewire\Admin\Jobs::class)->name('jobs');
    Route::get('/payments', \App\Livewire\Admin\Payments::class)->name('payments');

    // Événements serveur
    Route::get('/server-events', \App\Livewire\Admin\ServerEvents::class)->name('server-events');

    // Discord Webhooks
    Route::get('/discord', \App\Livewire\Admin\DiscordWebhooks::class)->name('discord');
});

use App\Http\Controllers\UploadController;
Route::post('/editor/image', [UploadController::class, 'storeEditorImage'])->name('editor.image.store');

// Sitemap XML (basic static sitemap). Set APP_URL=https://stg-world.fr in .env
Route::get('/sitemap.xml', function () {
    $base = rtrim(config('app.url') ?? url('/'), '/');
    $now = now()->toAtomString();
    $urls = [
        '/',
        '/register',
        '/forgot-password',
        // Add more public pages here when available
    ];

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($urls as $path) {
        $loc = $base . $path;
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
        $xml .= "    <lastmod>" . $now . "</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.8</priority>\n";
        $xml .= "  </url>\n";
    }
    $xml .= "</urlset>\n";

    return response($xml, 200)->header('Content-Type', 'application/xml');
});
