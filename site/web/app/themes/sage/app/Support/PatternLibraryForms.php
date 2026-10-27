<?php

namespace App\Support;

/**
 * Every Gravity Forms field type, rendered by GF itself from in-memory field
 * objects (nothing saved to the database), for the pattern library's dev-only
 * "Form fields" entry. Same markup and theme filters (app/filters.php) as a
 * real form, so it shows the fields as they currently look — including ones
 * no live form uses yet. Missing: GF's per-form JS (character counters,
 * datepicker popups) and the inline colour variables a real form prints.
 */
class PatternLibraryForms
{
    /**
     * Fake form IDs: one per variant so input IDs (and label `for`s) stay unique on the page.
     */
    protected const DEFAULT_ID = 9001;

    protected const ERRORS_ID = 9002;

    public static function available(): bool
    {
        return class_exists('GFFormDisplay') && class_exists('GF_Fields');
    }

    /**
     * @return array{default: string, errors: string}
     */
    public static function render(): array
    {
        $default = self::form(self::DEFAULT_ID, false);
        $errors = self::form(self::ERRORS_ID, true);

        // Registers GF's theme stylesheets; runs before wp_head since Blade renders sections before the layout.
        \GFFormDisplay::enqueue_form_scripts($default);

        // GF reads some form settings (e.g. "(Required)" vs "*") from the saved form by ID; with no
        // saved form it assumes legacy markup. These fake forms are modern-markup, like the live ones.
        add_filter('gform_enable_legacy_markup', '__return_false');

        $html = [
            'default' => self::markup($default, false),
            'errors' => self::markup($errors, true),
        ];

        remove_filter('gform_enable_legacy_markup', '__return_false');

        return $html;
    }

    protected static function form(int $formId, bool $failed): array
    {
        $fields = array_map(function (array $props) use ($formId, $failed) {
            $field = \GF_Fields::create($props + ['formId' => $formId]);

            if ($failed && $props['type'] !== 'section') {
                $field->failed_validation = true;
                $field->validation_message = $props['errorMessage'] ?? __('This field is required.', 'sage');
            }

            return $field;
        }, self::fields());

        return [
            'id' => $formId,
            'title' => 'Pattern library',
            'markupVersion' => 2,
            'labelPlacement' => 'top_label',
            'descriptionPlacement' => 'below',
            'subLabelPlacement' => 'above',
            'validationSummary' => true,
            'fields' => $fields,
        ];
    }

    /**
     * The wrapper/body classes a real Orbital-themed form renders with.
     */
    protected static function markup(array $form, bool $failed): string
    {
        $fieldsHtml = implode('', array_map(
            fn($field) => \GFFormDisplay::get_field($field, '', false, $form),
            $form['fields'],
        ));

        $summary = $failed ? \GFFormDisplay::get_validation_errors_markup($form, [], true) : '';

        if (! $failed) {
            $fieldsHtml = self::withSampleUploads($fieldsHtml, $form);
        }

        return sprintf(
            '<div class="gform_wrapper gform-theme gform-theme--foundation gform-theme--framework gform-theme--orbital%s" data-form-theme="orbital" data-form-index="0" id="gform_wrapper_%d">'
            . '<form method="post" id="gform_%d" onsubmit="return false" novalidate>%s'
            . '<div class="gform-body gform_body"><div id="gform_fields_%2$d" class="gform_fields top_label form_sublabel_above description_below validation_below">%s</div></div>'
            . '</form></div>',
            $failed ? ' gform_validation_error' : '',
            $form['id'],
            $form['id'],
            $summary,
            $fieldsHtml,
        );
    }

    /**
     * The multi-file list is filled by GF's JS, which doesn't run here: one finished file
     * (GF's own server-side row) and one mid-upload (the row its JS builds, gravityforms.js).
     */
    protected static function withSampleUploads(string $html, array $form): string
    {
        $field = collect($form['fields'])->first(fn($field) => $field->type === 'fileupload' && $field->multipleFiles);

        if (! $field) {
            return $html;
        }

        $uploaded = $field->get_file_preview_markup(['id' => 'sample-uploaded', 'uploaded_filename' => 'bike_certificate.jpeg'], $form);
        $uploading = '<div id="sample-uploading" class="ginput_preview">'
            . '<span class="gfield_fileupload_filename">route_map.pdf</span>'
            . '<span class="gfield_fileupload_filesize">2.4 MB</span>'
            . '<span class="gfield_fileupload_progress"><span class="gfield_fileupload_progressbar"><span class="gfield_fileupload_progressbar_progress" style="width: 60%;"></span></span><span class="gfield_fileupload_percent">60%</span></span>'
            . '<a class="gfield_fileupload_cancel gform-theme-button gform-theme-button--simple" href="javascript:void(0)" title="Cancel this upload">Cancel</a>'
            . '</div>';

        $list = "<div id='gform_preview_{$form['id']}_{$field->id}' class='ginput_preview_list'>";

        return str_replace("{$list}</div>", $list . $uploaded . $uploading . '</div>', $html);
    }

    protected static function fields(): array
    {
        $choices = [
            ['text' => 'First choice', 'value' => 'first'],
            ['text' => 'Second choice', 'value' => 'second'],
            ['text' => 'Third choice', 'value' => 'third'],
        ];

        $choiceInputs = fn(int $id) => [
            ['id' => "{$id}.1", 'label' => 'First choice'],
            ['id' => "{$id}.2", 'label' => 'Second choice'],
            ['id' => "{$id}.3", 'label' => 'Third choice'],
        ];

        return [
            ['type' => 'text', 'id' => 1, 'label' => 'Text', 'isRequired' => true, 'description' => 'Help text below the field.'],
            ['type' => 'email', 'id' => 2, 'label' => 'Email', 'isRequired' => true, 'errorMessage' => 'Please supply a valid email address'],
            [
                'type' => 'email', 'id' => 3, 'label' => 'Email with confirmation', 'emailConfirmEnabled' => true,
                'inputs' => [['id' => '3', 'label' => 'Enter Email'], ['id' => '3.2', 'label' => 'Confirm Email']],
            ],
            [
                'type' => 'name', 'id' => 4, 'label' => 'Name', 'isRequired' => true, 'nameFormat' => 'advanced',
                'inputs' => [
                    ['id' => '4.2', 'label' => 'Prefix', 'isHidden' => true],
                    ['id' => '4.3', 'label' => 'First'],
                    ['id' => '4.4', 'label' => 'Middle', 'isHidden' => true],
                    ['id' => '4.6', 'label' => 'Last'],
                    ['id' => '4.8', 'label' => 'Suffix', 'isHidden' => true],
                ],
                'errorMessage' => 'This field is required. Please complete the following fields: First, Last.',
            ],
            ['type' => 'textarea', 'id' => 5, 'label' => 'Textarea', 'maxLength' => 600],
            ['type' => 'phone', 'id' => 6, 'label' => 'Phone', 'phoneFormat' => 'international'],
            ['type' => 'number', 'id' => 7, 'label' => 'Number', 'numberFormat' => 'decimal_dot'],
            ['type' => 'website', 'id' => 8, 'label' => 'Website', 'placeholder' => 'https://'],
            ['type' => 'date', 'id' => 9, 'label' => 'Date (date picker)', 'dateType' => 'datepicker', 'dateFormat' => 'dmy', 'calendarIconType' => 'calendar'],
            [
                'type' => 'date', 'id' => 10, 'label' => 'Date (fields)', 'dateType' => 'datefield', 'dateFormat' => 'dmy',
                'inputs' => [['id' => '10.1', 'label' => 'DD'], ['id' => '10.2', 'label' => 'MM'], ['id' => '10.3', 'label' => 'YYYY']],
            ],
            ['type' => 'select', 'id' => 11, 'label' => 'Dropdown', 'choices' => $choices, 'placeholder' => 'Choose one'],
            ['type' => 'multiselect', 'id' => 12, 'label' => 'Multi select', 'choices' => $choices],
            ['type' => 'radio', 'id' => 13, 'label' => 'Radio', 'choices' => $choices],
            ['type' => 'checkbox', 'id' => 14, 'label' => 'Checkboxes', 'choices' => $choices, 'inputs' => $choiceInputs(14)],
            [
                'type' => 'consent', 'id' => 15, 'label' => 'Consent', 'isRequired' => true, 'checkboxLabel' => 'I agree to the privacy policy.',
                'inputs' => [['id' => '15.1', 'label' => 'Consent'], ['id' => '15.2', 'label' => 'Text', 'isHidden' => true], ['id' => '15.3', 'label' => 'Description', 'isHidden' => true]],
            ],
            ['type' => 'fileupload', 'id' => 16, 'label' => 'File upload', 'allowedExtensions' => 'jpg, jpeg, png, pdf', 'maxFileSize' => 10],
            ['type' => 'fileupload', 'id' => 17, 'label' => 'File upload (multiple)', 'multipleFiles' => true, 'allowedExtensions' => 'jpg, jpeg, png, pdf'],
            ['type' => 'section', 'id' => 18, 'label' => 'Section break (Gravity forms)', 'description' => 'Section description (Gravity forms).'],
        ];
    }
}
