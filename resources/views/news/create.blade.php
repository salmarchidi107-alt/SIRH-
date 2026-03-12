@extends('layouts.app')

@section('title', 'Créer une actualité')
@section('page-title', 'Créer une actualité')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">📰 Nouvelle actualité</div>
    </div>
    <div class="card-body">
        <form action="{{ route('news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="title">Titre</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="image">Image / Flyer</label>
                        <div class="image-upload-area" onclick="document.getElementById('image').click()">
                            <div id="image-preview-container" style="display:none;">
                                <img id="image-preview" src="" alt="Preview" style="max-width:100%;max-height:200px;border-radius:8px;">
                            </div>
                            <div id="image-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <p>Cliquez pour uploader une image</p>
                                <span class="text-muted">PNG, JPG, GIF (max 2MB)</span>
                            </div>
                        </div>
                        <input type="file" name="image" id="image" class="form-control d-none" accept="image/*" onchange="previewImage(event)">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select name="type" id="type" class="form-control" required>
                            @foreach(\App\Models\News::TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="event_date">Date</label>
                        <input type="date" name="event_date" id="event_date" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" checked>
                    Actif
                </label>
            </div>

            <div style="display:flex;gap:12px;margin-top:24px">
                <button type="submit" class="btn btn-primary">Créer</button>
                <a href="{{ route('news.index') }}" class="btn btn-ghost">Annuler</a>
            </div>
        </form>
    </div>
</div>

<style>
.image-upload-area {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8fafc;
}
.image-upload-area:hover {
    border-color: #6366f1;
    background: #eef2ff;
}
.image-upload-area svg {
    color: #94a3b8;
    margin-bottom: 8px;
}
.image-upload-area p {
    margin: 0;
    color: #475569;
    font-weight: 500;
}
</style>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
            document.getElementById('image-preview-container').style.display = 'block';
            document.getElementById('image-placeholder').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection

