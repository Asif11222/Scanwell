<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Campaign;
use App\Models\HealthRule;
use App\Models\AppContent;
use App\Models\User;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['groups' => []]);
        }

        $groups = [];

        // Products
        $products = Product::where('name', 'like', "%{$q}%")
            ->orWhere('brand', 'like', "%{$q}%")
            ->orWhere('barcode', 'like', "%{$q}%")
            ->take(4)
            ->get()
            ->map(fn ($p) => [
                'title' => $p->name,
                'sub' => "{$p->brand} · {$p->barcode}",
                'url' => route('admin.products.index', ['q' => $p->barcode]),
                'icon' => 'package',
            ]);

        if ($products->isNotEmpty()) {
            $groups[] = ['label' => 'Products', 'items' => $products];
        }

        // Campaigns
        $campaigns = Campaign::where('name', 'like', "%{$q}%")
            ->orWhere('headline', 'like', "%{$q}%")
            ->take(3)
            ->get()
            ->map(fn ($c) => [
                'title' => $c->name,
                'sub' => "{$c->placement} · {$c->status}",
                'url' => route('admin.ads.index'),
                'icon' => 'megaphone',
            ]);

        if ($campaigns->isNotEmpty()) {
            $groups[] = ['label' => 'Campaigns', 'items' => $campaigns];
        }

        // Health rules
        $rules = HealthRule::where('name', 'like', "%{$q}%")
            ->orWhere('target', 'like', "%{$q}%")
            ->orWhere('concern', 'like', "%{$q}%")
            ->take(3)
            ->get()
            ->map(fn ($r) => [
                'title' => $r->name,
                'sub' => "{$r->target} · {$r->status}",
                'url' => route('admin.health.rules'),
                'icon' => 'pulse',
            ]);

        if ($rules->isNotEmpty()) {
            $groups[] = ['label' => 'Health rules', 'items' => $rules];
        }

        // App content
        $content = AppContent::where('content_key', 'like', "%{$q}%")
            ->orWhere('title', 'like', "%{$q}%")
            ->take(3)
            ->get()
            ->map(fn ($c) => [
                'title' => $c->title,
                'sub' => $c->content_key,
                'url' => route('admin.content.index'),
                'icon' => 'file',
            ]);

        if ($content->isNotEmpty()) {
            $groups[] = ['label' => 'App content', 'items' => $content];
        }

        // Users
        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->take(3)
            ->get()
            ->map(fn ($u) => [
                'title' => $u->name,
                'sub' => "{$u->role} · {$u->email}",
                'url' => route('admin.users.index'),
                'icon' => 'users',
            ]);

        if ($users->isNotEmpty()) {
            $groups[] = ['label' => 'Users', 'items' => $users];
        }

        return response()->json(['groups' => $groups]);
    }
}
