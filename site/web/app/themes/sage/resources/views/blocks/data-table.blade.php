{{-- No heading in this block, so a plain <div> rather than a <section> (a section is meant to have a heading). --}}
@php($hasHeadings = collect($columns)->contains(fn ($column) => filled($column['heading'])))

<div {{ $attributes->class(['c-table']) }}>

<div class="o-container">
  <div class="c-table__scroll" tabindex="0" @if ($caption) role="region" aria-label="{{ $caption }}" @endif>
    <table class="c-table__table">
      @if ($caption)
        <caption class="u-sr-only">{{ $caption }}</caption>
      @endif
      {{-- Skipped when no column has a heading: an empty <thead> is an empty dark band to everyone and noise to a screen reader. --}}
      @if ($hasHeadings)
        <thead>
          <tr>
            @foreach ($columns as $column)
              <th scope="col" class="u-table-column-heading">{{ $column['heading'] }}</th>
            @endforeach
          </tr>
        </thead>
      @endif
      <tbody>
        @foreach ($rows as $row)
          <tr>
            @foreach ($row['cells'] as $i => $cell)
              @if ($i === 0 && $firstColumnIsHeader)
                <th scope="row" class="u-table-cell-heading u-rich-text">{!! $cell['content'] !!}</th>
              @else
                <td class="u-body-regular u-rich-text">{!! $cell['content'] !!}</td>
              @endif
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

</div>
