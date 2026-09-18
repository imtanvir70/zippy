<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PartialController extends Controller
{
    protected array $allowedPartials = [
        'cart-drawer',
        'bottom-sheet',
        'quick-view-modal',
        'chatbot',
        'recent-sales-popup',
    ];

    public function show(string $component): Response
    {
        if (!in_array($component, $this->allowedPartials, true)) {
            abort(404);
        }

        $viewName = 'frontend.inc.' . $component;
        if (!view()->exists($viewName)) {
            abort(404);
        }

        return response(view($viewName)->render(), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}
