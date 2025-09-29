<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Models\Motors;
use App\Models\User;
use Mary\Traits\Toast;

new class extends Component {
    use WithFileUploads;
    use Toast;

    public string $merk = '';
    public string $tipe_cc = '';
    public string $no_plat = '';
    public $motorPhoto = null;
    public $motorDocument = null;
    public float $tarif_harian = 0;
    public float $tarif_mingguan = 0;
    public float $tarif_bulanan = 0;
    public string $owner_id = '';
    public string $status = 'aktif';

    public array $ccOptions = [
        ['id' => '100cc', 'name' => '100 CC'], 
        ['id' => '125cc', 'name' => '125 CC'], 
        ['id' => '150cc', 'name' => '150 CC']
    ];

    public array $statusOptions = [
        ['id' => 'aktif', 'name' => 'Aktif'],
        ['id' => 'sedang_diverifikasi', 'name' => 'Sedang Diverifikasi'],
        ['id' => 'tidak_aktif', 'name' => 'Tidak Aktif'],
        ['id' => 'disewa', 'name' => 'Disewa']
    ];

    public function mount()
    {
        // Get all users with role 'pemilik' for owner selection
        $this->owners = User::where('role', 'pemilik')->get()->map(function ($user) {
            return ['id' => $user->ID_User, 'name' => $user->name];
        })->toArray();
    }

    public array $owners = [];

    public function save()
    {
        $data = $this->validate([
            'merk' => 'required|string|max:50',
            'tipe_cc' => 'required|in:100cc,125cc,150cc',
            'no_plat' => 'required|string|max:20|unique:motors,no_plat',
            'motorPhoto' => 'required|image|max:2048',
            'motorDocument' => 'required|image|max:2048',
            'tarif_harian' => 'required|numeric|min:0',
            'tarif_mingguan' => 'required|numeric|min:0',
            'tarif_bulanan' => 'required|numeric|min:0',
            'owner_id' => 'required|exists:users,ID_User',
            'status' => 'required|in:aktif,sedang_diverifikasi,tidak_aktif,disewa',
        ]);

        // Handle File Uploads with unique naming
        $owner = User::find($data['owner_id']);
        $date = now()->format('Ymd');

        // Generate unique filename for motor photo
        $photoExtension = $this->motorPhoto->extension();
        $photoFileName = Str::slug($owner->name . '-' . $data['merk'] . '-' . $date) . '.' . $photoExtension;
        $photoPath = $this->motorPhoto->storeAs('motors', $photoFileName, 'public');

        // Generate unique filename for document
        $documentExtension = $this->motorDocument->extension();
        $documentFileName = Str::slug($owner->name . '-' . $data['merk'] . '-document-' . $date) . '.' . $documentExtension;
        $documentPath = $this->motorDocument->storeAs('documents', $documentFileName, 'public');

        // Create Motor record
        $motor = Motors::create([
            'owner_id' => $data['owner_id'],
            'merk' => $data['merk'],
            'tipe_cc' => $data['tipe_cc'],
            'no_plat' => $data['no_plat'],
            'photo' => $photoPath,
            'dokumen_kepemilikan' => $documentPath,
            'status' => $data['status'],
        ]);

        // Create Tarif Rental record associated with the motor
        $motor->tarif()->create([
            'tarif_harian' => $data['tarif_harian'],
            'tarif_mingguan' => $data['tarif_mingguan'],
            'tarif_bulanan' => $data['tarif_bulanan'],
        ]);

        $this->success('Motor berhasil ditambahkan.');

        return redirect('/admin/motors');
    }
}; ?>

<div>
    <x-header title="Tambah Motor Baru" separator progress-indicator>
        <x-slot:middle></x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Kembali" link="/admin/motors" class="btn-ghost" />
        </x-slot:actions>
    </x-header>

    <div class="p-4">
        <x-form wire:submit="save">
            {{-- Section for Motor Details and Admin Fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                {{-- Left Column: Motor Info --}}
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold border-b pb-2">Informasi Motor</h2>
                    <x-select label="Pemilik Motor" :options="$owners" wire:model.defer="owner_id"
                        placeholder="Pilih Pemilik Motor" />
                    <x-input label="Merk Motor" wire:model.defer="merk" placeholder="cth: Honda Vario" />
                    <x-select label="CC (Cylinder Capacity)" :options="$ccOptions" wire:model.defer="tipe_cc"
                        placeholder="Pilih CC Motor" />
                    <x-input label="Nomor Polisi" wire:model.defer="no_plat" placeholder="cth: Z 1234 ABC" />
                    <x-select label="Status Motor" :options="$statusOptions" wire:model.defer="status"
                        placeholder="Pilih Status" />
                </div>

                {{-- Right Column: Rental Rates --}}
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold border-b pb-2">Tarif Sewa</h2>
                    <x-input label="Harga Harian" type="number" wire:model.defer="tarif_harian" min="0"
                        prefix="Rp" locale="id-ID" />
                    <x-input label="Harga Mingguan" type="number" wire:model.defer="tarif_mingguan" min="0"
                        prefix="Rp" locale="id-ID" />
                    <x-input label="Harga Bulanan" type="number" wire:model.defer="tarif_bulanan" min="0"
                        prefix="Rp" locale="id-ID" />
                </div>
            </div>

            {{-- Section for File Uploads --}}
            <div class="border-t pt-6">
                <h2 class="text-lg font-semibold mb-4">Upload Dokumen</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Motor Photo Upload --}}
                    <div>
                        <x-file wire:model="motorPhoto" accept="image/png, image/jpeg" label="Foto Motor">
                            <img src="{{ $motorPhoto ? $motorPhoto->temporaryUrl() : 'https://placehold.co/400x300?text=Upload+Photo' }}"
                                class="h-40 w-full object-cover rounded-lg" />
                        </x-file>
                    </div>

                    {{-- Document Upload --}}
                    <div>
                        <x-file wire:model="motorDocument" accept="image/png, image/jpeg" label="Foto Dokumen (STNK)">
                            <img src="{{ $motorDocument ? $motorDocument->temporaryUrl() : 'https://placehold.co/400x300?text=Upload+Dokumen' }}"
                                class="h-40 w-full object-cover rounded-lg" />
                        </x-file>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="mt-8 flex justify-end">
                <x-button type="submit" label="Tambah Motor" icon-right="o-arrow-right" class="btn-primary"
                    spinner="save" />
            </div>
        </x-form>
    </div>
</div>
