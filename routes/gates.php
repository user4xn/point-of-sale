<?php

use Illuminate\Support\Facades\Gate;
use App\Models\User;

Gate::define('admin-only', function (User $user) {
    return $user->role === 'admin';
});

