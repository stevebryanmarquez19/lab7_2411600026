{{--
  Reusable filter form. Usage:
  <x-filter-form :action="route('purchase-orders.index')" :fields="[
      ['name' => 'search', 'label' => 'Search', 'type' => 'text', 'col' => 'col-md-3'],
      ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft']],
      ['name' => 'date_from', 'label' => 'From', 'type' => 'date'],
  ]" />
  Anything placed between the tags (extra buttons) is shown after Filter / Reset.
--}}
@props(['action', 'fields' => []])
<form method="GET" action="{{ $action }}" class="row g-2 align-items-end">
  @foreach($fields as $field)
    @php($type = $field['type'] ?? 'text')
    <div class="{{ $field['col'] ?? 'col-md-3 col-sm-6' }}">
      <label class="form-label small fw-semibold mb-1" for="f_{{ $field['name'] }}">{{ $field['label'] }}</label>
      @if($type === 'select')
        <select id="f_{{ $field['name'] }}" name="{{ $field['name'] }}" class="form-select form-select-sm">
          <option value="">{{ $field['placeholder'] ?? 'All' }}</option>
          @foreach($field['options'] as $value => $label)
            <option value="{{ $value }}" {{ (string) request($field['name']) === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      @else
        <input id="f_{{ $field['name'] }}" type="{{ $type }}" name="{{ $field['name'] }}" value="{{ request($field['name']) }}" class="form-control form-control-sm" placeholder="{{ $field['placeholder'] ?? '' }}" @if($type === 'number') min="0" @endif>
      @endif
    </div>
  @endforeach
  <div class="col-auto d-flex gap-2">
    <button class="btn btn-emerald btn-sm" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
    <a href="{{ $action }}" class="btn btn-light border btn-sm">Reset</a>
    {{ $slot }}
  </div>
</form>
