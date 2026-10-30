<?php

/**
 * Move each case study's old WYSIWYG content into the Q&A repeater: every
 * heading becomes a question, the content under it the answer.
 *
 *   wp eval-file scripts/case-study-repeater/migrate-case-studies.php <mode> [ids]
 *
 * Modes:
 *   check    read-only (default): report what each case study would become
 *   migrate  write the rows; skips case studies that already have rows
 *   force    like migrate, but replaces existing rows
 *
 * The old post_content is left untouched, so it can be re-run or rolled back.
 * Case studies whose content doesn't start with a heading are reported and skipped.
 */

$mode = $args[0] ?? 'check';
$only = isset($args[1]) ? array_map('intval', explode(',', $args[1])) : null;

if (! in_array($mode, ['check', 'migrate', 'force'], true)) {
    WP_CLI::error("Unknown mode '{$mode}'. Use check, migrate or force.");
}

$posts = get_posts([
    'post_type' => 'case_study',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
    'post__in' => $only ?: [],
]);

/** "Why" -> "Why?", as in the design. */
function case_study_question(string $headingHtml): string
{
    $question = trim(html_entity_decode(wp_strip_all_tags($headingHtml), ENT_QUOTES));

    return strcasecmp($question, 'why') === 0 ? $question . '?' : $question;
}

/** @return array<int, array{question: string, answer: string}>|null null when content doesn't start with a heading */
function case_study_rows(string $content): ?array
{
    $content = trim(preg_replace('/<!--\s*\/?wp:.*?-->/s', '', $content));
    $parts = preg_split('/(<h[1-6][^>]*>.*?<\/h[1-6]>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    if (! $parts || ! preg_match('/^<h[1-6]/i', $parts[0])) {
        return null;
    }

    $rows = [];

    foreach ($parts as $part) {
        if (preg_match('/^<h[1-6]/i', $part)) {
            $rows[] = ['question' => case_study_question($part), 'answer' => ''];

            continue;
        }

        $rows[array_key_last($rows)]['answer'] .= $part;
    }

    foreach ($rows as &$row) {
        $row['answer'] = trim($row['answer']);
    }

    return $rows;
}

$counts = ['migrated' => 0, 'skipped' => 0, 'unmatched' => 0];

foreach ($posts as $post) {
    $rows = case_study_rows($post->post_content);
    $label = "#{$post->ID} {$post->post_title}";

    if ($rows === null) {
        WP_CLI::warning("{$label}: content doesn't start with a heading, left alone.");
        $counts['unmatched']++;

        continue;
    }

    if ((int) get_post_meta($post->ID, 'rows', true) > 0 && $mode !== 'force') {
        WP_CLI::log("{$label}: already has rows, skipped.");
        $counts['skipped']++;

        continue;
    }

    WP_CLI::log(sprintf('%s: %d rows%s', $label, count($rows), $mode === 'check' ? ' (would migrate)' : ''));

    if ($mode === 'check') {
        continue;
    }

    // Field key follows ACF Composer's naming (see app/Fields/CaseStudyQuestions.php).
    update_field('field_case_study_questions_rows', $rows, $post->ID);
    $counts['migrated']++;
}

WP_CLI::success(sprintf(
    '%d case studies: %d migrated, %d skipped, %d unmatched.%s',
    count($posts),
    $counts['migrated'],
    $counts['skipped'],
    $counts['unmatched'],
    $mode === 'check' ? ' Read-only run, nothing written.' : '',
));
