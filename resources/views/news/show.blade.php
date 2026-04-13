@extends('layouts.app')

@section('title', $news->title)
@section('page-title', 'Détails de l\'actualité')

@section('content')
<div class="news-detail">
    @if($news->image)
    <div class="news-flyer">
        <img src="{{ asset($news->image) }}" alt="{{ $news->title }}">
    </div>
    @endif
    
    <div class="card">
        <div class="card-header">
            <div class="card-title">📰 {{ $news->title }}</div>
            <div style="display:flex;gap:8px">
                <a href="{{ route('news.edit', $news) }}" class="btn btn-ghost">Modifier</a>
                <a href="{{ route('news.index') }}" class="btn btn-ghost">Retour</a>
            </div>
        </div>
        <div class="card-body">
            <div style="margin-bottom:16px">
                <span class="badge bg-{{ $news->type === 'holiday' ? 'success' : ($news->type === 'promotion' ? 'warning' : 'primary') }}">
                    {{ \App\Models\News::TYPES[$news->type] ?? $news->type }}
                </span>
                <span class="badge bg-{{ $news->is_active ? 'success' : 'secondary' }}">
                    {{ $news->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>

            <div style="margin-bottom:16px;color:var(--text-muted)">
                <strong>Date:</strong> {{ $news->event_date->format('d/m/Y') }}
            </div>

            <div style="margin-bottom:16px;color:var(--text-muted)">
                <strong>Date:</strong> {{ $news->event_date->format('d/m/Y') }}
            </div>

            @if($news->description)
            <div>
                <strong>Description:</strong>
                <p style="margin-top:8px;white-space: pre-wrap;">{{ $news->description }}</p>
            </div>
            @endif

            <form action="{{ route('news.destroy', $news) }}" method="POST" style="margin-top:24px">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité?')">
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.news-detail {
    max-width: 900px;
    margin: 0 auto;
}

.news-flyer {
    margin-bottom: 24px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}

.news-flyer img {
    width: 100%;
    height: auto;
    display: block;
}
</style>
@endsection
