<?php

namespace App\Http\Controllers\Marketing;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', [
            'eventTypes' => EventType::orderBy('sort_order')->get(),
            'categories' => ServiceCategory::whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
        ]);
    }

    public function organisers(): View
    {
        return view('marketing.for-organisers', [
            'eventTypes' => EventType::orderBy('sort_order')->get(),
        ]);
    }

    public function providers(): View
    {
        return view('marketing.for-providers', [
            'categories' => ServiceCategory::whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
        ]);
    }
}
