import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

function money(value, currency) {
    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

export default function Show({ project, members, canViewFinancials }) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <Link
                        href={route('companies.show', project.company.id)}
                        className="text-sm text-blue-700 hover:underline"
                    >
                        {project.company.name}
                    </Link>
                    <div className="mt-1 flex flex-wrap items-center gap-3">
                        <h1 className="text-xl font-semibold leading-tight text-gray-800">
                            {project.name}
                        </h1>
                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium capitalize text-slate-700">
                            {project.status.replaceAll('_', ' ')}
                        </span>
                    </div>
                </div>
            }
        >
            <Head title={project.name} />

            <div className="py-10">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                    <section className="rounded-lg bg-white p-6 shadow-sm lg:col-span-2">
                        <h2 className="text-base font-semibold text-gray-900">
                            Project overview
                        </h2>
                        {project.description && (
                            <p className="mt-3 whitespace-pre-line text-sm text-gray-600">
                                {project.description}
                            </p>
                        )}
                        <dl className="mt-6 grid gap-5 sm:grid-cols-2">
                            <Detail label="Project code" value={project.code} />
                            <Detail label="Client" value={project.client_name} />
                            <Detail label="Location" value={project.location} />
                            <Detail
                                label="Construction type"
                                value={project.construction_type}
                            />
                            <Detail
                                label="Start date"
                                value={project.start_date ?? 'Not set'}
                            />
                            <Detail
                                label="Expected completion"
                                value={
                                    project.expected_completion_date ?? 'Not set'
                                }
                            />
                            {canViewFinancials && (
                                <>
                                    <Detail
                                        label="Original budget"
                                        value={money(
                                            project.original_budget,
                                            project.company.currency,
                                        )}
                                    />
                                    <Detail
                                        label="Approved budget"
                                        value={money(
                                            project.approved_revised_budget,
                                            project.company.currency,
                                        )}
                                    />
                                    <Detail
                                        label="Contract value"
                                        value={
                                            project.contract_value
                                                ? money(
                                                      project.contract_value,
                                                      project.company.currency,
                                                  )
                                                : 'Not set'
                                        }
                                    />
                                    <Detail
                                        label="Payment terms"
                                        value={project.payment_terms}
                                    />
                                </>
                            )}
                        </dl>
                    </section>

                    <section className="rounded-lg bg-white p-6 shadow-sm">
                        <h2 className="text-base font-semibold text-gray-900">
                            Assigned team
                        </h2>
                        {members.length === 0 ? (
                            <p className="mt-4 text-sm text-gray-500">
                                No team members assigned.
                            </p>
                        ) : (
                            <ul className="mt-4 divide-y divide-gray-100">
                                {members.map((member) => (
                                    <li key={member.id} className="py-3">
                                        <p className="text-sm font-medium text-gray-900">
                                            {member.name}
                                        </p>
                                        {member.email && (
                                            <p className="mt-1 text-xs text-gray-500">
                                                {member.email}
                                            </p>
                                        )}
                                        <p className="mt-1 text-xs capitalize text-blue-700">
                                            {member.role.replaceAll('_', ' ')}
                                        </p>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Detail({ label, value }) {
    return (
        <div>
            <dt className="text-sm text-gray-500">{label}</dt>
            <dd className="mt-1 text-sm font-medium text-gray-900">
                {value || 'Not set'}
            </dd>
        </div>
    );
}
