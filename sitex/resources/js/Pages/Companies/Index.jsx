import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Index({ companies }) {
    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-xl font-semibold leading-tight text-gray-800">
                    Your companies
                </h1>
            }
        >
            <Head title="Companies" />

            <div className="py-10">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {companies.length === 0 ? (
                        <div className="rounded-lg bg-white p-8 text-center shadow-sm">
                            <h2 className="text-lg font-semibold text-gray-900">
                                No company memberships
                            </h2>
                            <p className="mt-2 text-sm text-gray-600">
                                Ask a company owner to invite you to their
                                company.
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                            {companies.map((company) => (
                                <Link
                                    key={company.id}
                                    href={route('companies.show', company.id)}
                                    className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:border-blue-300 hover:shadow"
                                >
                                    <div className="flex items-start justify-between gap-4">
                                        <h2 className="text-lg font-semibold text-gray-900">
                                            {company.name}
                                        </h2>
                                        <span className="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium capitalize text-blue-700">
                                            {company.role.replaceAll('_', ' ')}
                                        </span>
                                    </div>
                                    <dl className="mt-6 grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <dt className="text-gray-500">
                                                Projects
                                            </dt>
                                            <dd className="mt-1 font-semibold text-gray-900">
                                                {company.projects_count}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-gray-500">
                                                Currency
                                            </dt>
                                            <dd className="mt-1 font-semibold text-gray-900">
                                                {company.currency}
                                            </dd>
                                        </div>
                                    </dl>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
