<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class CaseStudyQuestions extends Field
{
    /**
     * The Q&A rows shown on a case study page.
     */
    public function fields(): array
    {
        $fields = Builder::make('case_study_questions', [
            'title' => 'Questions and answers',
            'position' => 'normal',
            'style' => 'seamless',
        ]);

        $fields
            ->setLocation('post_type', '==', 'case_study');

        $fields
            ->addRepeater('rows', [
                'label' => 'Rows',
                'button_label' => 'Add row',
                'layout' => 'block',
            ])
                ->addText('question', [
                    'label' => 'Question',
                    'required' => 1,
                ])
                ->addWysiwyg('answer', [
                    'label' => 'Answer',
                    'media_upload' => 0,
                    'toolbar' => 'basic',
                    'required' => 1,
                ])
            ->endRepeater();

        return $fields->build();
    }
}
