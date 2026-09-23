<?php

namespace Foorintodev\FormImport\Http\Controllers;

use Foorintodev\FormImport\Support\ResolvesForms;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Statamic\Facades\Form;

class ExportController extends Controller
{
    use ResolvesForms;

    /**
     * Critères de dédoublonnage proposés. Chaque critère = un groupe de champs
     * qui doivent TOUS correspondre ; les champs cochés par défaut sont devinés
     * d'après leur handle (motifs ci-dessous), modifiables dans l'écran.
     */
    private const CRITERIA = [
        'email' => ['label' => 'Email', 'guess' => '/e-?mail/'],
        'name' => ['label' => 'Nom et prénom', 'guess' => '/^(prenom|nom|first_?name|last_?name|name)$/'],
        'address' => ['label' => 'Adresse complète', 'guess' => '/(rue|adresse|address|street|code_?postal|postal|zip|localite|ville|city|pays|country)/'],
    ];

    /** Écran d'options d'export pour un formulaire (?form=handle). */
    public function create(Request $request)
    {
        $form = Form::find($request->query('form'));
        abort_unless($form, 404);

        $fields = $this->formFields($form);

        $criteria = collect(self::CRITERIA)->map(fn ($c) => [
            'label' => $c['label'],
            'fields' => collect($fields)->pluck('handle')->filter(fn ($h) => preg_match($c['guess'], $h))->values()->all(),
        ])->all();

        return view('form-import::cp.export', [
            'form' => $form,
            'fields' => $fields,
            'criteria' => $criteria,
            'total' => $form->submissions()->count(),
            'back' => $this->backUrl($request),
        ]);
    }

    /**
     * Page précédente (en-tête Referer) pour le bouton « Retour » : page du
     * formulaire si on vient du bouton « Export dédoublonné », sinon l'outil.
     * Uniquement une URL du site, et jamais la page d'export elle-même.
     */
    private function backUrl(Request $request): string
    {
        $fallback = cp_route('form-import.index');
        $previous = (string) $request->headers->get('referer');

        if ($previous === ''
            || parse_url($previous, PHP_URL_HOST) !== $request->getHost()
            || Str::startsWith(strtok($previous, '?'), strtok(cp_route('form-import.export.create'), '?'))
        ) {
            return $fallback;
        }

        return $previous;
    }

    /** Génère le CSV, dédoublonné selon les critères cochés. */
    public function download(Request $request)
    {
        $request->validate([
            'form' => 'required|string',
            'criteria' => 'nullable|array',
            'keep' => 'nullable|in:newest,oldest',
        ]);

        $form = Form::find($request->input('form'));
        abort_unless($form, 404);

        $fields = collect($this->formFields($form));

        // Critères actifs : cochés ET avec au moins un champ sélectionné.
        $criteria = collect($request->input('criteria', []))
            ->filter(fn ($c) => ! empty($c['enabled']) && ! empty($c['fields']))
            ->map(fn ($c) => array_values(array_intersect((array) $c['fields'], $fields->pluck('handle')->all())))
            ->filter()
            ->values();

        $submissions = $form->submissions()->sortBy(fn ($s) => $s->date()->timestamp);
        if ($request->input('keep', 'newest') === 'newest') {
            $submissions = $submissions->reverse();
        }

        // On parcourt dans l'ordre de préférence : une soumission est écartée si
        // UN des critères correspond à une soumission déjà retenue.
        $seen = [];
        $kept = $submissions->filter(function ($submission) use ($criteria, &$seen) {
            $keys = $criteria->map(fn ($handles, $i) => $this->key($submission, $handles))->filter();

            foreach ($keys as $i => $key) {
                if (isset($seen[$i][$key])) {
                    return false;
                }
            }
            foreach ($keys as $i => $key) {
                $seen[$i][$key] = true;
            }

            return true;
        })->sortBy(fn ($s) => $s->date()->timestamp)->values();

        $filename = Str::slug($form->handle()).'-'.now()->format('Y-m-d').($criteria->isNotEmpty() ? '-dedoublonne' : '').'.csv';

        return response()->streamDownload(function () use ($kept, $fields) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM : accents corrects dans Excel

            fputcsv($out, array_merge(['Date'], $fields->pluck('display')->all()), ';');

            foreach ($kept as $submission) {
                $row = [$submission->date()->format('d/m/Y H:i')];
                foreach ($fields as $field) {
                    $row[] = $this->display($submission->get($field['handle']), $field['type']);
                }
                fputcsv($out, $row, ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Clé de comparaison d'une soumission pour un critère. Normalisation :
     * email → minuscules ; autres champs → sans accents, casse, espaces ni
     * ponctuation (« 12, Rue de l'Église » = « 12 rue de l eglise »).
     * Null si tous les champs sont vides (une soumission vide ne « matche » rien).
     */
    private function key($submission, array $handles): ?string
    {
        $parts = [];
        foreach ($handles as $handle) {
            $value = trim((string) $this->display($submission->get($handle), null));

            $parts[] = preg_match('/e-?mail/', $handle)
                ? mb_strtolower($value)
                : preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($value)));
        }

        return implode('', $parts) === '' ? null : implode('|', $parts);
    }

    private function display($value, ?string $type): string
    {
        if ($type === 'toggle' || is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }
        if (is_array($value)) {
            return implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $value));
        }

        return (string) $value;
    }
}
