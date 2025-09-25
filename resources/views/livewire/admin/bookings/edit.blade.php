<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use App\Models\Motors;
use App\Models\User;
use Mary\Traits\Toast;
use Carbon\Carbon;

new class extends Component {
    use Toast;

    public $booking;
    public $motor;
    public $penyewa;
    
    // Form properties
    public $selectedMotor = null;
    public $selectedPenyewa = null;
    public $tanggal_mulai = '';
    public $tanggal_selesai = '';
    public $tipe_durasi = '';
    public $harga = 0;
    public $status = '';

    // Options
    public $motorOptions = [];
    public $penyewaOptions = [];
    public $tipeDurasiOptions = [
        ['id' => 'harian', 'name' => 'Harian'],
        ['id' => 'mingguan', 'name' => 'Mingguan'],
        ['id' => 'bulanan', 'name' => 'Bulanan']
    ];
    public $statusOptions = [
        ['id' => 'pending', 'name' => 'Pending'],
        ['id' => 'active', 'name' => 'Active'],
        ['id' => 'selesai', 'name' => 'Selesai'],
        ['id' => 'canceled', 'name' => 'Canceled']
    ];

    protected $rules = [
        'selectedMotor' => 'required|exists:motors,ID_Motor',
        'selectedPenyewa' => 'required|exists:users,ID_User',
        'tanggal_mulai' => 'required|date',
        'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        'tipe_durasi' => 'required|string',
        'harga' => 'required|numeric|min:0',
        'status' => 'required|string'
    ];

    public function mount($id)
    {
        $this->booking = Penyewaan::with(['motor', 'user'])->findOrFail($id);
        
        // Hanya izinkan edit untuk booking dengan status 'selesai'
        if ($this->booking->status !== 'selesai') {
            session()->flash('error', 'Hanya booking dengan status selesai yang dapat diedit.');
            return redirect()->route('bookings-admin');
        }
        
        // Load current data
        $this->selectedMotor = $this->booking->ID_Motor;
        $this->selectedPenyewa = $this->booking->ID_User;
        $this->tanggal_mulai = Carbon::parse($this->booking->tanggal_mulai)->format('Y-m-d');
        $this->tanggal_selesai = Carbon::parse($this->booking->tanggal_selesai)->format('Y-m-d');
        $this->tipe_durasi = $this->booking->tipe_durasi;
        $this->harga = $this->booking->harga;
        $this->status = $this->booking->status;

        // Load options
        $this->motorOptions = Motors::where('status', 'tersedia')
            ->orWhere('ID_Motor', $this->selectedMotor)
            ->get(['ID_Motor as id', 'merk as name', 'no_plat'])
            ->map(function($motor) {
                return [
                    'id' => $motor->id,
                    'name' => $motor->name . ' (' . $motor->no_plat . ')'
                ];
            })->toArray();

        $this->penyewaOptions = User::where('role', 'penyewa')
            ->get(['ID_User as id', 'nama as name'])
            ->toArray();
    }

    public function update()
    {
        $this->validate();

        try {
            $this->booking->update([
                'ID_Motor' => $this->selectedMotor,
                'ID_User' => $this->selectedPenyewa,
                'tanggal_mulai' => $this->tanggal_mulai,
                'tanggal_selesai' => $this->tanggal_selesai,
                'tipe_durasi' => $this->tipe_durasi,
                'harga' => $this->harga,
                'status' => $this->status,
            ]);

            $this->toast('success', 'Sukses!', 'Data booking berhasil diperbarui.');
            return redirect()->route('bookings-admin');

        } catch (\Exception $e) {
            $this->toast('error', 'Error!', 'Gagal memperbarui booking: ' . $e->getMessage());
        }
    }
};
?>

<div>
    <x-header title="Edit Booking #{{ $booking->ID_Penyewaan }}" separator progress-indicator>
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button icon="o-arrow-left" label="Kembali" link="{{ route('bookings-admin') }}" class="btn-ghost" />
                <x-badge value="{{ ucfirst($booking->status) }}" 
                    class="{{ $booking->status === 'selesai' ? 'badge badge-success' : 'badge badge-warning' }} badge-soft" />
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-6">
        <!-- Warning Alert -->
        <div class="alert alert-warning mb-6">
            <x-icon name="o-exclamation-triangle" class="w-5 h-5" />
            <div>
                <h3 class="font-bold">Perhatian!</h3>
                <div class="text-sm">Anda sedang mengedit booking yang sudah selesai. Pastikan perubahan yang dilakukan sudah tepat.</div>
            </div>
        </div>

        <x-form wire:submit.prevent="update">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Informasi Booking -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <x-icon name="o-clipboard-document-list" class="w-5 h-5" />
                        Informasi Booking
                    </h2>

                    <x-select 
                        label="Motor" 
                        :options="$motorOptions" 
                        wire:model="selectedMotor"
                        placeholder="Pilih motor..."
                        required
                    />

                    <x-select 
                        label="Penyewa" 
                        :options="$penyewaOptions" 
                        wire:model="selectedPenyewa"
                        placeholder="Pilih penyewa..."
                        required
                    />

                    <x-select 
                        label="Tipe Durasi" 
                        :options="$tipeDurasiOptions" 
                        wire:model="tipe_durasi"
                        placeholder="Pilih tipe durasi..."
                        required
                    />

                    <x-select 
                        label="Status" 
                        :options="$statusOptions" 
                        wire:model="status"
                        placeholder="Pilih status..."
                        required
                    />
                </div>

                <!-- Informasi Tanggal dan Harga -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <x-icon name="o-calendar-days" class="w-5 h-5" />
                        Tanggal & Harga
                    </h2>

                    <x-input 
                        label="Tanggal Mulai" 
                        type="date"
                        wire:model="tanggal_mulai"
                        required
                    />

                    <x-input 
                        label="Tanggal Selesai" 
                        type="date"
                        wire:model="tanggal_selesai"
                        required
                    />

                    <x-input 
                        label="Harga Total" 
                        type="number"
                        wire:model="harga"
                        prefix="Rp"
                        min="0"
                        step="1000"
                        required
                    />
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t">
                <x-button label="Batal" link="{{ route('bookings-admin') }}" class="btn-ghost" />
                <x-button label="Simpan Perubahan" type="submit" class="btn-primary" spinner="update" />
            </div>
        </x-form>
    </div>
</div>
