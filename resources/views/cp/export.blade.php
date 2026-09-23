@extends('statamic::layout')
@section('title', __('Export formulaire'))

@section('content')
    <div class="fi-wrap">
        <h1 class="fi-h1">Exporter « {{ $form->title() }} »</h1>
        <p class="fi-sub">
            {{ $total }} soumission(s). Cochez les critères de dédoublonnage : une soumission est écartée
            dès qu'<strong>un</strong> critère coché correspond à une autre (tous les champs du critère doivent être identiques,
            sans tenir compte des majuscules, accents, espaces et ponctuation).
        </p>

        @include('form-import::cp.partials.flash')

        <form method="POST" action="{{ cp_route('form-import.export.download') }}">
            @csrf
            <input type="hidden" name="form" value="{{ $form->handle() }}">

            <div class="fi-card">
                <h2>Dédoublonner sur</h2>
                @foreach ($criteria as $key => $criterion)
                    <div class="fi-criterion">
                        <label class="fi-check">
                            <input type="checkbox" name="criteria[{{ $key }}][enabled]" value="1" @checked(! empty($criterion['fields']))>
                            <strong>{{ $criterion['label'] }}</strong>
                        </label>
                        <div class="fi-fields">
                            @foreach ($fields as $field)
                                <label class="fi-check fi-chip">
                                    <input type="checkbox" name="criteria[{{ $key }}][fields][]" value="{{ $field['handle'] }}" @checked(in_array($field['handle'], $criterion['fields'], true))>
                                    {{ $field['display'] }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <p class="fi-muted fi-mt-sm">Aucun critère coché = export complet, sans dédoublonnage.</p>
            </div>

            <div class="fi-card">
                <h2>En cas de doublon, garder</h2>
                <label class="fi-check"><input type="radio" name="keep" value="newest" checked> la soumission la plus récente</label><br>
                <label class="fi-check fi-mt-sm"><input type="radio" name="keep" value="oldest"> la soumission la plus ancienne</label>
            </div>

            <button type="submit" class="fi-btn">Télécharger le CSV</button>
            <a href="{{ $back }}" class="fi-btn fi-btn-light fi-ml">Retour</a>
        </form>
    </div>
@endsection
