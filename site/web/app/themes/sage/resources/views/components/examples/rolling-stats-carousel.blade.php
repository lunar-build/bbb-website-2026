<x-rolling-stats-carousel
  :image-left="Illuminate\Support\Facades\Vite::asset('resources/images/illustrations/pump.png')"
  :image-right="Illuminate\Support\Facades\Vite::asset('resources/images/illustrations/helmet.png')"
  :slides="[
    [
      'prefix' => 'There are',
      'value' => 7.5,
      'decimals' => 1,
      'formattedValue' => '7.5',
      'digits' => ['7', '.', '5'],
      'unit' => 'miles',
      'caption' => 'of cycling commuter routes in Bristol',
      'accessibleText' => 'There are 7.5 miles of cycling commuter routes in Bristol',
    ],
    [
      'prefix' => 'Over the last year',
      'value' => 1200,
      'decimals' => 0,
      'formattedValue' => '1200',
      'digits' => ['1', '2', '0', '0'],
      'unit' => 'bikes',
      'caption' => 'were loaned out for free across the West of England',
      'accessibleText' => 'Over the last year 1200 bikes were loaned out for free across the West of England',
    ],
    [
      'prefix' => 'We\'ve helped train',
      'value' => 4.2,
      'decimals' => 1,
      'formattedValue' => '4.2',
      'digits' => ['4', '.', '2'],
      'unit' => 'thousand people',
      'caption' => 'to ride with confidence since the scheme began',
      'accessibleText' => 'We\'ve helped train 4.2 thousand people to ride with confidence since the scheme began',
    ],
  ]"
/>
