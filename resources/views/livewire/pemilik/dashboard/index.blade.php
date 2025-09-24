<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $periode = 'bulanan';
    public array $periodeOptions = [
        ['id' => 'harian', 'name' => 'Harian'],
        ['id' => 'mingguan', 'name' => 'Mingguan'],
        ['id' => 'bulanan', 'name' => 'Bulanan']
    ];
    public array $chartData = [];
    public int $totalMotorSaya = 0;
    public int $totalMotorDisewa = 0; // motor pemilik yang sedang disewa
    public float $totalPendapatanSaya = 0; // dari bagi hasil pemilik
    public int $totalPenyewaan = 0; // total penyewaan motor pemilik

    public function mount(): void
    {
        $userId = Auth::id();

        // Hitung total motor milik pemilik dan yang disewa
        $this->totalMotorSaya = \App\Models\Motors::where('owner_id', $userId)->count();
        $this->totalMotorDisewa = \App\Models\Motors::where('owner_id', $userId)
            ->where('status', 'disewa')->count();

        // Total penyewaan motor milik pemilik
        $this->totalPenyewaan = \App\Models\Penyewaan::whereHas('motor', function($q) use ($userId) {
            $q->where('owner_id', $userId);
        })->count();

        // Total pendapatan: akumulasi bagian pemilik dari tabel bagi_hasils untuk motor miliknya
        $this->totalPendapatanSaya = (float) \App\Models\BagiHasil::whereHas('penyewaan.motor', function($q) use ($userId) {
            $q->where('owner_id', $userId);
        })->sum('bagi_hasil_pemilik');

        $this->updateChartData();
    }

    public function updatedPeriode(): void
    {
        $this->updateChartData();
        $this->dispatch('chartDataUpdated', chartData: $this->chartData);
    }

    public function updateChartData(): void
    {
        $userId = Auth::id();
        $labels = [];
        $data = [];

        // Gunakan model Penyewaan dengan filter motor milik pemilik
        if ($this->periode === 'harian') {
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->startOfDay();
                $labels[] = $date->locale('id')->translatedFormat('l');
                $count = \App\Models\Penyewaan::whereDate('tanggal_mulai', $date)
                    ->whereHas('motor', function($q) use ($userId) {
                        $q->where('owner_id', $userId);
                    })->count();
                $data[] = $count;
            }
        } elseif ($this->periode === 'mingguan') {
            for ($i = 3; $i >= 0; $i--) {
                $startOfWeek = now()->subWeeks($i)->startOfWeek();
                $endOfWeek = now()->subWeeks($i)->endOfWeek();
                $labels[] = 'Minggu ' . $startOfWeek->format('d M');
                $count = \App\Models\Penyewaan::whereBetween('tanggal_mulai', [$startOfWeek, $endOfWeek])
                    ->whereHas('motor', function($q) use ($userId) {
                        $q->where('owner_id', $userId);
                    })->count();
                $data[] = $count;
            }
        } else { // bulanan
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->locale('id')->translatedFormat('F Y');
                $count = \App\Models\Penyewaan::whereYear('tanggal_mulai', $date->year)
                    ->whereMonth('tanggal_mulai', $date->month)
                    ->whereHas('motor', function($q) use ($userId) {
                        $q->where('owner_id', $userId);
                    })->count();
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
    <x-header title="Hallo Pemilik {{ auth()->user()->nama }}" separator progress-indicator />

    {{-- STATISTIK GRID PEMILIK --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 my-6">
        <x-stat title="Motor Saya" value="{{ $totalMotorSaya }}" icon="o-truck" color="text-primary" />
        <x-stat title="Motor Disewa" value="{{ $totalMotorDisewa }}" icon="o-clock" color="text-warning" />
        <x-stat title="Total Penyewaan" value="{{ $totalPenyewaan }}" icon="o-clipboard-document-list" color="text-info" />
        <x-stat title="Pendapatan Saya" value="Rp {{ number_format($totalPendapatanSaya, 0, ',', '.') }}"
            icon="o-currency-dollar" color="text-success" />
    </div>

    {{-- CHARTJS LINE --}}
    <div class="bg-base-100 rounded-lg p-6 mb-8">
        <x-header title="Grafik Penyewaan Motor Saya" size="text-xl" separator>
            <x-slot:actions>
                <x-select :options="$periodeOptions" option-label="name" option-value="id" wire:model.live="periode" />
            </x-slot:actions>
        </x-header>
        
        <canvas id="rentChart" height="120"></canvas>
    </div>

    {{-- KODE JAVASCRIPT --}}
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
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.1)',
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
            renderChart(@json($this->chartData['labels']), @json($this->chartData['data']));

            // Listener untuk event `chartDataUpdated` dari komponen Livewire
            document.addEventListener('chartDataUpdated', event => {
                const newLabels = event.detail.chartData.labels;
                const newData = event.detail.chartData.data;
                renderChart(newLabels, newData);
            });
        })
    </script>
</div>
