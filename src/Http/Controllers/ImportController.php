<?php

namespace Foorintodev\FormImport\Http\Controllers;

use Foorintodev\FormImport\Support\Csv;
use Foorintodev\FormImport\Support\ResolvesForms;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Statamic\Facades\Form;

class ImportController extends Controller
{
    use ResolvesForms;

    public function index()
    {
        return view('form-import::cp.index', [
            'forms' => $this->allForms(),
        ]);
    }

    /** Étape 1 : réception du CSV → écran de mapping. */
    public function upload(Request $request)
    {
        $request->validate([
            'form' => 'required|string',
            'csv' => 'required|file',
        ]);

        $form = Form::find($request->input('form'));
        abort_unless($form, 404);

        $dir = storage_path('app/form-import');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $token = Str::uuid()->toString();
        $request->file('csv')->move($dir, $token.'.csv');
        $path = $dir.'/'.$token.'.csv';

        $csv = Csv::read($path);

        if (empty($csv['headers'])) {
            @unlink($path);

            return back()->with('error', 'CSV illisible ou vide.');
        }

        return view('form-import::cp.mapping', [
            'form' => $form,
            'fields' => $this->formFields($form),
            'headers' => $csv['headers'],
            'rowCount' => count($csv['rows']),
            'token' => $token,
        ]);
    }

    /** Étape 2 : création d'une soumission par ligne selon le mapping. */
    public function import(Request $request)
    {
        $request->validate([
            'form' => 'required|string',
            'token' => 'required|string',
            'mapping' => 'nullable|array',
            'fixed' => 'nullable|array',
        ]);

        $form = Form::find($request->input('form'));
        abort_unless($form, 404);

        $path = storage_path('app/form-import/'.basename($request->input('token')).'.csv');
        abort_unless(is_file($path), 404, 'Fichier CSV introuvable — ré-uploadez le fichier.');

        $csv = Csv::read($path);

        $mapping = $request->input('mapping', []);
        $fixed = $request->input('fixed', []);
        $types = collect($this->formFields($form))->mapWithKeys(fn ($f) => [$f['handle'] => $f['type']]);

        // Champs à remplir : ceux qui ont une valeur fixe OU une colonne associée.
        $activeFields = $types->keys()->filter(fn ($handle) => $this->hasFixed($fixed, $handle) || $this->hasColumn($mapping, $handle))->values();

        if ($activeFields->isEmpty()) {
            @unlink($path);

            return redirect()->route('form-import.index')
                ->with('error', 'Aucun champ associé : recommencez en associant au moins un champ (colonne ou valeur fixe).');
        }

        $imported = 0;
        foreach ($csv['rows'] as $row) {
            $data = [];
            foreach ($activeFields as $handle) {
                // La valeur fixe prime ; sinon on prend la valeur de la colonne.
                $raw = $this->hasFixed($fixed, $handle)
                    ? $fixed[$handle]
                    : ($row[$mapping[$handle]] ?? '');

                $data[$handle] = $this->cast($types->get($handle), (string) $raw);
            }

            if (empty(array_filter($data, fn ($v) => $v !== '' && $v !== false && $v !== null))) {
                continue; // ligne entièrement vide
            }

            $submission = $form->makeSubmission();
            $submission->data($data);
            $submission->save();
            $imported++;
        }

        @unlink($path);

        return redirect()->route('form-import.index')
            ->with('success', "{$imported} soumission(s) importée(s) dans « {$form->title()} ».");
    }

    private function hasFixed(array $fixed, string $handle): bool
    {
        return isset($fixed[$handle]) && $fixed[$handle] !== '';
    }

    private function hasColumn(array $mapping, string $handle): bool
    {
        return isset($mapping[$handle]) && $mapping[$handle] !== '';
    }

    /** Conversion d'une valeur CSV vers le type du champ. */
    private function cast(?string $type, string $value)
    {
        if ($type === 'toggle') {
            return in_array(mb_strtolower(trim($value)), ['1', 'true', 'oui', 'yes', 'x', 'vrai'], true);
        }

        return $value;
    }
}
