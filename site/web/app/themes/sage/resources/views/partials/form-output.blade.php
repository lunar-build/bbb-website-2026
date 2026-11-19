{{-- The form slot for the Form block: a Gravity Form or a pasted embed. The editor and pattern library get a placeholder, since an embed's scripts would run (or be dead) inside the editor iframe. --}}
@if ($block->preview)
  <p class="c-form__placeholder">
    @if ($formSource === 'embed')
      {{ __('The embed code will render here.', 'sage') }}
    @else
      {{ sprintf(__('Gravity Form #%d will render here.', 'sage'), $formId) }}
    @endif
  </p>
@elseif ($formSource === 'embed')
  {{-- Raw HTML by design: the editor pastes trusted embed code. --}}
  <div class="c-form__embed">{!! $embedCode !!}</div>
@else
  @php gravity_form($formId, false, false, false, null, true, 0, true); @endphp
@endif
