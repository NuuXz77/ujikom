<?php

use Livewire\Volt\Component;
use App\Models\Motors;

new class extends Component {
    public $featuredMotors = [];
    public $testimonials = [];
    public $stats = [];

    public function mount()
    {
        // Get featured motors (available motors)
        $this->featuredMotors = Motors::with(['tarif'])
            ->where('status', 'aktif')
            ->take(6)
            ->get();

        // Sample testimonials data
        $this->testimonials = [
            [
                'name' => 'Ahmad Rizki',
                'rating' => 5,
                'comment' => 'Pelayanan sangat memuaskan! Motor dalam kondisi prima dan prosesnya mudah.',
                'avatar' => 'https://ui-avatars.com/api/?name=Ahmad+Rizki&background=3B82F6&color=fff'
            ],
            [
                'name' => 'Sari Dewi',
                'rating' => 5,
                'comment' => 'Harga terjangkau, motor bersih dan terawat. Pasti akan sewa lagi!',
                'avatar' => 'https://ui-avatars.com/api/?name=Sari+Dewi&background=EC4899&color=fff'
            ],
            [
                'name' => 'Budi Santoso',
                'rating' => 4,
                'comment' => 'Pengalaman sewa yang menyenangkan. Staff ramah dan profesional.',
                'avatar' => 'https://ui-avatars.com/api/?name=Budi+Santoso&background=10B981&color=fff'
            ]
        ];

        // Sample stats
        $this->stats = [
            ['label' => 'Motor Tersedia', 'value' => $this->featuredMotors->count(), 'icon' => 'o-truck'],
            ['label' => 'Pelanggan Senang', 'value' => '500+', 'icon' => 'o-users'],
            ['label' => 'Kota Terjangkau', 'value' => '25+', 'icon' => 'o-map-pin'],
            ['label' => 'Tahun Berpengalaman', 'value' => '5+', 'icon' => 'o-star']
        ];
    }
}; ?>

<div class="min-h-screen bg-white">
    {{-- Navigation Bar --}}
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <div class="flex items-center">
                    <x-icon name="o-truck" class="h-8 w-8 text-blue-600 mr-2" />
                    <span class="text-2xl font-bold text-gray-800">RentalMotor</span>
                </div>

                {{-- Desktop Menu --}}
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Beranda</a>
                    <a href="#motors" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Motor</a>
                    <a href="#services" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Layanan</a>
                    <a href="#testimonials" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Testimoni</a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Kontak</a>
                </div>

                {{-- Auth Buttons --}}
                <div class="hidden md:flex items-center space-x-4">
                    <x-button label="Masuk" link="/login" class="btn-outline btn-primary" />
                    <x-button label="Daftar" link="/register" class="btn-primary" />
                </div>

                {{-- Mobile Menu Button --}}
                <div class="md:hidden">
                    <x-button icon="o-bars-3" class="btn-ghost" x-data x-on:click="$refs.mobileMenu.classList.toggle('hidden')" />
                </div>
            </div>

            {{-- Mobile Menu --}}
            <div class="md:hidden hidden" x-ref="mobileMenu">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t">
                    <a href="#home" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Beranda</a>
                    <a href="#motors" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Motor</a>
                    <a href="#services" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Layanan</a>
                    <a href="#testimonials" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Testimoni</a>
                    <a href="#contact" class="block px-3 py-2 text-gray-700 hover:text-blue-600">Kontak</a>
                    <div class="flex space-x-2 px-3 py-2">
                        <x-button label="Masuk" link="/login" class="btn-outline btn-primary btn-sm" />
                        <x-button label="Daftar" link="/register" class="btn-primary btn-sm" />
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section id="home" class="pt-16 bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <h1 class="text-4xl md:text-6xl font-bold text-gray-800 leading-tight mb-6">
                        Sewa Motor
                        <span class="text-blue-600">Mudah & Cepat</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Nikmati perjalanan Anda dengan motor berkualitas, harga terjangkau, dan pelayanan terbaik di kota.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <x-button label="Sewa Sekarang" class="btn-primary btn-lg" icon="o-rocket-launch" />
                        <x-button label="Lihat Motor" class="btn-outline btn-lg" icon="o-eye" />
                    </div>
                </div>
                
                <div class="flex justify-center">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1558618047-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Motor Rental" class="rounded-lg shadow-2xl w-full max-w-lg">
                        <div class="absolute -top-4 -right-4 bg-blue-600 text-white p-4 rounded-full">
                            <x-icon name="o-truck" class="h-8 w-8" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                @foreach($stats as $stat)
                <div class="text-center">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-icon name="{{ $stat['icon'] }}" class="h-8 w-8 text-blue-600" />
                    </div>
                    <div class="text-3xl font-bold text-gray-800 mb-2">{{ $stat['value'] }}</div>
                    <div class="text-gray-600">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Featured Motors Section --}}
    <section id="motors" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Motor Pilihan</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Koleksi motor terbaik dengan berbagai pilihan untuk kebutuhan perjalanan Anda
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($featuredMotors as $motor)
                <x-card class="hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="relative">
                        <img src="{{ $motor->photo ? Storage::url($motor->photo) : 'https://images.unsplash.com/photo-1558618047-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80' }}" 
                             alt="{{ $motor->merk }}" class="w-full h-48 object-cover rounded-t-lg">
                        <div class="absolute top-4 right-4">
                            <x-badge value="{{ $motor->tipe_cc }}" class="badge badge-primary" />
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $motor->merk }}</h3>
                        <p class="text-gray-600 mb-4">{{ $motor->no_plat }}</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Harian</span>
                                <span class="font-bold text-blue-600">Rp {{ number_format($motor->tarif->tarif_harian ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mingguan</span>
                                <span class="font-bold text-green-600">Rp {{ number_format($motor->tarif->tarif_mingguan ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <x-button label="Sewa Motor" class="btn-primary w-full" />
                    </div>
                </x-card>
                @empty
                <div class="col-span-full text-center py-12">
                    <x-icon name="o-truck" class="h-16 w-16 mx-auto text-gray-400 mb-4" />
                    <p class="text-gray-600">Belum ada motor yang tersedia</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Services Section --}}
    <section id="services" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Layanan Kami</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Nikmati berbagai layanan terbaik untuk pengalaman sewa motor yang tak terlupakan
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="text-center p-8 rounded-lg hover:bg-blue-50 transition-colors">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-icon name="o-clock" class="h-8 w-8 text-blue-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">24/7 Layanan</h3>
                    <p class="text-gray-600">Siap melayani Anda kapan saja, setiap hari dalam seminggu</p>
                </div>

                <div class="text-center p-8 rounded-lg hover:bg-green-50 transition-colors">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-icon name="o-shield-check" class="h-8 w-8 text-green-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Motor Terawat</h3>
                    <p class="text-gray-600">Semua motor selalu dalam kondisi prima dan siap pakai</p>
                </div>

                <div class="text-center p-8 rounded-lg hover:bg-purple-50 transition-colors">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-icon name="o-currency-dollar" class="h-8 w-8 text-purple-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Harga Terjangkau</h3>
                    <p class="text-gray-600">Dapatkan harga sewa terbaik dengan kualitas premium</p>
                </div>

                <div class="text-center p-8 rounded-lg hover:bg-red-50 transition-colors">
                    <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-icon name="o-map-pin" class="h-8 w-8 text-red-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Antar Jemput</h3>
                    <p class="text-gray-600">Layanan antar jemput motor ke lokasi Anda</p>
                </div>

                <div class="text-center p-8 rounded-lg hover:bg-yellow-50 transition-colors">
                    <div class="bg-yellow-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-icon name="o-wrench-screwdriver" class="h-8 w-8 text-yellow-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Bantuan Darurat</h3>
                    <p class="text-gray-600">Tim bantuan siap membantu jika terjadi masalah teknis</p>
                </div>

                <div class="text-center p-8 rounded-lg hover:bg-indigo-50 transition-colors">
                    <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-icon name="o-device-phone-mobile" class="h-8 w-8 text-indigo-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Booking Online</h3>
                    <p class="text-gray-600">Pesan motor dengan mudah melalui platform online</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials Section --}}
    <section id="testimonials" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Kata Mereka</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Kepuasan pelanggan adalah prioritas utama kami
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($testimonials as $testimonial)
                <x-card class="text-center">
                    <div class="p-6">
                        <img src="{{ $testimonial['avatar'] }}" alt="{{ $testimonial['name'] }}" 
                             class="w-16 h-16 rounded-full mx-auto mb-4">
                        
                        <div class="flex justify-center mb-4">
                            @for($i = 0; $i < $testimonial['rating']; $i++)
                                <x-icon name="o-star" class="h-5 w-5 text-yellow-400 fill-current" />
                            @endfor
                        </div>
                        
                        <p class="text-gray-600 mb-4 italic">"{{ $testimonial['comment'] }}"</p>
                        <h4 class="text-lg font-bold text-gray-800">{{ $testimonial['name'] }}</h4>
                    </div>
                </x-card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Contact Section --}}
    <section id="contact" class="py-20 bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-4">Siap Memulai Perjalanan?</h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Hubungi kami sekarang dan dapatkan penawaran terbaik untuk rental motor Anda
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="text-white">
                    <x-icon name="o-phone" class="h-8 w-8 mx-auto mb-4" />
                    <h3 class="text-lg font-bold mb-2">Telepon</h3>
                    <p class="text-blue-100">+62 812-3456-7890</p>
                </div>
                
                <div class="text-white">
                    <x-icon name="o-envelope" class="h-8 w-8 mx-auto mb-4" />
                    <h3 class="text-lg font-bold mb-2">Email</h3>
                    <p class="text-blue-100">info@rentalmotor.com</p>
                </div>
                
                <div class="text-white">
                    <x-icon name="o-map-pin" class="h-8 w-8 mx-auto mb-4" />
                    <h3 class="text-lg font-bold mb-2">Alamat</h3>
                    <p class="text-blue-100">Jl. Merdeka No. 123, Jakarta</p>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <x-button label="Hubungi Sekarang" class="btn-white" icon="o-phone" />
                <x-button label="Mulai Sewa" class="btn-outline text-white border-white hover:bg-white hover:text-blue-600" icon="o-rocket-launch" />
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                {{-- Company Info --}}
                <div>
                    <div class="flex items-center mb-4">
                        <x-icon name="o-truck" class="h-8 w-8 text-blue-400 mr-2" />
                        <span class="text-2xl font-bold">RentalMotor</span>
                    </div>
                    <p class="text-gray-300 mb-4">
                        Penyedia jasa rental motor terpercaya dengan layanan terbaik di Indonesia.
                    </p>
                    <div class="flex space-x-4">
                        <x-icon name="o-globe-alt" class="h-6 w-6 text-gray-400 hover:text-blue-400 cursor-pointer" />
                        <x-icon name="o-envelope" class="h-6 w-6 text-gray-400 hover:text-blue-400 cursor-pointer" />
                        <x-icon name="o-phone" class="h-6 w-6 text-gray-400 hover:text-blue-400 cursor-pointer" />
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-gray-300 hover:text-blue-400">Beranda</a></li>
                        <li><a href="#motors" class="text-gray-300 hover:text-blue-400">Daftar Motor</a></li>
                        <li><a href="#services" class="text-gray-300 hover:text-blue-400">Layanan</a></li>
                        <li><a href="#contact" class="text-gray-300 hover:text-blue-400">Kontak</a></li>
                    </ul>
                </div>

                {{-- Services --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Layanan</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-blue-400">Rental Harian</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-blue-400">Rental Mingguan</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-blue-400">Rental Bulanan</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-blue-400">Antar Jemput</a></li>
                    </ul>
                </div>

                {{-- Contact Info --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Kontak</h3>
                    <div class="space-y-2">
                        <p class="text-gray-300 flex items-center">
                            <x-icon name="o-map-pin" class="h-5 w-5 mr-2" />
                            Jl. Merdeka No. 123, Jakarta
                        </p>
                        <p class="text-gray-300 flex items-center">
                            <x-icon name="o-phone" class="h-5 w-5 mr-2" />
                            +62 812-3456-7890
                        </p>
                        <p class="text-gray-300 flex items-center">
                            <x-icon name="o-envelope" class="h-5 w-5 mr-2" />
                            info@rentalmotor.com
                        </p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-300">
                    © {{ date('Y') }} RentalMotor. All rights reserved. | Made with ❤️ for Indonesia
                </p>
            </div>
        </div>
    </footer>
    <script>
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    </script>
</div>

