import React from 'react';

export default function DashboardGudang({ auth }) {
    return (
        <div className="p-8">
            <h2 className="text-2xl font-bold mb-4">Dashboard Gudang & Distribusi</h2>
            <p>Selamat datang, {auth.user.name}!</p>
            <div className="mt-6">
                {/* Contoh: Monitoring stok bahan baku */}
                <h4 className="font-semibold mb-2">Monitoring Stok Bahan Baku</h4>
                <table className="table-auto border-collapse border w-full">
                    <thead>
                        <tr>
                            <th className="border p-2">Nama Bahan</th>
                            <th className="border p-2">Saldo Awal</th>
                            <th className="border p-2">Pemakaian</th>
                            <th className="border p-2">Stok Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td className="border p-2">Besi</td>
                            <td className="border p-2">100</td>
                            <td className="border p-2">20</td>
                            <td className="border p-2">80</td>
                        </tr>
                        {/* Tambah baris data sesuai kebutuhan */}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
