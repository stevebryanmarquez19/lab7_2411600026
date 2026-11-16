<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>{{ $title }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a2e24; }
  h1 { color: #0d5c3e; font-size: 16px; margin: 0; }
  .muted { color: #748279; margin-bottom: 8px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #0d5c3e; color: #fff; text-align: left; padding: 5px; }
  td { padding: 5px; border-bottom: 1px solid #e7eee9; }
  tr:nth-child(even) td { background: #f4f9f6; }
</style></head><body>
<h1>{{ $title }}</h1>
<div class="muted">{{ config('app.name') }} · generated {{ now()->format('M d, Y H:i') }} · {{ count($rows) }} row(s)</div>
<table>
  <thead><tr>@foreach($headings as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
  <tbody>
  @forelse($rows as $row)<tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
  @empty<tr><td colspan="{{ count($headings) }}">No data.</td></tr>@endforelse
  </tbody>
</table>
</body></html>
