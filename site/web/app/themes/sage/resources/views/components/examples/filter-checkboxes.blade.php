<x-filter-checkboxes :groups="[
  [
    'heading' => 'Filter by location',
    'items' => [
      ['label' => 'Bath', 'name' => 'location', 'value' => 'bath', 'checked' => true],
      ['label' => 'Bristol', 'name' => 'location', 'value' => 'bristol', 'checked' => true],
      ['label' => 'North Somerset', 'name' => 'location', 'value' => 'north-somerset', 'checked' => true],
      ['label' => 'South Gloucestershire', 'name' => 'location', 'value' => 'south-gloucestershire', 'checked' => true],
    ],
  ],
  [
    'heading' => 'Categories',
    'items' => [
      ['label' => 'New bikes', 'name' => 'category', 'value' => 'new-bikes', 'checked' => true],
      ['label' => 'Secondhand bikes', 'name' => 'category', 'value' => 'secondhand-bikes', 'checked' => true],
      ['label' => 'E-bikes', 'name' => 'category', 'value' => 'e-bikes'],
      ['label' => 'Custom bikes', 'name' => 'category', 'value' => 'custom-bikes'],
      ['label' => 'Donate a bike', 'name' => 'category', 'value' => 'donate-a-bike'],
    ],
  ],
  [
    'heading' => 'Fix your bike',
    'items' => [
      ['label' => 'Bike servicing', 'name' => 'service', 'value' => 'bike-servicing', 'checked' => true],
      ['label' => 'Mobile bike repairs', 'name' => 'service', 'value' => 'mobile-bike-repairs', 'checked' => true],
    ],
  ],
  [
    'heading' => 'Rent a bike',
    'items' => [
      ['label' => 'Bike hire', 'name' => 'rent', 'value' => 'bike-hire'],
      ['label' => 'Bike tours', 'name' => 'rent', 'value' => 'bike-tours'],
    ],
  ],
]" />
