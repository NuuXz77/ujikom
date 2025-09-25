<?php

use Livewire\Volt\Component;
use App\Models\Motors;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use Toast;

    public bool $deleteModal = false;
    public $motorId, $merk, $tipe_cc, $no_plat;
    public $listeners = ['showDeleteModal' => 'openModal'];

    public function openModal($id)
    {
        // Pastikan motor milik user yang sedang login
        $motor = Motors::where('ID_Motor', $id)
            ->where('owner_id', Auth::id())
            ->firstOrFail();
            
        $this->motorId = $motor->ID_Motor;
        $this->merk = $motor->merk;
        $this->tipe_cc = $motor->tipe_cc;
        $this->no_plat = $motor->no_plat;
        $this->deleteModal = true;
    }

    public function deleteMotor()
    {
        try {
            // Cek apakah motor sedang disewa
            $motor = Motors::findOrFail($this->motorId);
            
            if (in_array($motor->status, ['disewa', 'dibooking', 'dibayar'])) {
                $this->error('Motor tidak dapat dihapus karena sedang dalam proses penyewaan.');
                return;
            }

            // Cek apakah ada riwayat penyewaan yang belum selesai
            $activeRentals = \DB::table('penyewaans')
                ->where('motor_id', $this->motorId)
                ->whereIn('status', ['dibayar', 'disewa', 'dikembalikan'])
                ->count();

            if ($activeRentals > 0) {
                $this->error('Motor tidak dapat dihapus karena masih memiliki penyewaan yang aktif.');
                return;
            }

            $motor->delete();
            
            $this->reset(['motorId', 'merk', 'tipe_cc', 'no_plat']);
            $this->deleteModal = false;
            $this->success('Motor berhasil dihapus.');
            $this->dispatch('refresh');

        } catch (\Exception $e) {
            $this->error('Gagal menghapus motor: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->reset(['motorId', 'merk', 'tipe_cc', 'no_plat']);
        $this->deleteModal = false;
    }
}; ?>

<div>
    <x-modal wire:model="deleteModal" title="Hapus Motor" persistent class="backdrop-blur">
        <div class="mb-4 space-y-4">
            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <div class="flex items-start">
                    <x-icon name="o-exclamation-triangle" class="w-6 h-6 text-red-500 mr-3 mt-0.5" />
                    <div>
                        <h4 class="font-medium text-red-800 mb-2">Peringatan!</h4>
                        <p class="text-sm text-red-700 mb-3">Anda akan menghapus motor berikut secara permanen:</p>
                        
                        <div class="bg-white p-3 rounded border">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-600">Merk:</span>
                                    <div class="font-medium">{{ $merk }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-600">CC:</span>
                                    <div class="font-medium">{{ $tipe_cc }}</div>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-gray-600">Plat:</span>
                                    <div class="font-medium">{{ $no_plat }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-xs text-red-600">
                            <p>• Data motor akan dihapus secara permanen</p>
                            <p>• Riwayat penyewaan akan tetap tersimpan</p>
                            <p>• Aksi ini tidak dapat dibatalkan</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                <div class="flex items-start">
                    <x-icon name="o-information-circle" class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" />
                    <div class="text-sm text-yellow-700">
                        <p class="font-medium">Catatan:</p>
                        <p>Motor hanya bisa dihapus jika tidak sedang disewa atau memiliki booking aktif.</p>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Batal" wire:click="closeModal" class="btn-outline" />
            <x-button 
                label="Ya, Hapus Motor" 
                wire:click="deleteMotor" 
                class="btn-error" 
                spinner="deleteMotor"
                wire:confirm="Apakah Anda benar-benar yakin ingin menghapus motor ini?"
            />
        </x-slot:actions>
    </x-modal>
</div>
