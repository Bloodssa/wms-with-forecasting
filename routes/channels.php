<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('inquiry.{id}', function ($user, $id) {
    $inquiry = \App\Models\WarrantyInquiries::find($id);

    if (!$inquiry) return false;

    // admin or tech
    $roleValue = $user->role instanceof \App\Enum\UserRole 
        ? $user->role->value 
        : $user->role;

    return (int) $user->id === (int) $inquiry->user_id 
           || in_array($roleValue, ['admin', 'technician']);
});
