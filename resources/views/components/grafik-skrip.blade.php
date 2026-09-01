
@assets
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@endassets

@script
<script>
    // Warna token dibaca dari CSS supaya satu sumber kebenaran (app.css).
    const tokenWarna = (nama) => getComputedStyle(document.documentElement).getPropertyValue(nama).trim();

    const dasarChart = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 200 },
        font: { family: 'Inter, sans-serif' },
    };

    // Grafik batang bertumpuk horizontal: komposisi Kurang/Sedang/Baik/Tidak
    // tersedia untuk tiap dimensi. Angka ditulis langsung pada segmen.
    Alpine.data('grafikKomposisi', (data) => ({
        chart: null,
        gambar() {
            if (typeof Chart === 'undefined') return
            const kisi = tokenWarna('--color-grafik-kisi')
            this.chart = new Chart(this.$refs.kanvas, {
                type: 'bar',
                data: {
                    labels: data.label_baris,
                    datasets: data.seri.map((s) => ({
                        label: s.label,
                        data: s.nilai,
                        backgroundColor: tokenWarna(s.warna),
                        borderColor: tokenWarna('--color-kartu'),
                        borderWidth: 2,
                        borderRadius: 3,
                        barThickness: 20,
                    })),
                },
                options: {
                    ...dasarChart,
                    indexAxis: 'y',
                    scales: {
                        x: { stacked: true, grid: { color: kisi }, ticks: { precision: 0, font: { size: 11 } }, title: { display: true, text: 'jumlah indikator', font: { size: 10 }, color: '#8b8375' } },
                        y: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } },
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: { label: (c) => `${c.dataset.label}: ${c.raw} indikator` },
                        },
                    },
                },
                plugins: [{
                    id: 'labelSegmen',
                    afterDatasetsDraw(chart) {
                        const { ctx } = chart
                        ctx.save()
                        ctx.font = '600 10px Inter, sans-serif'
                        ctx.fillStyle = '#fffdf8'
                        ctx.textAlign = 'center'
                        ctx.textBaseline = 'middle'
                        chart.data.datasets.forEach((ds, i) => {
                            chart.getDatasetMeta(i).data.forEach((bar, j) => {
                                const v = ds.data[j]
                                if (! v) return
                                const { x, y, base } = bar.getProps(['x', 'y', 'base'], true)
                                if (Math.abs(x - base) < 16) return
                                ctx.fillText(v, (x + base) / 2, y)
                            })
                        })
                        ctx.restore()
                    },
                }],
            })
        },
        destroy() { this.chart?.destroy() },
    }))

    // Grafik batang horizontal: peringkat seluruh kabupaten/kota pada satu
    // indikator. Batang diwarnai menurut label capaian; daerah terpilih
    // ditebalkan garisnya.
    Alpine.data('grafikPeringkat', (data) => ({
        chart: null,
        gambar() {
            if (typeof Chart === 'undefined') return
            const petaWarna = {
                Baik: tokenWarna('--color-grafik-baik'),
                Sedang: tokenWarna('--color-grafik-sedang'),
                Kurang: tokenWarna('--color-grafik-kurang'),
            }
            const petaNilai = { Baik: 3, Sedang: 2, Kurang: 1 }
            this.chart = new Chart(this.$refs.kanvas, {
                type: 'bar',
                data: {
                    labels: data.baris.map((b) => b.nama),
                    datasets: [{
                        data: data.baris.map((b) => petaNilai[b.label] ?? 0),
                        backgroundColor: data.baris.map((b) => petaWarna[b.label] ?? tokenWarna('--color-grafik-kosong')),
                        borderColor: data.baris.map((b) => b.terpilih ? tokenWarna('--color-navy-900') : tokenWarna('--color-kartu')),
                        borderWidth: data.baris.map((b) => b.terpilih ? 2.5 : 1),
                        borderRadius: 3,
                        barThickness: 14,
                    }],
                },
                options: {
                    ...dasarChart,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            min: 0, max: 3,
                            grid: { color: tokenWarna('--color-grafik-kisi') },
                            ticks: { stepSize: 1, font: { size: 11 }, callback: (v) => ({ 1: 'Kurang', 2: 'Sedang', 3: 'Baik' }[v] ?? '') },
                        },
                        y: { grid: { display: false }, ticks: { font: { size: 10 }, autoSkip: false } },
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (c) => {
                                    const b = data.baris[c.dataIndex]
                                    return `${b.label} · ${b.perubahan} · peringkat ${b.peringkat}`
                                },
                            },
                        },
                    },
                },
            })
        },
        destroy() { this.chart?.destroy() },
    }))
</script>
@endscript
