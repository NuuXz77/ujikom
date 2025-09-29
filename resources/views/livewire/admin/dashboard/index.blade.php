<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    public string $periode = 'bulanan';
    public array $periodeOptions = [
        ['id' => 'harian', 'name' => 'Harian'],
        ['id' => 'mingguan', 'name' => 'Mingguan'],
        ['id' => 'bulanan', 'name' => 'Bulanan']
    ];
    public array $chartData = [];
    public int $totalMotors = 0;
    public int $totalRentedMotors = 0; // status 'disewa'
    public float $totalPendapatan = 0; // dari bagi hasil admin
    public int $totalPemilik = 0;
    public int $totalPenyewa = 0;
    public int $totalAkun = 0;

    public function mount(): void
    {
        // Hitung total motor dan yang disewa
        $this->totalMotors = \App\Models\Motors::count();
        $this->totalRentedMotors = \App\Models\Motors::where('status', 'disewa')->count();

        // Hitung user berdasarkan role
        $this->totalPemilik = \App\Models\User::where('role', 'pemilik')->count();
        $this->totalPenyewa = \App\Models\User::where('role', 'penyewa')->count();
        $this->totalAkun = $this->totalPemilik + $this->totalPenyewa;

        // Total pendapatan: akumulasi bagian admin dari tabel bagi_hasils
        $this->totalPendapatan = (float) \App\Models\BagiHasil::sum('bagi_hasil_admin');

        $this->updateChartData();
    }

    public function updatedPeriode(): void
    {
        $this->updateChartData();
        $this->dispatch('chartDataUpdated', chartData: $this->chartData);
    }

    public function updateChartData(): void
    {
        $labels = [];
        $data = [];

        // Gunakan model Penyewaan, kolom tanggal_mulai
        if ($this->periode === 'harian') {
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->startOfDay();
                $labels[] = $date->locale('id')->translatedFormat('l');
                $count = \App\Models\Penyewaan::whereDate('tanggal_mulai', $date)->count();
                $data[] = $count;
            }
        } elseif ($this->periode === 'mingguan') {
            for ($i = 3; $i >= 0; $i--) {
                $startOfWeek = now()->subWeeks($i)->startOfWeek();
                $endOfWeek = now()->subWeeks($i)->endOfWeek();
                $labels[] = 'Minggu ' . $startOfWeek->format('d M');
                $count = \App\Models\Penyewaan::whereBetween('tanggal_mulai', [$startOfWeek, $endOfWeek])->count();
                $data[] = $count;
            }
        } else { // bulanan
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->locale('id')->translatedFormat('F Y');
                $count = \App\Models\Penyewaan::whereYear('tanggal_mulai', $date->year)
                    ->whereMonth('tanggal_mulai', $date->month)
                    ->count();
                $data[] = $count;
            }
        }

        $this->chartData = [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}; ?>

<div>
    {{-- HAPUS DROPDOWN DUPLIKAT DI SINI --}}
    <x-header title="Selamat Datang {{ auth()->user()->nama }}" separator progress-indicator />

    {{-- STATISTIK GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 my-6">
        <x-stat title="Total Motor Terdaftar" value="{{ $totalMotors }}" icon="o-truck" color="text-primary" />
        <x-stat title="Motor Disewa" value="{{ $totalRentedMotors }}" icon="o-clock" color="text-warning" />
        <x-stat title="Total Pendapatan" value="Rp {{ number_format($totalPendapatan, 0, ',', '.') }}"
            icon="o-currency-dollar" color="text-success" />
        <x-stat title="Total Pemilik" value="{{ $totalPemilik }}" icon="o-user-group" color="text-info" />
        <x-stat title="Total Penyewa" value="{{ $totalPenyewa }}" icon="o-user" color="text-secondary" />
        <x-stat title="Total Akun" value="{{ $totalAkun }}" icon="o-users" color="text-accent" />
    </div>

    {{-- CHARTJS LINE --}}
    <div class="bg-base-100 rounded-lg p-6 mb-8">
        <x-header title="Grafik Penyewaan" size="text-xl" separator>
            <x-slot:actions>
                {{-- CUKUP SATU DROPDOWN FILTER DI SINI --}}
                <x-select :options="$periodeOptions" option-label="name" option-value="id" wire:model.live="periode" />
            </x-slot:actions>
        </x-header>
        
        <canvas id="rentChart" height="120"></canvas>
    </div>

    {{-- KODE JAVASCRIPT YANG BENAR --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            let rentChart;
            const ctx = document.getElementById('rentChart').getContext('2d');

            // Fungsi untuk membuat atau mengupdate chart
            function renderChart(labels, data) {
                if (rentChart) {
                    rentChart.destroy(); // Hancurkan chart lama sebelum membuat yang baru
                }
                rentChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Penyewaan',
                            data: data,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                        }
                    }
                });
            }

            // Render chart saat halaman pertama kali dimuat
            // Menggunakan data awal dari PHP
            renderChart(@json($this->chartData['labels']), @json($this->chartData['data']));

            // Listener untuk event `chartDataUpdated` dari komponen Livewire
            document.addEventListener('chartDataUpdated', event => {
                // Mengambil data baru dari detail event
                const newLabels = event.detail.chartData.labels;
                const newData = event.detail.chartData.data;
                renderChart(newLabels, newData);
            });
        })
    </script>
</div>