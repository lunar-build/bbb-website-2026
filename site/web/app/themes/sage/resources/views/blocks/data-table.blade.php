{{-- No heading in this block, so a plain <div> rather than a <section> (a section is meant to have a heading). --}}
<div {{ $attributes->class(['c-table']) }}>

<div class="o-container">
  <div class="c-table__scroll" role="region" aria-label="{{ $caption }}" tabindex="0">
    <table class="c-table__table">
      <caption class="u-sr-only">{{ $caption }}</caption>
      <thead>
        <tr>
          @foreach ($columns as $column)
            <th scope="col" @class(['u-table-column-heading', 'c-table__col--'.($column['width'] ?? 'auto')])>{{ $column['heading'] }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach ($rows as $row)
          <tr>
            @foreach ($row['cells'] as $i => $cell)
              @php($widthClass = 'c-table__col--'.($columns[$i]['width'] ?? 'auto'))
              @if ($i === 0 && $firstColumnIsHeader)
                <th scope="row" @class(['u-table-cell-heading', 'u-rich-text', $widthClass])>{!! $cell['content'] !!}</th>
              @else
                <td @class(['u-body-regular', 'u-rich-text', $widthClass])>{!! $cell['content'] !!}</td>
              @endif
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

</div>
