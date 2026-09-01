<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\DraftApplicationService;
use Illuminate\Http\Request;

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

    public function saveData( Request $request, DraftApplicationService $draftService)
    {
        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);

        $draft = $draftService->saveDataForUser(auth()->user(), $validated['data']);

        return response()->json(['saved' => (bool) $draft]);
    }
}