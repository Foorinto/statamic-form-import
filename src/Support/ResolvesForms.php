<?php

namespace Foorintodev\FormImport\Support;

use Statamic\Facades\Form;

trait ResolvesForms
{
    /** Liste des formulaires : [['handle' => ..., 'title' => ...], ...]. */
    protected function allForms(): array
    {
        return Form::all()
            ->map(fn ($form) => ['handle' => $form->handle(), 'title' => $form->title()])
            ->sortBy('title')
            ->values()
            ->all();
    }

    /** Champs du blueprint d'un formulaire : [['handle', 'display', 'type', 'options'], ...]. */
    protected function formFields($form): array
    {
        return $form->blueprint()->fields()->all()
            ->map(fn ($field) => [
                'handle' => $field->handle(),
                'display' => $field->display() ?: $field->handle(),
                'type' => $field->type(),
                'options' => (array) ($field->config()['options'] ?? []),
            ])
            ->values()
            ->all();
    }
}
