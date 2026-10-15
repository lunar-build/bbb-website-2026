<section {{ $attributes->class(['c-table']) }}>

<div class="o-container">
  <div class="c-table__scroll" role="region" aria-label="{{ $caption }}" tabindex="0">
    <table class="c-table__table">
      <caption class="u-sr-only">{{ $caption }}</caption>
      <thead>
        <tr>
          @foreach ($columns as $column)
            <th scope="col" class="u-table-column-heading">{{ $column['heading'] }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach ($rows as $row)
          <tr>
            @foreach ($row['cells'] as $i => $cell)
              @if ($i === 0 && $firstColumnIsHeader)
                <th scope="row" class="u-table-cell-heading">{!! $cell['content'] !!}</th>
              @else
                <td class="u-body-regular">{!! $cell['content'] !!}</td>
              @endif
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

</section>
