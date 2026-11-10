<?php
/**
 * Galerie aufräumen, wenn Bilder oder Videos in der Mediathek gelöscht werden
 *
 * Das Feld «Bildergalerie» (gallery) speichert nur die IDs der Dateien. Wird eine Datei in der
 * Mediathek gelöscht, bliebe ihre ID im Projekt stehen, und Carbon Fields zeigt eine leere Kachel.
 * 1. Beim Löschen einer Datei: ID aus allen Projekt-Galerien entfernen.
 * 2. Beim Speichern eines Projekts: IDs entfernen, zu denen es keine Datei mehr gibt
 *    (räumt auch alte Lücken auf, die vor dieser Funktion entstanden sind).
 */

// -> Nur IDs behalten, zu denen es noch eine Datei in der Mediathek gibt
function filter_existing_attachment_ids(array $ids, int $exclude_id = 0): array
{
    return array_values(array_filter(array_map('intval', $ids), function (int $id) use ($exclude_id): bool {
        return $id > 0 && $id !== $exclude_id && get_post_type($id) === 'attachment';
    }));
}

// -> Galerie eines Projekts nur speichern, wenn sich etwas geändert hat
function cleanup_project_gallery(int $project_id, int $exclude_id = 0): void
{
    $ids = array_map('intval', carbon_get_post_meta($project_id, 'gallery') ?: []);
    $clean = filter_existing_attachment_ids($ids, $exclude_id);
    if ($clean !== $ids) {
        carbon_set_post_meta($project_id, 'gallery', $clean);
    }
}

// -> 1. Datei wird gelöscht (läuft kurz vor dem Löschen, die Datei existiert also noch)
add_action('delete_attachment', function (int $attachment_id): void {
    $projects = get_posts([
        'post_type' => 'project',
        'post_status' => 'any',
        'numberposts' => -1,
        'fields' => 'ids',
    ]);
    foreach ($projects as $project_id) {
        cleanup_project_gallery((int) $project_id, $attachment_id);
    }
});

// -> 2. Projekt wird gespeichert (Hook von Carbon Fields, läuft nach dem Speichern der Felder)
add_action('carbon_fields_post_meta_container_saved', function (int $post_id): void {
    if (get_post_type($post_id) === 'project') {
        cleanup_project_gallery($post_id);
    }
});
