import React from 'react';

export default function DashboardQC({ auth }) {
    return (
        <div className="p-8">
            <h2 className="text-2xl font-bold mb-4">Dashboard Quality Control</h2>
            <p>Selamat datang, {auth.user.name}!</p>
            <div className="mt-6">
                {/* Contoh: Input hasil inspeksi */}
                <h4 className="font-semibold mb-2">Input Hasil Inspeksi</h4>
                <form>
                    <div>
                        <label>Barang Layak:</label>
                        <input type="number" className="border p-1 rounded ml-2"/>
                    </div>
                    <div className="mt-2">
                        <label>Barang Rusak:</label>
                        <input type="number" className="border p-1 rounded ml-2"/>
                    </div>
                    <button type="submit" className="mt-4 bg-green-500 text-white px-4 py-1 rounded">Submit</button>
                </form>
            </div>
        </div>
    );
}
