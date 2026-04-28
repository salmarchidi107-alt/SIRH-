@extends('layouts.app')

@section('content')

<div class="container">
    <h2 class="mb-4">Gestion des salles</h2>

    <!-- FORMULAIRE -->
    <form action="{{ route('rooms.store') }}" method="POST" class="mb-4">
        @csrf

        <div class="row">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Nom de la salle" required>
            </div>
            <br>
            <div class="col-md-4">
                <select name="department_id" class="form-control" required>
                    <option value="">Choisir service</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <br>
            <div class="col-md-2">
                <button class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </form>

    <!-- TABLE -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Salle</th>
                <th>Service</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rooms as $room)
            <tr>
                <td>{{ $room->id }}</td>
                <td>{{ $room->name }}</td>
                <td>{{ $room->department->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection