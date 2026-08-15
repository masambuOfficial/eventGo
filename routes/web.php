<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Events\DashboardController as EventDashboardController;
use App\Http\Controllers\Events\IndexController as EventIndexController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Providers\DashboardController as ProviderDashboardController;
use App\Livewire\Admin\VerificationQueue;
use App\Livewire\Events\Wizard as EventWizard;
use App\Livewire\Providers\Availability as ProviderAvailability;
use App\Livewire\Providers\Media as ProviderMedia;
use App\Livewire\Providers\Onboarding as ProviderOnboarding;
use App\Livewire\Providers\Offers\Index as ProviderOfferIndex;
use App\Livewire\Providers\Offers\Submit as ProviderOfferSubmit;
use App\Livewire\Providers\Opportunities\Browse as ProviderOpportunityBrowse;
use App\Livewire\Providers\Opportunities\Invitations as ProviderInvitations;
use App\Livewire\Providers\RequestVerification;
use App\Livewire\Sourcing\OfferComparison;
use App\Livewire\Sourcing\OpportunityPanel;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', HomeController::class)->name('home');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/verifications', VerificationQueue::class)->name('verifications.index');
    });

    Route::prefix('provider')->name('provider.')->group(function () {
        Route::get('/dashboard', ProviderDashboardController::class)->name('dashboard');
        Route::get('/onboarding', ProviderOnboarding::class)->name('onboarding');
        Route::get('/availability', ProviderAvailability::class)->name('availability.index');
        Route::get('/media', ProviderMedia::class)->name('media.index');
        Route::get('/verification', RequestVerification::class)->name('verification.create');
        Route::get('/opportunities', ProviderOpportunityBrowse::class)->name('opportunities.index');
        Route::get('/invitations', ProviderInvitations::class)->name('invitations.index');
        Route::get('/offers', ProviderOfferIndex::class)->name('offers.index');
    });

    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', EventIndexController::class)->name('index');
        Route::get('/create', EventWizard::class)->name('create');
        Route::get('/{event}/wizard', EventWizard::class)->name('wizard');
        Route::get('/{event}', EventDashboardController::class)->name('dashboard');
    });

    Route::get('/requirements/{requirement}/sourcing', OpportunityPanel::class)->name('sourcing.show');
    Route::get('/requirements/{requirement}/offers', OfferComparison::class)->name('sourcing.offers');
    Route::get('/requirements/{requirement}/offers/submit', ProviderOfferSubmit::class)->name('offers.submit');
});
