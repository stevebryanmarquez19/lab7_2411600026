<script>
(function () {
  const cur = @json(config('procurement.currency'));
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const money = n => cur + Number(n || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
  const get = url => fetch(url, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}}).then(r => r.ok ? r.json() : Promise.reject(r.status));

  const jobs = [
    ['{{ route('api.dashboard.pending-orders') }}', d => {
      document.getElementById('wPendingCount').textContent = d.pendingCount;
      document.getElementById('wPendingValue').textContent = money(d.pendingValue) + ' total value';
      document.getElementById('wPendingAdmin').textContent = d.needsAdminCount + ' need administrator approval';
    }],
    ['{{ route('api.dashboard.recent-orders') }}', d => {
      document.getElementById('wRecentBody').innerHTML = d.recentOrders.length ? d.recentOrders.map(o =>
        `<tr><td><a href="${esc(o.url)}" class="fw-semibold text-decoration-none">${esc(o.reference)}</a><div class="item-sku">${esc(o.supplier)}</div></td><td><span class="badge ${esc(o.status_badge)}">${esc(o.status_label)}</span></td><td class="text-end">${esc(o.total_formatted)}</td></tr>`
      ).join('') : '<tr><td class="empty-state">No purchase orders yet.</td></tr>';
    }],
    ['{{ route('api.dashboard.supplier-performance') }}', d => {
      document.getElementById('wPerfList').innerHTML = d.topSuppliers.length ? d.topSuppliers.map(s =>
        `<li class="list-group-item d-flex justify-content-between align-items-center"><span>${esc(s.name)}</span><span class="badge text-bg-success">${esc(s.on_time_rate)}%</span></li>`
      ).join('') : '<li class="list-group-item text-muted">No completed deliveries yet.</li>';
    }],
    ['{{ route('api.dashboard.reorder-alerts') }}', d => {
      document.getElementById('wReorderCount').textContent = d.reorderAlerts.length;
      document.getElementById('wReorderBody').innerHTML = d.reorderAlerts.length ? d.reorderAlerts.map(a =>
        `<tr><td class="item-name">${esc(a.name)}<div class="item-sku">${esc(a.sku)}</div></td><td class="text-end">${esc(a.quantity)}</td><td class="text-end">${esc(a.reorder_point)}</td><td class="text-end">${esc(a.suggested_quantity)}</td><td>${esc(a.supplier)}</td></tr>`
      ).join('') : '<tr><td colspan="5" class="empty-state">Nothing needs reordering right now.</td></tr>';
    }],
  ];

  function refresh() { jobs.forEach(([url, apply]) => get(url).then(apply).catch(() => {})); }
  setInterval(refresh, 30000); // same polling approach as the Lab 6 inventory cards
})();
</script>
