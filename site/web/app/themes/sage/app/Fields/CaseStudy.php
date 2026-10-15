<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class CaseStudy extends Field
{
    /**
     * The field group.
     *
     * Same meta names as the old site's case studies, so migrated values land
     * here directly. No single case study design yet — kept for that.
     */
    public function fields(): array
    {
        $fields = Builder::make('case_study', [
            'title' => 'Case Study',
            'position' => 'side',
        ]);

        $fields
            ->setLocation('post_type', '==', 'case_study');

        $fields
            ->addTextarea('quote', [
                'label' => 'Quote',
                'rows' => 3,
            ])
            ->addImage('card_image', [
                'label' => 'Card image',
                'return_format' => 'id',
                'preview_size' => 'medium',
            ]);

        return $fields->build();
    }
}
