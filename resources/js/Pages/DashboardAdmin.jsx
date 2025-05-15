import React from 'react';

export default function DashboardAdmin({ auth }) {
    return (
        <div className="p-8">
            <h2 className="text-2xl font-bold mb-4">Dashboard Admin / Manager</h2>
            <p>Selamat datang, {auth.user.name}!</p>
            <div className="mt-6">
                {/* Contoh: Statistik Produksi */}
                <h4 className="font-semibold mb-2">Statistik Produksi</h4>
                <ul className="list-disc pl-6">
                    <li>Tren Produksi Harian</li>
                    <li>Efisiensi Mesin</li>
                    <li>Perbandingan Output per Lini</li>
                </ul>
                {/* Tambahkan grafik atau tabel real data nanti */}
            </div>
            <div className="mt-6">
                <h4 className="font-semibold mb-2">Master Data & Validasi</h4>
                <ul className="list-disc pl-6">
                    <li>Manajemen User</li>
                    <li>Jenis Produk</li>
                    <li>Lini Produksi</li>
                    <li>Bahan Baku</li>
                    {/* dst. */}
                </ul>
            </div>
        </div>
    );
}
