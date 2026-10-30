# Case study repeater migration

Case studies used to keep their Q&A in the classic editor (an H3 per question,
paragraphs as the answer). They now use a "Questions and answers" repeater
(`rows`: `question`, `answer`). `migrate-case-studies.php` converts the old
content into rows.

Run from the Bedrock root (`/site` locally, `/srv/www/<site>/current` on a server):

```bash
wp db export /tmp/before-case-study-repeater.sql                          # back up first
wp eval-file scripts/case-study-repeater/migrate-case-studies.php check    # read-only report
wp eval-file scripts/case-study-repeater/migrate-case-studies.php migrate  # write rows
wp eval-file scripts/case-study-repeater/migrate-case-studies.php migrate 1019,1020  # some only
wp eval-file scripts/case-study-repeater/migrate-case-studies.php force    # replace existing rows
```

- Each heading becomes a question; "Why" becomes "Why?" to match the design. The content under it becomes the answer.
- Case studies already holding rows are skipped (use `force` to replace them).
- Content that doesn't start with a heading is reported and left alone. Until it's re-entered by hand, the page shows the old content as plain text.
- `post_content` is never changed, so the run is safe to repeat. The editor is hidden, so the old content is no longer visible in the admin.
- Needs this branch deployed first, so the field group exists. Clear the ACF Composer cache if the field doesn't show: `wp acorn acf:clear`.
