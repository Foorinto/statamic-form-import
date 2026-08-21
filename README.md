# Foorintodev Form Import (addon Statamic)

Importe des soumissions dans **n'importe quel formulaire Statamic** depuis un
**CSV** (avec mapping des colonnes), et permet la **saisie manuelle** d'une
soumission — le tout depuis une page dédiée du Control Panel. Pratique pour
reprendre une liste existante ou ajouter une inscription à la main, sans CLI.

- **Aucun email envoyé** à l'import : les soumissions sont créées directement
  (via l'API `Form::makeSubmission()`), sans déclencher les notifications du
  formulaire.
- **CSV tolérant** : séparateur `,` / `;` / tabulation détecté automatiquement,
  BOM UTF-8 géré, accents convertis (repli Windows-1252), champs entre
  guillemets pris en charge.
- **Générique** : fonctionne avec le blueprint de n'importe quel formulaire.

## Installation

```bash
composer config repositories.foorintodev-form-import vcs https://github.com/Foorinto/statamic-form-import.git
composer config github-oauth.github.com <PAT>          # si le repo est privé
composer require foorintodev/statamic-form-import:^1.0
```

Rien d'autre : un menu **« Import formulaire »** apparaît dans la section
**Tools** du Control Panel.

## Utilisation

**Import CSV**
1. CP → **Tools → Import formulaire**.
2. Choisis le formulaire de destination, uploade le CSV (1re ligne = noms de
   colonnes).
3. Associe chaque champ du formulaire à une colonne du CSV (pré-rempli quand les
   noms correspondent). Les champs laissés sur « ignorer » ne sont pas remplis.
4. **Importer** → une soumission est créée par ligne. Doublons **non** filtrés
   (tout est importé).

**Saisie manuelle**
1. CP → **Tools → Import formulaire** → *Ajouter une soumission manuellement*.
2. Choisis le formulaire, remplis les champs, **Enregistrer**.

## Notes

- L'import crée les soumissions telles quelles ; aucune validation du blueprint
  n'est appliquée (c'est un outil d'administration).
- Les valeurs des champs `toggle` sont interprétées : `1/true/oui/yes/x/vrai`
  → activé, sinon désactivé.

## Stack

Statamic 6 · PHP 8.2+ · aucune dépendance externe.
