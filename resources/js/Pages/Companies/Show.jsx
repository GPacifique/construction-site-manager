import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const statusStyles = {
    planning: 'bg-slate-100 text-slate-700',
    active: 'bg-green-100 text-green-700',
    on_hold: 'bg-amber-100 text-amber-800',
    completed: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-red-100 text-red-700',
};

const memberRoles = [
    ['project_manager', 'Project Manager'],
    ['civil_engineer', 'Civil Engineer / Site Engineer'],
    ['site_foreman', 'Site Foreman'],
    ['storekeeper', 'Storekeeper'],
    ['procurement_officer', 'Procurement Officer'],
    ['accountant', 'Accountant / Finance Officer'],
    ['worker', 'Worker / Contractor'],
    ['client', 'Client / Project Owner'],
];

export default function Show({
    company,
    projects,
    members,
    canCreateProject,
    canManage,
}) {
    const { flash } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        role: 'civil_engineer',
    });

    function addMember(event) {
        event.preventDefault();
        post(route('companies.members.store', company.id), {
            onSuccess: () => reset('email'),
        });
    }

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <Link
                            href={route('companies.index')}
                            className="text-sm text-blue-700 hover:underline"
                        >
                            Companies
                        </Link>
                        <h1 className="mt-1 text-xl font-semibold leading-tight text-gray-800">
                            {company.name}
                        </h1>
                    </div>
                    {canCreateProject && (
                        <Link
                            href={route(
                                'companies.projects.create',
                                company.id,
                            )}
                            className="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800"
                        >
                            Create project
                        </Link>
                    )}
                </div>
            }
        >
            <Head title={company.name} />

            <div className="py-10">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div
                            role="status"
                            className="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-800"
                        >
                            {flash.success}
                        </div>
                    )}
                    {projects.length === 0 ? (
                        <div className="rounded-lg bg-white p-8 text-center shadow-sm">
                            <h2 className="text-lg font-semibold text-gray-900">
                                No projects yet
                            </h2>
                            <p className="mt-2 text-sm text-gray-600">
                                Projects you are authorized to access will
                                appear here.
                            </p>
                        </div>
                    ) : (
                        <div className="overflow-hidden rounded-lg bg-white shadow-sm">
                            <ul className="divide-y divide-gray-200">
                                {projects.map((project) => (
                                    <li key={project.id}>
                                        <Link
                                            href={route(
                                                'projects.show',
                                                project.id,
                                            )}
                                            className="flex flex-wrap items-center justify-between gap-4 p-5 hover:bg-gray-50"
                                        >
                                            <div>
                                                <p className="font-semibold text-gray-900">
                                                    {project.name}
                                                </p>
                                                <p className="mt-1 text-sm text-gray-500">
                                                    {project.code}
                                                    {project.location
                                                        ? ` · ${project.location}`
                                                        : ''}
                                                </p>
                                            </div>
                                            <span
                                                className={`rounded-full px-3 py-1 text-xs font-medium ${statusStyles[project.status] ?? statusStyles.planning}`}
                                            >
                                                {project.status.replaceAll(
                                                    '_',
                                                    ' ',
                                                )}
                                            </span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <section className="mt-8 rounded-lg bg-white p-6 shadow-sm">
                        <div className="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 className="text-base font-semibold text-gray-900">
                                    Company team
                                </h2>
                                <p className="mt-1 text-sm text-gray-500">
                                    Members must have a verified account before
                                    they can be added.
                                </p>
                            </div>
                        </div>

                        {canManage && (
                            <form
                                onSubmit={addMember}
                                className="mt-5 grid gap-4 border-b border-gray-200 pb-6 sm:grid-cols-[1fr_1fr_auto]"
                            >
                                <div>
                                    <InputLabel
                                        htmlFor="member_email"
                                        value="Registered user email"
                                    />
                                    <TextInput
                                        id="member_email"
                                        type="email"
                                        value={data.email}
                                        onChange={(event) =>
                                            setData('email', event.target.value)
                                        }
                                        className="mt-1 block w-full"
                                        required
                                    />
                                    <InputError
                                        message={errors.email}
                                        className="mt-2"
                                    />
                                </div>
                                <div>
                                    <InputLabel
                                        htmlFor="member_role"
                                        value="Company role"
                                    />
                                    <select
                                        id="member_role"
                                        value={data.role}
                                        onChange={(event) =>
                                            setData('role', event.target.value)
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        {memberRoles.map(([value, label]) => (
                                            <option key={value} value={value}>
                                                {label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.role}
                                        className="mt-2"
                                    />
                                </div>
                                <div className="self-end">
                                    <PrimaryButton disabled={processing}>
                                        Add member
                                    </PrimaryButton>
                                </div>
                            </form>
                        )}

                        {members.length === 0 ? (
                            <p className="mt-5 text-sm text-gray-500">
                                No active company members.
                            </p>
                        ) : (
                            <ul className="mt-4 divide-y divide-gray-100">
                                {members.map((member) => (
                                    <li
                                        key={member.id}
                                        className="flex flex-wrap justify-between gap-3 py-3"
                                    >
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">
                                                {member.name}
                                            </p>
                                            <p className="mt-1 text-xs text-gray-500">
                                                {member.email}
                                            </p>
                                        </div>
                                        <p className="text-sm capitalize text-gray-600">
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
