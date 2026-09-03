<?php

use App\Models\Idir;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('idir.{idirId}', function (User $user, int $idirId) {
    $idir = Idir::find($idirId);
    if (!$idir) {
        return false;
    }

    return $user->canAccessTenant($idir);
});
