<?php

namespace Foorintodev\FormImport\Http\Controllers;

use Foorintodev\FormImport\Support\ResolvesForms;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Facades\Form;

class ManualController extends Controller
{
    use ResolvesForms;

    /** Formulaire de saisie manuelle pour un formulaire donné (?form=handle). */
    public function create(Request $request)
    {
        $form = Form::find($request->query('form'));
        abort_unless($form, 404);

        return view('form-import::cp.manual', [
            'form' => $form,
            'fields' => $this->formFields($form),
        ]);
    }

    /** Crée une soumission unique à partir des champs saisis. */
    public function store(Request $request)
    {
        $form = Form::find($request->input('form'));
        abort_unless($form, 404);

        $data = [];
        foreach ($this->formFields($form) as $field) {
            $handle = $field['handle'];

            if ($field['type'] === 'toggle') {
                $data[$handle] = $request->boolean('fields.'.$handle);

                continue;
            }

            $value = $request->input('fields.'.$handle);
            if ($value !== null && $value !== '') {
                $data[$handle] = $value;
            }
        }

        $submission = $form->makeSubmission();
        $submission->data($data);
        $submission->save();

        return redirect(cp_route('form-import.index'))
            ->with('success', "Soumission ajoutée à « {$form->title()} ».");
    }
}
