@extends('statamic::layout')
@section('title', __('Mapping des colonnes'))

@section('content')
    <div class="fi-wrap">
        <h1 class="fi-h1">Associer les colonnes</h1>
        <p class="fi-sub">
            {{ $rowCount }} ligne(s) détectée(s) → formulaire « {{ $form->title() }} ».
            Associez chaque champ à une colonne du CSV, et/ou saisissez une <strong>valeur fixe</strong>
            (appliquée à toutes les lignes ; elle prime sur la colonne). Laissez vide pour ignorer.
        </p>

        @include('form-import::cp.partials.flash')

        <form method="POST" action="{{ cp_route('form-import.import') }}">
            @csrf
            <input type="hidden" name="form" value="{{ $form->handle() }}">
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="fi-card">
                <table class="fi-table">
                    <thead>
                        <tr><th>Champ du formulaire</th><th>Colonne du CSV</th><th>Valeur fixe (optionnel)</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($fields as $field)
                            <tr>
                                <td>
                                    <strong>{{ $field['display'] }}</strong>
                                    <span class="fi-muted">({{ $field['handle'] }})</span>
                                </td>
                                <td>
                                    <select name="mapping[{{ $field['handle'] }}]" class="fi-select">
                                        <option value="">— ignorer —</option>
                                        @foreach ($headers as $header)
                                            <option value="{{ $header }}" @selected(\Illuminate\Support\Str::slug($header) === \Illuminate\Support\Str::slug($field['handle']) || \Illuminate\Support\Str::slug($header) === \Illuminate\Support\Str::slug($field['display']))>{{ $header }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="fixed[{{ $field['handle'] }}]" class="fi-input" placeholder="—">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="fi-btn">Importer {{ $rowCount }} ligne(s)</button>
            <a href="{{ cp_route('form-import.index') }}" class="fi-btn fi-btn-light fi-ml">Annuler</a>
        </form>
    </div>
@endsection
