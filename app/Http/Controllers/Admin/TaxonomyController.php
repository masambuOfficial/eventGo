<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Models\EventType;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class TaxonomyController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.taxonomy.index', [
            'eventTypes' => EventType::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}
