<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportsController as AdminReportsController;
use App\Http\Controllers\Admin\TaxonomyController as AdminTaxonomyController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Events\DashboardController as EventDashboardController;
use App\Http\Controllers\Events\IndexController as EventIndexController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Marketing\LegalController;
use App\Http\Controllers\Marketing\PageController as MarketingPageController;
use App\Http\Controllers\Marketing\PricingController as MarketingPricingController;
use App\Http\Controllers\Providers\BillingController as ProviderBillingController;
use App\Http\Controllers\Providers\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Providers\RoiDashboardController;
use App\Livewire\Admin\BillingActivation;
use App\Livewire\Admin\Taxonomy\Districts as AdminDistricts;
use App\Livewire\Admin\Taxonomy\EventTypes as AdminEventTypes;
use App\Livewire\Admin\Taxonomy\RequirementTemplates as AdminRequirementTemplates;
use App\Livewire\Admin\Taxonomy\ScopeQuestions as AdminScopeQuestions;
use App\Livewire\Admin\Taxonomy\ServiceCategories as AdminServiceCategories;
use App\Livewire\Admin\UserManagement as AdminUserManagement;
use App\Livewire\Admin\VerificationQueue;
use App\Livewire\Bookings\Workspace as BookingWorkspace;
use App\Livewire\Events\Wizard as EventWizard;
use App\Livewire\Providers\Availability as ProviderAvailability;
use App\Livewire\Providers\Media as ProviderMedia;
use App\Livewire\Providers\Onboarding as ProviderOnboarding;
use App\Livewire\Providers\Offers\Index as ProviderOfferIndex;
use App\Livewire\Providers\Offers\Submit as ProviderOfferSubmit;
use App\Livewire\Providers\Opportunities\Browse as ProviderOpportunityBrowse;
use App\Livewire\Providers\Opportunities\Invitations as ProviderInvitations;
use App\Livewire\Providers\RequestVerification;
use App\Livewire\Settings\Security as SecuritySettings;
use App\Livewire\Sourcing\OfferComparison;
use App\Livewire\Sourcing\OpportunityPanel;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketingPageController::class, 'home'])->name('marketing.home');
Route::get('/for-organisers', [MarketingPageController::class, 'organisers'])->name('marketing.organisers');
Route::get('/for-providers', [MarketingPageController::class, 'providers'])->name('marketing.providers');
Route::get('/pricing', MarketingPricingController::class)->name('marketing.pricing');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', HomeController::class)->name('home');

    Route::get('/settings/security', SecuritySettings::class)
        ->middleware('password.confirm')
        ->name('settings.security');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/verifications', VerificationQueue::class)->name('verifications.index');
        Route::get('/billing', BillingActivation::class)->name('billing.index');
        Route::get('/users', AdminUserManagement::class)->name('users.index');
        Route::get('/reports', AdminReportsController::class)->name('reports.index');

        Route::prefix('taxonomy')->name('taxonomy.')->group(function () {
            Route::get('/', AdminTaxonomyController::class)->name('index');
            Route::get('/event-types', AdminEventTypes::class)->name('event-types');
            Route::get('/service-categories', AdminServiceCategories::class)->name('service-categories');
            Route::get('/districts', AdminDistricts::class)->name('districts');
            Route::get('/event-types/{eventType}/scope-questions', AdminScopeQuestions::class)->name('scope-questions');
            Route::get('/event-types/{eventType}/requirement-templates', AdminRequirementTemplates::class)->name('requirement-templates');
        });
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
        Route::get('/roi', RoiDashboardController::class)->name('roi.index');
        Route::get('/billing', ProviderBillingController::class)->name('billing.index');
    });

    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', EventIndexController::class)->name('index');
        Route::get('/create', EventWizard::class)->name('create');
        Route::get('/{event}/wizard', EventWizard::class)->name('wizard');
        Route::get('/{event}', EventDashboardController::class)->name('dashboard');
    });

    Route::get('/bookings/{booking}', BookingWorkspace::class)->name('bookings.show');

    Route::get('/requirements/{requirement}/sourcing', OpportunityPanel::class)->name('sourcing.show');
    Route::get('/requirements/{requirement}/offers', OfferComparison::class)->name('sourcing.offers');
    Route::get('/requirements/{requirement}/offers/submit', ProviderOfferSubmit::class)->name('offers.submit');
});
