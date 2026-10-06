<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class PostStandfirst extends Field
{
    /**
     * The field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('post_standfirst');

        $fields
            ->setLocation('post_type', '==', 'post')
            ->setGroupConfig('position', 'side');

        $fields->addTextarea('standfirst', [
            'label' => 'Standfirst',
            'instructions' => 'Short lead paragraph shown above the article body.',
            'rows' => 2,
            'new_lines' => false,
        ]);

        return $fields->build();
    }
}
