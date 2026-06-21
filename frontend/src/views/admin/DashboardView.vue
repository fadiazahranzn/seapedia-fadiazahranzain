<template>
  <div>
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard Admin</h1>
        <p class="page-sub">{{ periodLabel }}</p>
      </div>
      <div class="header-actions">
        <!-- Period filter -->
        <div class="period-tabs">
          <button v-for="p in periods" :key="p.value"
            class="period-tab" :class="{ active: activePeriod === p.value }"
            @click="setPeriod(p.value)">
            {{ p.label }}
          </button>
        </div>
        <!-- Custom date range -->
        <div v-if="activePeriod === 'custom'" class="custom-range">
          <input type="date" v-model="customFrom" class="date-input" @change="loadDashboard" />
          <span class="range-sep">–</span>
          <input type="date" v-model="customTo" class="date-input" @change="loadDashboard" />
        </div>
        <button class="btn btn-outline btn-sm" @click="loadDashboard" :disabled="loading">
          <RefreshCw class="w-3 h-3" :class="{ 'spin': loading }" />
        </button>
        <button class="btn btn-default btn-sm">
          <Download class="w-3 h-3" /> Export
        </button>
      </div>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading" class="grid-4" style="margin-bottom:14px;">
      <div v-for="i in 4" :key="i" class="skeleton-card" />
    </div>

    <template v-else>
      <!-- ══ PENGGUNA ══ -->
      <div class="section-label">Pengguna</div>
      <div class="grid-4">

        <div class="stat-card">
          <div class="stat-inner">
            <div class="stat-row">
              <span class="stat-label">Total User</span>
              <div class="icon-wrap icon-crimson">
                <Users class="w-4 h-4" />
              </div>
            </div>
            <div class="stat-value">{{ data.users?.total ?? 0 }}</div>
            <div class="stat-meta up">
              <TrendingUp class="w-3 h-3" /> +12% dari bulan lalu
            </div>
          </div>
          <div class="sparkline-row">
            <div v-for="(h, i) in userSparkline" :key="i" class="spark-bar" :style="`height:${h}%;background:#c41952;opacity:${0.25 + i*0.15}`" />
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-inner">
            <div class="stat-row">
              <span class="stat-label">Buyer</span>
              <div class="icon-wrap icon-green">
                <ShoppingCart class="w-4 h-4" />
              </div>
            </div>
            <div class="stat-value">{{ data.users?.buyers ?? 0 }}</div>
            <div class="stat-meta up">
              <TrendingUp class="w-3 h-3" /> 73.7% dari total user
            </div>
          </div>
          <div class="sparkline-row">
            <div v-for="(h, i) in buyerSparkline" :key="i" class="spark-bar" :style="`height:${h}%;background:#15803d;opacity:${0.25 + i*0.15}`" />
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-inner">
            <div class="stat-row">
              <span class="stat-label">Seller</span>
              <div class="icon-wrap icon-orange">
                <Store class="w-4 h-4" />
              </div>
            </div>
            <div class="stat-value">{{ data.users?.sellers ?? 0 }}</div>
            <div class="stat-meta up">
              <TrendingUp class="w-3 h-3" /> +5 toko baru minggu ini
            </div>
          </div>
          <div class="sparkline-row">
            <div v-for="(h, i) in sellerSparkline" :key="i" class="spark-bar" :style="`height:${h}%;background:#b45309;opacity:${0.25 + i*0.15}`" />
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-inner">
            <div class="stat-row">
              <span class="stat-label">Driver</span>
              <div class="icon-wrap icon-purple">
                <Truck class="w-4 h-4" />
              </div>
            </div>
            <div class="stat-value">{{ data.users?.drivers ?? 0 }}</div>
            <div class="stat-meta down">
              <TrendingDown class="w-3 h-3" /> −3 dari bulan lalu
            </div>
          </div>
          <div class="sparkline-row">
            <div v-for="(h, i) in driverSparkline" :key="i" class="spark-bar" :style="`height:${h}%;background:#7c3aed;opacity:${0.25 + i*0.15}`" />
          </div>
        </div>
      </div>

      <!-- ══ PESANAN ══ -->
      <div class="section-label">Pesanan</div>
      <div class="grid-6">
        <div class="order-card" v-for="s in orderStats" :key="s.key">
          <div class="order-label">{{ s.label }}</div>
          <div class="order-value" :class="s.color">{{ data.orders?.[s.key] ?? 0 }}</div>
          <div class="progress-wrap">
            <div class="progress-bar" :style="`width:${orderPercent(s.key)}%;background:${s.barColor}`" />
          </div>
        </div>
      </div>

      <!-- ══ CHART + DONUT ══ -->
      <div class="section-label">Aktivitas Terkini</div>
      <div class="grid-2-1" style="margin-bottom:14px;">

        <!-- Area chart -->
        <div class="card">
          <div class="card-head">
            <div>
              <div class="card-title">Pesanan Masuk (30 Hari)</div>
              <div class="card-desc">Total transaksi harian</div>
            </div>
          </div>
          <div class="card-body">
            <svg viewBox="0 0 540 120" xmlns="http://www.w3.org/2000/svg" style="width:100%;display:block;">
              <defs>
                <linearGradient id="aG" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#c41952" stop-opacity=".18"/>
                  <stop offset="100%" stop-color="#c41952" stop-opacity="0"/>
                </linearGradient>
              </defs>
              <line x1="0" y1="100" x2="540" y2="100" stroke="#f3e0e6" stroke-width="1"/>
              <line x1="0" y1="75"  x2="540" y2="75"  stroke="#f3e0e6" stroke-width="1" stroke-dasharray="4,4"/>
              <line x1="0" y1="50"  x2="540" y2="50"  stroke="#f3e0e6" stroke-width="1" stroke-dasharray="4,4"/>
              <line x1="0" y1="25"  x2="540" y2="25"  stroke="#f3e0e6" stroke-width="1" stroke-dasharray="4,4"/>
              <path d="M0,80 C18,75 36,60 54,65 C72,70 90,45 108,40 C126,35 144,55 162,50 C180,45 198,30 216,35 C234,40 252,60 270,55 C288,50 306,25 324,30 C342,35 360,50 378,45 C396,40 414,20 432,15 C450,10 468,30 486,25 C504,20 522,40 540,35 L540,100 L0,100 Z" fill="url(#aG)"/>
              <path d="M0,80 C18,75 36,60 54,65 C72,70 90,45 108,40 C126,35 144,55 162,50 C180,45 198,30 216,35 C234,40 252,60 270,55 C288,50 306,25 324,30 C342,35 360,50 378,45 C396,40 414,20 432,15 C450,10 468,30 486,25 C504,20 522,40 540,35" fill="none" stroke="#c41952" stroke-width="2" stroke-linejoin="round"/>
              <circle cx="432" cy="15" r="3.5" fill="#c41952" stroke="#fff" stroke-width="2"/>
              <text x="4" y="103" font-size="9" fill="#a06070" font-family="Inter,sans-serif">0</text>
              <text x="4" y="78"  font-size="9" fill="#a06070" font-family="Inter,sans-serif">50</text>
              <text x="4" y="53"  font-size="9" fill="#a06070" font-family="Inter,sans-serif">100</text>
              <text x="4" y="28"  font-size="9" fill="#a06070" font-family="Inter,sans-serif">150</text>
            </svg>
            <div style="display:flex;justify-content:space-between;margin-top:4px;">
              <span class="chart-axis-label">30 hari lalu</span>
              <span class="chart-axis-label">Hari ini</span>
            </div>
          </div>
          <div class="card-footer">
            <TrendingUp class="w-3 h-3 text-green-600" />
            <span style="color:#15803d;font-weight:700;">+18.4%</span>
            dibanding periode sebelumnya
          </div>
        </div>

        <!-- Donut -->
        <div class="card">
          <div class="card-head">
            <div>
              <div class="card-title">Status Pesanan</div>
              <div class="card-desc">Distribusi saat ini</div>
            </div>
          </div>
          <div class="card-body">
            <div style="display:flex;justify-content:center;margin-bottom:16px;">
              <svg viewBox="0 0 120 120" width="110" height="110">
                <circle cx="60" cy="60" r="42" fill="none" stroke="#f3e0e6" stroke-width="14"/>
                <circle cx="60" cy="60" r="42" fill="none" stroke="#15803d" stroke-width="14" :stroke-dasharray="`${donut.selesai} 263.8`"    stroke-dashoffset="0"                          stroke-linecap="round"/>
                <circle cx="60" cy="60" r="42" fill="none" stroke="#b45309" stroke-width="14" :stroke-dasharray="`${donut.dikemas} 263.8`"    :stroke-dashoffset="`-${donut.selesai}`"       stroke-linecap="round"/>
                <circle cx="60" cy="60" r="42" fill="none" stroke="#0369a1" stroke-width="14" :stroke-dasharray="`${donut.menunggu} 263.8`"   :stroke-dashoffset="`-${donut.selesai + donut.dikemas}`" stroke-linecap="round"/>
                <circle cx="60" cy="60" r="42" fill="none" stroke="#c41952" stroke-width="14" :stroke-dasharray="`${donut.kembali} 263.8`"    :stroke-dashoffset="`-${donut.selesai + donut.dikemas + donut.menunggu}`" stroke-linecap="round"/>
                <text x="60" y="56" text-anchor="middle" font-size="15" font-weight="800" fill="#1a1a1a" font-family="Inter,sans-serif">{{ data.orders?.total ?? 0 }}</text>
                <text x="60" y="68" text-anchor="middle" font-size="8"  fill="#a06070" font-family="Inter,sans-serif">pesanan</text>
              </svg>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
              <div class="legend-row">
                <span class="legend-dot" style="background:#15803d;"></span>
                <span class="legend-label">Selesai</span>
                <span class="legend-val">{{ data.orders?.pesanan_selesai ?? 0 }}</span>
                <span class="legend-badge success">{{ pct('pesanan_selesai') }}%</span>
              </div>
              <div class="legend-row">
                <span class="legend-dot" style="background:#b45309;"></span>
                <span class="legend-label">Dikemas</span>
                <span class="legend-val">{{ data.orders?.sedang_dikemas ?? 0 }}</span>
                <span class="legend-badge warning">{{ pct('sedang_dikemas') }}%</span>
              </div>
              <div class="legend-row">
                <span class="legend-dot" style="background:#0369a1;"></span>
                <span class="legend-label">Menunggu Kurir</span>
                <span class="legend-val">{{ data.orders?.menunggu_pengirim ?? 0 }}</span>
                <span class="legend-badge info">{{ pct('menunggu_pengirim') }}%</span>
              </div>
              <div class="legend-row">
                <span class="legend-dot" style="background:#c41952;"></span>
                <span class="legend-label">Dikembalikan</span>
                <span class="legend-val">{{ data.orders?.dikembalikan ?? 0 }}</span>
                <span class="legend-badge crimson">{{ pct('dikembalikan') }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ MARKETPLACE ══ -->
      <div class="section-label">Marketplace</div>
      <div class="grid-3">
        <div class="stat-card">
          <div class="stat-inner">
            <div class="stat-row">
              <span class="stat-label">Total Toko</span>
              <div class="icon-wrap icon-orange"><Store class="w-4 h-4" /></div>
            </div>
            <div class="stat-value">{{ data.stores?.total ?? 0 }}</div>
            <div class="stat-meta up"><TrendingUp class="w-3 h-3" /> +5 toko baru bulan ini</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-inner">
            <div class="stat-row">
              <span class="stat-label">Total Produk</span>
              <div class="icon-wrap icon-green"><Package class="w-4 h-4" /></div>
            </div>
            <div class="stat-value">{{ data.products?.total ?? 0 }}</div>
            <div class="stat-meta down" v-if="data.products?.out_of_stock">
              <TrendingDown class="w-3 h-3" /> {{ data.products.out_of_stock }} habis stok
            </div>
          </div>
        </div>
        <div class="stat-card" :class="(data.overdue?.total ?? 0) > 0 ? 'overdue-card' : ''">
          <div class="stat-inner">
            <div class="stat-row">
              <span class="stat-label">Overdue</span>
              <div class="icon-wrap icon-red"><AlertTriangle class="w-4 h-4" /></div>
            </div>
            <div class="stat-value" :class="(data.overdue?.total ?? 0) > 0 ? 'text-[#dc2626]' : ''">{{ data.overdue?.total ?? 0 }}</div>
            <div class="stat-meta down" v-if="(data.overdue?.total ?? 0) > 0">Perlu segera ditangani</div>
          </div>
        </div>
      </div>

      <!-- ══ DISKON ══ -->
      <div class="section-label">Diskon &amp; Promosi</div>
      <div class="grid-4">
        <div class="stat-card">
          <div class="stat-inner">
            <div class="stat-row">
              <span class="stat-label">Total Voucher</span>
              <div class="icon-wrap icon-crimson"><Tag class="w-4 h-4" /></div>
            </div>
            <div class="stat-value">{{ data.vouchers?.total ?? 0 }}</div>
            <div class="badge-row">
              <span class="inline-badge success">{{ data.vouchers?.active ?? 0 }} aktif</span>
              <span class="inline-badge outline">{{ (data.vouchers?.total ?? 0) - (data.vouchers?.active ?? 0) }} nonaktif</span>
            </div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-inner">
            <div class="stat-row">
              <span class="stat-label">Total Promo</span>
              <div class="icon-wrap icon-purple"><Star class="w-4 h-4" /></div>
            </div>
            <div class="stat-value">{{ data.promos?.total ?? 0 }}</div>
            <div class="badge-row">
              <span class="inline-badge success">{{ data.promos?.active ?? 0 }} aktif</span>
              <span class="inline-badge outline">{{ (data.promos?.total ?? 0) - (data.promos?.active ?? 0) }} nonaktif</span>
            </div>
          </div>
        </div>
        <div class="card" style="grid-column:span 2;">
          <div class="card-head" style="padding-bottom:12px;">
            <div class="card-title">Voucher Teratas</div>
            <button class="btn btn-outline btn-sm" @click="$router.push('/admin/vouchers')">Lihat Semua →</button>
          </div>
          <div class="card-body" style="padding-top:0;">
            <table class="tbl">
              <thead><tr><th>Kode</th><th>Diskon</th><th>Digunakan</th><th>Status</th></tr></thead>
              <tbody>
                <tr v-for="v in topVouchers" :key="v.id">
                  <td class="bold">{{ v.code }}</td>
                  <td class="mono">{{ v.discount_type === 'percentage' ? v.discount_value + '%' : formatPrice(v.discount_value) }}</td>
                  <td class="mono">{{ v.used_count ?? 0 }}×</td>
                  <td><span :class="v.is_active ? 'inline-badge success' : 'inline-badge outline'">{{ v.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                </tr>
                <tr v-if="!topVouchers.length">
                  <td colspan="4" style="text-align:center;color:#a06070;padding:16px;">Belum ada voucher.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ══ DETAIL MONITORING ══ -->
      <div class="section-label">Detail Monitoring</div>
      <div class="card" style="margin-bottom:14px;">
        <div class="card-head">
          <div class="card-title">Data Platform</div>
          <button class="btn btn-outline btn-sm" @click="loadMonitoringTab(activeMonTab)" :disabled="monLoading">
            <RefreshCw class="w-3 h-3" :class="{ 'spin': monLoading }" />
          </button>
        </div>
        <div class="mon-tabs">
          <button v-for="t in monTabs" :key="t.key"
            class="mon-tab" :class="{ active: activeMonTab === t.key }"
            @click="setMonTab(t.key)">
            {{ t.label }}
          </button>
        </div>
        <div class="card-body" style="padding-top:8px;">
          <div v-if="monLoading" class="mon-loading">
            <div v-for="i in 5" :key="i" class="mon-skel" />
          </div>

          <!-- Users -->
          <template v-else-if="activeMonTab === 'users'">
            <table class="tbl">
              <thead><tr><th>ID</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Bergabung</th></tr></thead>
              <tbody>
                <tr v-for="u in monData.users" :key="u.id">
                  <td class="mono">#{{ u.id }}</td>
                  <td class="bold">{{ u.name }}</td>
                  <td class="mono">{{ u.username }}</td>
                  <td>{{ u.email }}</td>
                  <td>
                    <span v-for="r in u.roles" :key="r.id ?? r"
                      class="role-badge" :class="`role-${r.role ?? r}`">{{ r.role ?? r }}</span>
                  </td>
                  <td class="mono">{{ formatDate(u.created_at) }}</td>
                </tr>
                <tr v-if="!monData.users?.length"><td colspan="6" class="empty-row">Tidak ada data.</td></tr>
              </tbody>
            </table>
            <div v-if="monMeta.users" class="mon-meta">{{ monData.users?.length }} dari {{ monMeta.users.total }} user</div>
          </template>

          <!-- Stores -->
          <template v-else-if="activeMonTab === 'stores'">
            <table class="tbl">
              <thead><tr><th>ID</th><th>Nama Toko</th><th>Pemilik</th><th>Produk</th><th>Dibuat</th></tr></thead>
              <tbody>
                <tr v-for="s in monData.stores" :key="s.id">
                  <td class="mono">#{{ s.id }}</td>
                  <td class="bold">{{ s.name }}</td>
                  <td>{{ s.user?.name }}</td>
                  <td class="mono">{{ s.products_count }}</td>
                  <td class="mono">{{ formatDate(s.created_at) }}</td>
                </tr>
                <tr v-if="!monData.stores?.length"><td colspan="5" class="empty-row">Tidak ada data.</td></tr>
              </tbody>
            </table>
            <div v-if="monMeta.stores" class="mon-meta">{{ monData.stores?.length }} dari {{ monMeta.stores.total }} toko</div>
          </template>

          <!-- Products -->
          <template v-else-if="activeMonTab === 'products'">
            <table class="tbl">
              <thead><tr><th>ID</th><th>Nama Produk</th><th>Toko</th><th>Harga</th><th>Stok</th></tr></thead>
              <tbody>
                <tr v-for="p in monData.products" :key="p.id">
                  <td class="mono">#{{ p.id }}</td>
                  <td class="bold">{{ p.name }}</td>
                  <td>{{ p.store?.name }}</td>
                  <td class="mono">{{ formatPrice(p.price) }}</td>
                  <td class="mono" :class="p.stock === 0 ? 'text-[#dc2626]' : ''">{{ p.stock }}</td>
                </tr>
                <tr v-if="!monData.products?.length"><td colspan="5" class="empty-row">Tidak ada data.</td></tr>
              </tbody>
            </table>
            <div v-if="monMeta.products" class="mon-meta">{{ monData.products?.length }} dari {{ monMeta.products.total }} produk</div>
          </template>

          <!-- Orders -->
          <template v-else-if="activeMonTab === 'orders'">
            <table class="tbl">
              <thead><tr><th>ID</th><th>Pembeli</th><th>Toko</th><th>Total</th><th>Status</th><th>Tanggal</th></tr></thead>
              <tbody>
                <tr v-for="o in monData.orders" :key="o.id">
                  <td class="mono">#{{ o.id }}</td>
                  <td>{{ o.user?.name }}</td>
                  <td>{{ o.store?.name }}</td>
                  <td class="mono">{{ formatPrice(o.total) }}</td>
                  <td><span class="inline-badge" :class="statusBadgeClass(o.status)">{{ statusLabel(o.status) }}</span></td>
                  <td class="mono">{{ formatDate(o.created_at) }}</td>
                </tr>
                <tr v-if="!monData.orders?.length"><td colspan="6" class="empty-row">Tidak ada data.</td></tr>
              </tbody>
            </table>
            <div v-if="monMeta.orders" class="mon-meta">{{ monData.orders?.length }} dari {{ monMeta.orders.total }} pesanan</div>
          </template>

          <!-- Deliveries -->
          <template v-else-if="activeMonTab === 'deliveries'">
            <table class="tbl">
              <thead><tr><th>ID</th><th>Order</th><th>Driver</th><th>Metode</th><th>Status</th><th>Diambil</th><th>Selesai</th></tr></thead>
              <tbody>
                <tr v-for="d in monData.deliveries" :key="d.id">
                  <td class="mono">#{{ d.id }}</td>
                  <td class="mono">#{{ d.order_id }}</td>
                  <td>{{ d.driver?.name ?? '—' }}</td>
                  <td class="mono">{{ d.order?.delivery_method ?? '—' }}</td>
                  <td><span class="inline-badge" :class="d.status === 'completed' ? 'success' : d.status === 'taken' ? 'warning' : 'outline'">{{ d.status }}</span></td>
                  <td class="mono">{{ d.picked_up_at ? formatDate(d.picked_up_at) : '—' }}</td>
                  <td class="mono">{{ d.delivered_at ? formatDate(d.delivered_at) : '—' }}</td>
                </tr>
                <tr v-if="!monData.deliveries?.length"><td colspan="7" class="empty-row">Tidak ada data.</td></tr>
              </tbody>
            </table>
            <div v-if="monMeta.deliveries" class="mon-meta">{{ monData.deliveries?.length }} dari {{ monMeta.deliveries.total }} pengiriman</div>
          </template>

          <!-- Overdue -->
          <template v-else-if="activeMonTab === 'overdue'">
            <table class="tbl">
              <thead><tr><th>Order ID</th><th>User ID</th><th>Metode</th><th>Status</th><th>Dibuat</th></tr></thead>
              <tbody>
                <tr v-for="o in monData.overdue" :key="o.id">
                  <td class="mono">#{{ o.id }}</td>
                  <td class="mono">{{ o.user_id }}</td>
                  <td class="mono">{{ o.delivery_method }}</td>
                  <td><span class="inline-badge" style="background:#fff1f2;color:#dc2626;border-color:#fecdd3;">{{ statusLabel(o.status) }}</span></td>
                  <td class="mono">{{ formatDate(o.created_at) }}</td>
                </tr>
                <tr v-if="!monData.overdue?.length">
                  <td colspan="5" class="empty-row" style="color:#15803d;">Tidak ada pesanan overdue.</td>
                </tr>
              </tbody>
            </table>
          </template>
        </div>
      </div>

      <!-- ══ OVERDUE ══ -->
      <div class="section-label">Overdue Handling</div>
      <div class="card">
        <div class="card-head">
          <div>
            <div class="card-title">Simulasi &amp; Proses Overdue</div>
            <div class="card-desc">Ambang batas SLA sebelum pesanan dinyatakan overdue</div>
          </div>
        </div>
        <div class="card-body">
          <div class="overdue-grid">
            <div>
              <div class="sla-chips">
                <span class="sla-chip">⏱ Instant → 1 hari</span>
                <span class="sla-chip">⏱ Next Day → 2 hari</span>
                <span class="sla-chip">⏱ Regular → 5 hari</span>
              </div>
              <p style="font-size:12.5px;color:#a06070;margin-top:12px;max-width:500px;">
                Sistem akan otomatis menandai pesanan yang melewati SLA sebagai
                <strong style="color:#dc2626;">overdue</strong>.
                Gunakan tombol untuk simulasi hari berikutnya atau proses semua overdue.
              </p>
            </div>
            <div class="overdue-actions">
              <button class="btn btn-outline" :disabled="simulating" @click="simulateNextDay">
                <Play class="w-3 h-3" />
                {{ simulating ? 'Memproses...' : 'Simulasi Hari Berikutnya' }}
              </button>
              <button class="btn btn-destructive" :disabled="processing" @click="processOverdue">
                <AlertTriangle class="w-3 h-3" />
                {{ processing ? 'Memproses...' : `Proses Overdue (${data.overdue?.total ?? 0})` }}
              </button>
            </div>
          </div>

          <div v-if="overdueResult" class="overdue-result">
            <div class="result-header">
              <div class="result-check">
                <Check class="w-3 h-3 text-white" />
              </div>
              <span style="font-size:13px;font-weight:700;">{{ overdueResult.message }}</span>
              <span class="result-amount">{{ formatPrice(overdueResult.processed?.reduce((s,o) => s + (o.refunded ?? 0), 0)) }}</span>
            </div>
            <div v-if="overdueResult.processed?.length" style="border-top:1px solid #d1fae5;">
              <table class="tbl">
                <tbody>
                  <tr v-for="o in overdueResult.processed.slice(0, 3)" :key="o.order_id">
                    <td class="bold">Pesanan #{{ o.order_id }}</td>
                    <td><span class="inline-badge outline">{{ o.delivery_method }}</span></td>
                    <td style="text-align:right;font-weight:600;color:#15803d;">{{ formatPrice(o.refunded) }}</td>
                  </tr>
                </tbody>
              </table>
              <div v-if="overdueResult.processed.length > 3" class="result-more">
                +{{ overdueResult.processed.length - 3 }} pesanan lainnya berhasil diproses
              </div>
            </div>
          </div>
        </div>
      </div>

    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import {
  RefreshCw, Download, Users, ShoppingCart, Store, Truck, Package,
  Tag, Star, AlertTriangle, TrendingUp, TrendingDown, Play, Check
} from '@lucide/vue'
import { adminApi } from '@/services/admin'
import { toast } from 'vue-sonner'

const data = ref({})
const loading = ref(true)
const simulating = ref(false)
const processing = ref(false)
const overdueResult = ref(null)

// ── Detail monitoring tabs ──
const monTabs = [
  { key: 'users',      label: 'Users' },
  { key: 'stores',     label: 'Toko' },
  { key: 'products',   label: 'Produk' },
  { key: 'orders',     label: 'Pesanan' },
  { key: 'deliveries', label: 'Pengiriman' },
  { key: 'overdue',    label: 'Overdue' },
]
const activeMonTab = ref('users')
const monLoading   = ref(false)
const monData      = ref({ users: [], stores: [], products: [], orders: [], deliveries: [], overdue: [] })
const monMeta      = ref({})

async function loadMonitoringTab(tab) {
  monLoading.value = true
  try {
    const apiMap = {
      users:      adminApi.getUsers,
      stores:     adminApi.getStores,
      products:   adminApi.getProducts,
      orders:     adminApi.getOrders,
      deliveries: adminApi.getDeliveries,
      overdue:    adminApi.getOverdueOrders,
    }
    const { data: res } = await apiMap[tab]()
    if (tab === 'overdue') {
      monData.value[tab] = res.data ?? []
    } else {
      // Laravel paginate() returns { data: [], total, current_page, ... }
      monData.value[tab] = res.data ?? []
      if (res.total !== undefined) monMeta.value[tab] = { total: res.total }
    }
  } catch {
    toast.error('Gagal memuat data monitoring.')
  } finally {
    monLoading.value = false
  }
}

function setMonTab(tab) {
  activeMonTab.value = tab
  if (!monData.value[tab]?.length) loadMonitoringTab(tab)
}

const periods = [
  { value: 'daily',   label: 'Harian' },
  { value: 'monthly', label: 'Bulanan' },
  { value: '3month',  label: '3 Bulan' },
  { value: 'custom',  label: 'Custom' },
]
const activePeriod = ref('daily')
const customFrom   = ref('')
const customTo     = ref('')

const periodLabel = computed(() => {
  const now = new Date()
  if (activePeriod.value === 'daily')   return `Hari ini, ${now.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' })}`
  if (activePeriod.value === 'monthly') return `Bulan ini — ${now.toLocaleDateString('id-ID', { month:'long', year:'numeric' })}`
  if (activePeriod.value === '3month')  return `3 bulan terakhir`
  if (customFrom.value && customTo.value)
    return `${new Date(customFrom.value).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' })} – ${new Date(customTo.value).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' })}`
  return 'Pilih rentang tanggal'
})

function setPeriod(val) {
  activePeriod.value = val
  if (val !== 'custom') loadDashboard()
}

function periodParams() {
  const now = new Date()
  if (activePeriod.value === 'daily') {
    const d = now.toISOString().slice(0, 10)
    return { from: d, to: d }
  }
  if (activePeriod.value === 'monthly') {
    const from = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10)
    return { from, to: now.toISOString().slice(0, 10) }
  }
  if (activePeriod.value === '3month') {
    const from = new Date(now.getFullYear(), now.getMonth() - 2, 1).toISOString().slice(0, 10)
    return { from, to: now.toISOString().slice(0, 10) }
  }
  return { from: customFrom.value, to: customTo.value }
}

const userSparkline   = [40, 50, 55, 60, 70, 100]
const buyerSparkline  = [50, 60, 65, 72, 82, 100]
const sellerSparkline = [55, 60, 70, 75, 88, 100]
const driverSparkline = [90, 95, 100, 95, 90, 88]

const orderStats = [
  { key: 'total',             label: 'Total',           color: '',                 barColor: '#f3e0e6' },
  { key: 'sedang_dikemas',    label: 'Dikemas',         color: 'text-[#b45309]',   barColor: '#b45309' },
  { key: 'menunggu_pengirim', label: 'Menunggu Kurir',  color: 'text-[#0369a1]',   barColor: '#0369a1' },
  { key: 'sedang_dikirim',    label: 'Dikirim',         color: 'text-[#7c3aed]',   barColor: '#7c3aed' },
  { key: 'pesanan_selesai',   label: 'Selesai',         color: 'text-[#15803d]',   barColor: '#15803d' },
  { key: 'dikembalikan',      label: 'Dikembalikan',    color: 'text-[#dc2626]',   barColor: '#dc2626' },
]

function orderPercent(key) {
  const total = data.value.orders?.total
  if (!total) return 0
  const val = data.value.orders?.[key] ?? 0
  return Math.round((val / total) * 100)
}

const donut = computed(() => {
  const total = data.value.orders?.total || 1
  const circ = 263.8
  return {
    selesai:  ((data.value.orders?.pesanan_selesai   ?? 0) / total * circ).toFixed(1),
    dikemas:  ((data.value.orders?.sedang_dikemas    ?? 0) / total * circ).toFixed(1),
    menunggu: ((data.value.orders?.menunggu_pengirim ?? 0) / total * circ).toFixed(1),
    kembali:  ((data.value.orders?.dikembalikan      ?? 0) / total * circ).toFixed(1),
  }
})

function pct(key) {
  const total = data.value.orders?.total
  if (!total) return 0
  return ((data.value.orders?.[key] ?? 0) / total * 100).toFixed(1)
}

const topVouchers = computed(() => data.value.vouchers?.list?.slice(0, 3) ?? [])

function formatDate(d) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatPrice(p) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p ?? 0)
}

function statusLabel(s) {
  const map = {
    sedang_dikemas: 'Dikemas', menunggu_pengirim: 'Menunggu Kurir',
    sedang_dikirim: 'Dikirim', pesanan_selesai: 'Selesai', dikembalikan: 'Dikembalikan',
  }
  return map[s] ?? s
}

function statusBadgeClass(s) {
  const map = {
    pesanan_selesai: 'success', sedang_dikemas: 'warning',
    menunggu_pengirim: 'info', sedang_dikirim: 'info', dikembalikan: 'crimson',
  }
  return map[s] ?? 'outline'
}

async function loadDashboard() {
  loading.value = true
  try {
    const params = periodParams()
    const { data: res } = await adminApi.getDashboard(params)
    data.value = res.data
  } finally { loading.value = false }
}

async function simulateNextDay() {
  simulating.value = true
  try {
    const { data: res } = await adminApi.simulateNextDay()
    toast.success(res.message)
    await loadDashboard()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Gagal simulasi.')
  } finally { simulating.value = false }
}

async function processOverdue() {
  processing.value = true
  overdueResult.value = null
  try {
    const { data: res } = await adminApi.processOverdue()
    overdueResult.value = res
    toast.success(res.message)
    await loadDashboard()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Gagal proses overdue.')
  } finally { processing.value = false }
}

onMounted(() => {
  loadDashboard()
  loadMonitoringTab('users')
})
</script>

<style scoped>
/* ── Page header ── */
.page-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:28px; gap:16px; flex-wrap:wrap; }
.page-title  { font-size:22px; font-weight:800; letter-spacing:-.4px; color:#1a1a1a; }
.page-sub    { font-size:13px; color:#a06070; margin-top:3px; }
.header-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }

/* ── Period filter ── */
.period-tabs {
  display:flex; background:#fdf2f5; border:1.5px solid #f3e0e6;
  border-radius:10px; padding:3px; gap:2px;
}
.period-tab {
  padding:6px 14px; font-size:12.5px; font-weight:600; font-family:inherit;
  border:none; border-radius:7px; background:transparent; color:#a06070;
  cursor:pointer; transition:all .15s; white-space:nowrap;
}
.period-tab.active { background:#c41952; color:#fff; box-shadow:0 2px 6px rgba(196,25,82,.25); }
.period-tab:not(.active):hover { background:rgba(196,25,82,.08); color:#c41952; }

/* ── Custom date range ── */
.custom-range { display:flex; align-items:center; gap:6px; }
.date-input {
  height:34px; padding:0 10px; font-size:12.5px; font-family:inherit;
  border:1.5px solid #f3e0e6; border-radius:8px; background:#fff;
  color:#1a1a1a; outline:none; transition:border-color .15s;
}
.date-input:focus { border-color:#c41952; box-shadow:0 0 0 3px rgba(196,25,82,.08); }
.range-sep { font-size:13px; color:#a06070; font-weight:600; }

.spin { animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

/* ── Section label ── */
.section-label {
  font-size:11px; font-weight:700; letter-spacing:.8px; text-transform:uppercase;
  color:#a06070; margin:28px 0 12px;
  display:flex; align-items:center; gap:8px;
}
.section-label::after { content:''; flex:1; height:1px; background:#f3e0e6; }

/* ── Grids ── */
.grid-4   { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
.grid-6   { display:grid; grid-template-columns:repeat(6,1fr); gap:14px; }
.grid-3   { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
.grid-2-1 { display:grid; grid-template-columns:2fr 1fr; gap:14px; }

/* ── Skeleton ── */
.skeleton-card { height:96px; background:#fce4ec; border-radius:10px; animation:pulse 1.4s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }

/* ── Stat card ── */
.stat-card {
  background:#fff; border:1px solid #f3e0e6; border-radius:10px;
  overflow:hidden; box-shadow:0 1px 3px rgba(196,25,82,.05);
  transition:border-color 150ms, box-shadow 150ms;
}
.stat-card:hover { border-color:#f3c6d4; box-shadow:0 3px 12px rgba(196,25,82,.1); }
.stat-card.overdue-card { border-color:#fecdd3; background:#fff5f7; }
.stat-inner { padding:18px 18px 12px; }

.stat-row { display:flex; align-items:flex-start; justify-content:space-between; }
.stat-label { font-size:11px; font-weight:600; color:#a06070; text-transform:uppercase; letter-spacing:.05em; }
.stat-value { font-size:26px; font-weight:800; letter-spacing:-.5px; margin-top:8px; color:#1a1a1a; }
.stat-meta  { font-size:11px; color:#a06070; margin-top:5px; display:flex; align-items:center; gap:4px; }
.stat-meta.up   { color:#15803d; }
.stat-meta.down { color:#dc2626; }

.icon-wrap { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.icon-crimson { background:#fce4ec; color:#c41952; }
.icon-green   { background:#f0fdf4; color:#15803d; }
.icon-orange  { background:#fffbeb; color:#b45309; }
.icon-purple  { background:#f5f3ff; color:#7c3aed; }
.icon-red     { background:#fff1f2; color:#dc2626; }
.icon-blue    { background:#f0f9ff; color:#0369a1; }

/* ── Sparkline ── */
.sparkline-row { display:flex; align-items:flex-end; gap:3px; height:32px; padding:0 18px 10px; }
.spark-bar { flex:1; border-radius:2px 2px 0 0; min-height:3px; transition:opacity 150ms; }

/* ── Order cards ── */
.order-card {
  background:#fff; border:1px solid #f3e0e6; border-radius:10px;
  padding:14px 16px; box-shadow:0 1px 3px rgba(196,25,82,.05);
}
.order-label { font-size:11px; font-weight:600; color:#a06070; text-transform:uppercase; letter-spacing:.04em; }
.order-value { font-size:20px; font-weight:800; margin-top:6px; color:#1a1a1a; }
.progress-wrap { background:#fdf2f5; border-radius:99px; height:5px; overflow:hidden; margin-top:10px; }
.progress-bar  { height:100%; border-radius:99px; transition:width .5s; }

/* ── Card ── */
.card {
  background:#fff; border:1px solid #f3e0e6; border-radius:10px;
  overflow:hidden; box-shadow:0 1px 3px rgba(196,25,82,.05);
  transition:border-color 150ms;
}
.card:hover { border-color:#f3c6d4; }
.card-head {
  padding:16px 20px 0;
  display:flex; align-items:center; justify-content:space-between;
}
.card-title { font-size:14px; font-weight:700; color:#1a1a1a; }
.card-desc  { font-size:12px; color:#a06070; margin-top:2px; }
.card-body  { padding:16px 20px 20px; }
.card-footer {
  padding:10px 20px; border-top:1px solid #f3e0e6;
  display:flex; align-items:center; gap:6px;
  font-size:12px; color:#a06070;
}
.chart-axis-label { font-size:10px; color:#a06070; }

/* ── Donut legend ── */
.legend-row { display:flex; align-items:center; gap:8px; font-size:12px; }
.legend-dot { width:9px; height:9px; border-radius:2px; flex-shrink:0; }
.legend-label { flex:1; color:#a06070; }
.legend-val   { font-weight:700; font-variant-numeric:tabular-nums; }
.legend-badge { font-size:10px; font-weight:600; padding:1px 7px; border-radius:99px; border:1px solid transparent; }
.legend-badge.success { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
.legend-badge.warning { background:#fffbeb; color:#b45309; border-color:#fed7aa; }
.legend-badge.info    { background:#f0f9ff; color:#0369a1; border-color:#bae6fd; }
.legend-badge.crimson { background:#fce4ec; color:#c41952; border-color:#f3c6d4; }

/* ── Inline badges ── */
.badge-row { display:flex; gap:6px; margin-top:10px; }
.inline-badge { font-size:10px; font-weight:600; padding:2px 8px; border-radius:99px; border:1px solid transparent; }
.inline-badge.success { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
.inline-badge.outline { background:transparent; color:#a06070; border-color:#f3e0e6; }
.inline-badge.warning { background:#fffbeb; color:#b45309; border-color:#fed7aa; }

/* ── Table ── */
.tbl { width:100%; border-collapse:collapse; }
.tbl thead tr { border-bottom:1px solid #f3e0e6; }
.tbl th { text-align:left; font-size:11px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; color:#a06070; padding:10px 14px; }
.tbl tbody tr { border-bottom:1px solid #fdf0f3; transition:background 100ms; }
.tbl tbody tr:last-child { border-bottom:none; }
.tbl tbody tr:hover { background:#fff8fa; }
.tbl td { padding:11px 14px; font-size:13px; color:#1a1a1a; }
.tbl td.mono { font-variant-numeric:tabular-nums; font-size:12.5px; }
.tbl td.bold { font-weight:600; }

/* ── Overdue ── */
.overdue-grid { display:grid; grid-template-columns:1fr auto; gap:20px; align-items:start; }
.sla-chips { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
.sla-chip  { font-size:11.5px; font-weight:500; background:#fdf2f5; border:1px solid #f3e0e6; color:#a06070; border-radius:7px; padding:3px 10px; }
.overdue-actions { display:flex; flex-direction:column; gap:8px; }

.overdue-result { margin-top:20px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; overflow:hidden; }
.result-header  { display:flex; align-items:center; gap:10px; padding:14px 16px; }
.result-check   { width:20px; height:20px; background:#15803d; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.result-amount  { margin-left:auto; font-size:13px; font-weight:700; color:#15803d; }
.result-more    { text-align:center; font-size:12px; color:#a06070; padding:10px; border-top:1px solid #d1fae5; }

/* ── Monitoring tabs ── */
.mon-tabs {
  display:flex; gap:2px; padding:0 20px; border-bottom:1px solid #f3e0e6;
  overflow-x:auto; scrollbar-width:none;
}
.mon-tabs::-webkit-scrollbar { display:none; }
.mon-tab {
  padding:10px 16px; font-size:12.5px; font-weight:600; font-family:inherit;
  border:none; background:transparent; color:#a06070; cursor:pointer;
  border-bottom:2px solid transparent; transition:all .15s; white-space:nowrap;
}
.mon-tab.active { color:#c41952; border-bottom-color:#c41952; }
.mon-tab:not(.active):hover { color:#c41952; background:#fdf2f5; border-radius:6px 6px 0 0; }

/* ── Monitoring loading ── */
.mon-loading { display:flex; flex-direction:column; gap:8px; padding:8px 0; }
.mon-skel { height:36px; background:#fdf2f5; border-radius:6px; animation:pulse 1.4s infinite; }

/* ── Mon pagination meta ── */
.mon-meta { font-size:11.5px; color:#a06070; padding:10px 14px; border-top:1px solid #fdf0f3; text-align:right; }

/* ── Empty row ── */
.empty-row { text-align:center; color:#a06070; padding:24px; font-size:13px; }

/* ── Role badges ── */
.role-badge {
  font-size:10px; font-weight:600; padding:1px 7px; border-radius:99px;
  border:1px solid transparent; margin-right:3px; display:inline-block;
}
.role-buyer  { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
.role-seller { background:#fffbeb; color:#b45309; border-color:#fed7aa; }
.role-driver { background:#f5f3ff; color:#7c3aed; border-color:#ddd6fe; }
.role-admin  { background:#fce4ec; color:#c41952; border-color:#f3c6d4; }

/* ── Inline badge extra ── */
.inline-badge.info   { background:#f0f9ff; color:#0369a1; border-color:#bae6fd; }
.inline-badge.crimson { background:#fce4ec; color:#c41952; border-color:#f3c6d4; }

/* ── Buttons ── */
.btn {
  display:inline-flex; align-items:center; gap:6px;
  font-size:13px; font-weight:600; font-family:inherit;
  padding:7px 14px; border-radius:7px; border:1px solid transparent;
  cursor:pointer; transition:all 150ms; white-space:nowrap;
}
.btn-default { background:#c41952; color:#fff; }
.btn-default:hover { background:#a0133f; }
.btn-outline { background:transparent; border-color:#f3e0e6; color:#1a1a1a; }
.btn-outline:hover { background:#fce4ec; border-color:#f3c6d4; color:#c41952; }
.btn-destructive { background:#dc2626; color:#fff; }
.btn-destructive:hover { background:#b91c1c; }
.btn-destructive:disabled { opacity:.6; cursor:not-allowed; }
.btn-outline:disabled { opacity:.6; cursor:not-allowed; }
.btn-sm { padding:5px 11px; font-size:12px; }
</style>
