import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

import DashboardOperator from './DashboardOperator';
import DashboardQC from './DashboardQC';
import DashboardGudang from './DashboardGudang';
import DashboardAdmin from './DashboardAdmin';

export default function Dashboard({ auth }) {
    function renderDashboardByRole(role) {
        switch (role) {
            case 'operator':
                return <DashboardOperator auth={auth} />;
            case 'qc':
                return <DashboardQC auth={auth} />;
            case 'gudang':
                return <DashboardGudang auth={auth} />;
            case 'admin':
                return <DashboardAdmin auth={auth} />;
            default:
                return <div>Anda tidak diizinkan masuk ke halaman ini.</div>;
        }
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            {renderDashboardByRole(auth.user.role)}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
