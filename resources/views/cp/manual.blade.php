@extends('statamic::layout')
@section('title', __('Saisie manuelle'))

@section('content')
    @include('form-import::cp.partials.styles')

    <div class="fi-wrap">
        <h1 class="fi-h1">Ajouter une soumission</h1>
        <p class="fi-sub">Formulaire « {{ $form->title() }} ».</p>

        @include('form-import::cp.partials.flash')

        <form method="POST" action="{{ cp_route('form-import.manual.store') }}">
            @csrf
            <input type="hidden" name="form" value="{{ $form->handle() }}">

            <div class="fi-card">
                @foreach ($fields as $field)
                    <div class="fi-row">
                        <label class="fi-label" for="f-{{ $field['handle'] }}">{{ $field['display'] }}</label>

                        @if ($field['type'] === 'toggle')
                            <label style="display: inline-flex; align-items: center; gap: .5rem;">
                                <input type="checkbox" id="f-{{ $field['handle'] }}" name="fields[{{ $field['handle'] }}]" value="1"
                                    @checked(old('fields.'.$field['handle']))> Oui
                            </label>
                        @elseif ($field['type'] === 'select')
                            <select id="f-{{ $field['handle'] }}" name="fields[{{ $field['handle'] }}]" class="fi-select">
                                <option value="">—</option>
                                @foreach ($field['options'] as $optValue => $optLabel)
                                    <option value="{{ $optValue }}" @selected(old('fields.'.$field['handle']) === (string) $optValue)>{{ $optLabel ?: $optValue }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="f-{{ $field['handle'] }}" name="fields[{{ $field['handle'] }}]" class="fi-input"
                                value="{{ old('fields.'.$field['handle']) }}">
                        @endif
                    </div>
                @endforeach

                <button type="submit" class="fi-btn">Enregistrer la soumission</button>
                <a href="{{ cp_route('form-import.index') }}" class="fi-btn fi-btn-light" style="margin-left: .5rem;">Annuler</a>
            </div>
        </form>
    </div>
@endsection
