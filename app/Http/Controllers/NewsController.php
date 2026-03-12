<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NewsController extends Controller
{
    public function index()
    {
        $holidays = [];
        try {
            $response = Http::get('https://calendar-api.ma/api/holidays?year=' . date('Y'));
            if ($response->successful()) {
                $holidaysData = $response->json();
                if (isset($holidaysData['holidays'])) {
                    $holidays = $holidaysData['holidays'];
                } elseif (is_array($holidaysData)) {
                    $holidays = $holidaysData;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Calendar API Error: ' . $e->getMessage());
        }

        // Fallback: Default Morocco public holidays if API fails
        if (empty($holidays)) {
            $currentYear = date('Y');
            $holidays = [
                ['name' => 'Nouvel An', 'date' => $currentYear . '-01-01'],
                ['name' => 'Manifeste de l\'Indépendance', 'date' => $currentYear . '-01-11'],
                ['name' => 'Fête du Travail', 'date' => $currentYear . '-05-01'],
                ['name' => 'Fête de la Throne', 'date' => $currentYear . '-07-30'],
                ['name' => 'Fête de la Révolution', 'date' => $currentYear . '-08-14'],
                ['name' => 'Fête de la Jeunesse', 'date' => $currentYear . '-08-21'],
                ['name' => 'Mort du Roi Hassan II', 'date' => $currentYear . '-07-30'],
                ['name' => 'Anniversaire du Roi', 'date' => $currentYear . '-08-21'],
                ['name' => 'Aïd al-Fitr', 'date' => ''],
                ['name' => 'Aïd al-Adha', 'date' => ''],
                ['name' => 'Nouvel An Hégirien', 'date' => ''],
                ['name' => 'Fête de l\'Indépendance', 'date' => $currentYear . '-11-18'],
            ];
        }

        $news = News::orderBy('event_date', 'desc')->paginate(10);
        return view('news.index', compact('news', 'holidays'));
    }

    public function create()
    {
        return view('news.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required',
            'event_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
            $image->move(public_path('images/news'), $imageName);
            $data['image'] = 'images/news/' . $imageName;
        }

        News::create($data);
        return redirect()->route('news.index')->with('success', 'Actualité créée avec succès');
    }

    public function show(News $news)
    {
        return view('news.show', compact('news'));
    }

    public function edit(News $news)
    {
        return view('news.edit', compact('news'));
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required',
            'event_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        
        if ($request->hasFile('image')) {
            if ($news->image && file_exists(public_path($news->image))) {
                unlink(public_path($news->image));
            }
            $image = $request->file('image');
            $imageName = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
            $image->move(public_path('images/news'), $imageName);
            $data['image'] = 'images/news/' . $imageName;
        }

        $news->update($data);
        return redirect()->route('news.index')->with('success', 'Actualité mise à jour avec succès');
    }

    public function destroy(News $news)
    {
        $news->delete();
        return redirect()->route('news.index')->with('success', 'Actualité supprimée avec succès');
    }
}
