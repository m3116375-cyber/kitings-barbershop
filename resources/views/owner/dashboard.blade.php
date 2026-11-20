
@extends('layouts.panel')

@section('title', 'Dashboard Owner')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

@verbatim
<style>
/*
=============================================
DASHBOARD OWNER - LAYOUT BARU
Warna dan tipografi asli tetap dipertahankan
=============================================
*/

.owner-layout {
    width: 100%;
    min-width: 0;
}

/* HEADER DAN FILTER */
.owner-layout-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 24px;
}

.owner-layout-heading {
    min-width: 0;
}

.owner-layout-period {
    margin-top: 12px;
}

.owner-layout-filter {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    padding: 14px;
    min-width: 0;
}

.owner-layout-filter-field {
    min-width: 0;
}

.owner-layout-filter-field label {
    display: block;
    margin-bottom: 6px;
}

.owner-layout-filter-field input {
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.owner-layout-filter button {
    flex-shrink: 0;
    white-space: nowrap;
}

/* KARTU RINGKASAN UTAMA */
.owner-layout-metrics {
    display: grid;
    grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.owner-layout-primary {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.owner-layout-secondary {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    grid-template-rows: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.owner-layout-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
    overflow-wrap: anywhere;
}

.owner-layout-primary .owner-layout-card {
    min-height: 190px;
}

.owner-layout-secondary .owner-layout-card {
    min-height: 87px;
}

.owner-layout-card-value {
    margin-top: 12px;
    line-height: 1.35;
}

/* GRAFIK STATISTIK */
.owner-layout-charts {
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(0, 1fr);
    gap: 24px;
    margin-bottom: 24px;
    align-items: stretch;
}

.owner-layout-chart-card {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.owner-layout-chart-area {
    position: relative;
    width: 100%;
    height: 290px;
}

.owner-layout-chart-area canvas {
    width: 100% !important;
    height: 100% !important;
    display: block;
}

/* TABEL STATISTIK */
.owner-layout-tables {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
    align-items: stretch;
}

.owner-layout-table-card {
    min-width: 0;
    min-height: 145px;
}

.owner-layout-table-scroll {
    width: 100%;
    overflow-x: auto;
}

.owner-layout-table {
    width: 100%;
    border-collapse: collapse;
}

.owner-layout-table td:first-child {
    padding-right: 14px;
    overflow-wrap: anywhere;
}

.owner-layout-table td:last-child {
    white-space: nowrap;
}

/* LAYOUT TABLET */
@media (max-width: 1050px) {
    .owner-layout-header {
        flex-direction: column;
        align-items: stretch;
    }

    .owner-layout-metrics {
        grid-template-columns: minmax(0, 1fr);
    }

    .owner-layout-secondary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        grid-template-rows: auto;
    }

    .owner-layout-primary .owner-layout-card {
        min-height: 145px;
    }
}

/* LAYOUT PONSEL */
@media (max-width: 700px) {
    .owner-layout-filter {
        flex-wrap: wrap;
    }

    .owner-layout-filter-field {
        flex: 1 1 130px;
    }

    .owner-layout-filter button {
        flex: 1 1 100%;
    }

    .owner-layout-charts,
    .owner-layout-tables {
        grid-template-columns: minmax(0, 1fr);
    }

    .owner-layout-chart-area {
        height: 255px;
    }
}

@media (max-width: 440px) {
    .owner-layout-primary,
    .owner-layout-secondary {
        grid-template-columns: minmax(0, 1fr);
    }

    .owner-layout-primary .owner-layout-card {
        min-height: 110px;
    }

    .owner-layout-secondary .owner-layout-card {
        min-height: 90px;
    }
}
</style>
@endverbatim
@endpush

@section('content')

<div class="owner-layout" data-layout="owner-redesign-v2">

    {{-- HEADER DASHBOARD --}}
    <div class="owner-layout-header">

        <div class="owner-layout-heading">
            <h1 class="text-2xl font-bold">
                Dashboard Owner
            </h1>

            <p class="owner-layout-period text-sm text-stone-500">
                Periode: {{ tgl($from) }} &ndash; {{ tgl($to) }}
            </p>
        </div>

        {{-- FILTER PERIODE --}}
        <form method="GET"
              class="owner-layout-filter bg-white rounded-lg border border-stone-200 text-sm">

            <div class="owner-layout-filter-field">
                <label for="owner-from"
                       class="text-xs font-semibold">
                    Dari
                </label>

                <input
                    id="owner-from"
                    type="date"
                    name="from"
                    value="{{ $from }}"
                    class="border border-stone-300 rounded px-2 py-1.5"
                >
            </div>

            <div class="owner-layout-filter-field">
                <label for="owner-to"
                       class="text-xs font-semibold">
                    Sampai
                </label>

                <input
                    id="owner-to"
                    type="date"
                    name="to"
                    value="{{ $to }}"
                    class="border border-stone-300 rounded px-2 py-1.5"
                >
            </div>

            <button type="submit"
                    class="px-4 py-1.5 rounded bg-brand text-white font-semibold">
                Terapkan
            </button>

        </form>

    </div>

    {{-- RINGKASAN STATISTIK --}}
    <div class="owner-layout-metrics">

        {{-- INFORMASI UTAMA --}}
        <div class="owner-layout-primary">

            <section class="owner-layout-card bg-white rounded-lg border border-stone-200 p-4">
                <div class="text-xs text-stone-500">
                    Total Booking
                </div>

                <div class="owner-layout-card-value text-2xl font-bold text-brand">
                    {{ $summary['count'] }}
                </div>
            </section>

            <section class="owner-layout-card bg-white rounded-lg border border-stone-200 p-4">
                <div class="text-xs text-stone-500">
                    Pendapatan (lunas)
                </div>

                <div class="owner-layout-card-value text-2xl font-bold text-brand">
                    {{ rupiah($summary['revenue']) }}
                </div>
            </section>

        </div>

        {{-- INFORMASI TERLARIS --}}
        <div class="owner-layout-secondary">

            <section class="owner-layout-card bg-white rounded-lg border border-stone-200 p-4">
                <div class="text-xs text-stone-500">
                    Layanan Terlaris
                </div>

                <div class="owner-layout-card-value text-lg font-bold text-brand">
                    {{ $topServices->first()->name ?? '-' }}
                </div>
            </section>

            <section class="owner-layout-card bg-white rounded-lg border border-stone-200 p-4">
                <div class="text-xs text-stone-500">
                    Produk Terlaris
                </div>

                <div class="owner-layout-card-value text-lg font-bold text-brand">
                    {{ $topProducts->first()->name ?? '-' }}
                </div>
            </section>

        </div>

    </div>

    {{-- GRAFIK DASHBOARD --}}
    <div class="owner-layout-charts">

        <section class="owner-layout-chart-card bg-white rounded-lg border border-stone-200 p-4">

            <h2 class="font-bold mb-3">
                Pendapatan per Minggu (8 minggu terakhir)
            </h2>

            <div class="owner-layout-chart-area">
                <canvas
                    id="revChart"
                    data-labels="{{ json_encode($weekly['labels']) }}"
                    data-values="{{ json_encode($weekly['values']) }}"
                    role="img"
                    aria-label="Grafik pendapatan per minggu"
                ></canvas>
            </div>

        </section>

        <section class="owner-layout-chart-card bg-white rounded-lg border border-stone-200 p-4">

            <h2 class="font-bold mb-3">
                Keterisian Slot per Hari (%)
            </h2>

            <div class="owner-layout-chart-area">
                <canvas
                    id="occChart"
                    data-labels="{{ json_encode($occupancy['labels']) }}"
                    data-values="{{ json_encode($occupancy['values']) }}"
                    role="img"
                    aria-label="Grafik keterisian slot per hari"
                ></canvas>
            </div>

        </section>

    </div>

    {{-- TABEL STATISTIK --}}
    <div class="owner-layout-tables">

        {{-- LAYANAN TERLARIS --}}
        <section class="owner-layout-table-card bg-white rounded-lg border border-stone-200 p-4">

            <h2 class="font-bold mb-2">
                Layanan Terlaris
            </h2>

            <div class="owner-layout-table-scroll">
                <table class="owner-layout-table text-sm">
                    <tbody>

                        @forelse($topServices as $i => $r)

                            <tr class="border-t border-stone-100">

                                <td class="py-1.5">
                                    {{ $i + 1 }}. {{ $r->name }}
                                </td>

                                <td class="py-1.5 text-right font-semibold">
                                    {{ $r->total }}x
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td class="text-stone-500 py-2">
                                    Belum ada data.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

        </section>

        {{-- PRODUK TERLARIS --}}
        <section class="owner-layout-table-card bg-white rounded-lg border border-stone-200 p-4">

            <h2 class="font-bold mb-2">
                Produk Terlaris
            </h2>

            <div class="owner-layout-table-scroll">
                <table class="owner-layout-table text-sm">
                    <tbody>

                        @forelse($topProducts as $i => $r)

                            <tr class="border-t border-stone-100">

                                <td class="py-1.5">
                                    {{ $i + 1 }}. {{ $r->name }}
                                </td>

                                <td class="py-1.5 text-right font-semibold">
                                    {{ $r->total }} pcs
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td class="text-stone-500 py-2">
                                    Belum ada data.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

        </section>

    </div>

</div>

@endsection

@push('scripts')
@verbatim
<script>
(function () {
    'use strict';

    function drawChart(id, label, maxY = null) {
        const canvas = document.getElementById(id);

        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        let labels;
        let values;

        try {
            labels = JSON.parse(canvas.dataset.labels || '[]');
            values = JSON.parse(canvas.dataset.values || '[]');

            if (!Array.isArray(labels) || !Array.isArray(values)) {
                return;
            }
        } catch (error) {
            console.error('Data grafik tidak valid:', error);
            return;
        }

        // Hapus grafik lama jika sudah tersedia
        const previous = Chart.getChart(canvas);

        if (previous) {
            previous.destroy();
        }

        const yAxis = {
            beginAtZero: true
        };

        if (maxY !== null) {
            yAxis.max = maxY;
        }

        // Warna grafik tetap sama dengan versi asli
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: values,
                    backgroundColor: '#B89A8E',
                    borderColor: '#6B4F45',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: yAxis
                }
            }
        });
    }

    drawChart('revChart', 'Pendapatan (Rp)');
    drawChart('occChart', 'Keterisian (%)', 100);

})();
</script>
@endverbatim
@endpush
