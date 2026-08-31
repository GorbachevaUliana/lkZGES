<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\DraftApplicationService;

class DraftController extends Controller
{
    public function destroy(DraftApplicationService $draftService)
    {
        $draft = $draftService->currentForUser(auth()->user());

        if ($draft) {
            $draft->delete();
        }

        return back()->with('success', 'Черновик удален');
    }
}