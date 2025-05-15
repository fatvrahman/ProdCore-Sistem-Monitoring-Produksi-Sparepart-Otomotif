import React from 'react';

export default function DashboardOperator({ auth }) {
    return (
        <div className="p-8">
            <h2 className="text-2xl font-bold mb-4">Dashboard Operator</h2>
            <p>Selamat datang, {auth.user.name}!</p>
            <div className="mt-6">
                {/* Contoh: Form input data produksi harian */}
                <h4 className="font-semibold mb-2">Input Data Produksi</h4>
                <form>
                    <div>
                        <label>Jumlah Unit:</label>
                        <input type="number" className="border p-1 rounded ml-2"/>
                    </div>
                    <div className="mt-2">
                        <label>Shift:</label>
                        <select className="border p-1 rounded ml-2">
                            <option>Pagi</option>
                            <option>Siang</option>
                            <option>Malam</option>
                        </select>
                    </div>
                    <button type="submit" className="mt-4 bg-blue-500 text-white px-4 py-1 rounded">Submit</button>
                </form>
            </div>
        </div>
    );
}
