@extends('statamic::layout')
@section('title', __('Import formulaire'))

@section('content')
    @include('form-import::cp.partials.styles')

    <div class="fi-wrap">
        <h1 class="fi-h1">Import de soumissions</h1>
        <p class="fi-sub">Importer un CSV dans un formulaire, ou ajouter une soumission à la main.</p>

        @include('form-import::cp.partials.flash')

        @if (count($forms) === 0)
            <div class="fi-card"><p class="fi-muted">Aucun formulaire trouvé.</p></div>
        @else
            <div class="fi-card">
                <h2>Importer un CSV</h2>
                <form method="POST" action="{{ cp_route('form-import.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="fi-row">
                        <label class="fi-label">Formulaire de destination</label>
                        <select name="form" class="fi-select" required>
                            @foreach ($forms as $f)
                                <option value="{{ $f['handle'] }}">{{ $f['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fi-row">
                        <label class="fi-label">Fichier CSV</label>
                        <input type="file" name="csv" accept=".csv,text/csv" class="fi-input" required>
                        <p class="fi-muted" style="margin-top: .4rem;">
                            La 1<sup>re</sup> ligne doit contenir les noms de colonnes. Séparateur « , » ou « ; » détecté automatiquement.
                        </p>
                    </div>
                    <button type="submit" class="fi-btn">Continuer → mapping</button>
                </form>
            </div>

            <div class="fi-card">
                <h2>Ajouter une soumission manuellement</h2>
                <form method="GET" action="{{ cp_route('form-import.manual.create') }}">
                    <div class="fi-row">
                        <label class="fi-label">Formulaire</label>
                        <select name="form" class="fi-select" required>
                            @foreach ($forms as $f)
                                <option value="{{ $f['handle'] }}">{{ $f['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="fi-btn fi-btn-light">Saisir une entrée →</button>
                </form>
            </div>
        @endif
    </div>
@endsection
