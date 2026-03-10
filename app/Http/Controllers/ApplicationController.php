<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApplicationTemplate;
use Inertia\Inertia;
use App\Models\Application;

class ApplicationController extends Controller
{
    public function show($slug)
    {
        $template = ApplicationTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Applications/DynamicForm', [
            'template' => [
                'id' => $template->id,
                'title' => $template->title,
                'slug' => $template->slug,
                'content' => $template->content,
            ]
        ]);
    }

    public function store(Request $request, $slug)
    {
        $template = ApplicationTemplate::where('slug', $slug)->firstOrFail();
        $data = $request->all();

        foreach ($request->allFiles() as $key => $file) {
            $data[$key] = $file->store('applications', 'public');
        }

        Application::create([
            'user_id' => auth()->id(),
            'template_id' => $template->id,
            'data' => $data,
            'status' => 'new',
        ]);

        return redirect()->route('client.dashboard')->with('message', 'Заявка успешно отправлена!');
    }
}
