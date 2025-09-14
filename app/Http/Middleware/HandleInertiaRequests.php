<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Setting;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                  ? [
                      'id' => auth()->id(),
                      'name' => auth()->user()->name,
                      'role' => auth()->user()->role,
                  ]
                  : null,
            ],
            'store' => fn () => Setting::first() ? [
                'name' => Setting::first()->store_name,
                'address' => Setting::first()->store_address,
                'contact' => Setting::first()->store_contact,
                'logo' => Setting::first()->store_logo ? asset('storage/' . Setting::first()->store_logo) : null,
            ] : null,
        ];
    }
}
