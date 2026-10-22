import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Edit({
    project,
    company,
    owners,
    projectManagers,
    statuses,
    canManageCompany,
    canManageFinancials,
}) {
    const { data, setData, put, processing, errors } = useForm({
        name: project.name ?? '',
        code: project.code ?? '',
        client_name: project.client_name ?? '',
        location: project.location ?? '',
        construction_type: project.construction_type ?? '',
        description: project.description ?? '',
        start_date: project.start_date ?? '',
        expected_completion_date: project.expected_completion_date ?? '',
        status: project.status,
        owner_id: project.owner_id ?? '',
        project_manager_id: project.project_manager_id ?? '',
        ...(canManageFinancials
            ? {
                  contract_value: project.contract_value ?? '',
                  payment_terms: project.payment_terms ?? '',
              }
            : {}),
        ...(canManageCompany
            ? { showcase_public: project.showcase_public ?? false }
            : {}),
    });

    function submit(event) {
        event.preventDefault();
        put(route('projects.update', project.id));
    }

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <Link
                        href={route('projects.show', project.id)}
                        className="text-sm text-blue-700 hover:underline"
                    >
                        {project.name}
                    </Link>
                    <h1 className="mt-1 text-xl font-semibold leading-tight text-gray-800">
                        Edit project
                    </h1>
                </div>
            }
        >
            <Head title={`Edit ${project.name}`} />

            <div className="py-10">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <form
                        onSubmit={submit}
                        className="space-y-8 rounded-lg bg-white p-6 shadow-sm sm:p-8"
                    >
                        <section>
                            <h2 className="text-base font-semibold text-gray-900">
                                Project details
                            </h2>
                            <div className="mt-5 grid gap-5 sm:grid-cols-2">
                                <Field label="Project name" name="name" data={data} setData={setData} errors={errors} required />
                                <Field label="Project code" name="code" data={data} setData={setData} errors={errors} required />
                                <Field label="Client" name="client_name" data={data} setData={setData} errors={errors} />
                                <Field label="Location" name="location" data={data} setData={setData} errors={errors} />
                                <Field label="Construction type" name="construction_type" data={data} setData={setData} errors={errors} />
                                <div>
                                    <InputLabel htmlFor="status" value="Project status" />
                                    <select
                                        id="status"
                                        value={data.status}
                                        onChange={(event) => setData('status', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        {statuses.map((status) => (
                                            <option key={status.value} value={status.value}>
                                                {status.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.status} className="mt-2" />
                                </div>
                                {canManageCompany && (
                                    <div>
                                        <InputLabel htmlFor="owner_id" value="Project owner" />
                                        <select
                                            id="owner_id"
                                            value={data.owner_id}
                                            onChange={(event) => setData('owner_id', event.target.value)}
                                            required
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="" disabled>Select a company owner</option>
                                            {owners.map((owner) => (
                                                <option key={owner.id} value={owner.id}>{owner.name}</option>
                                            ))}
                                        </select>
                                        <InputError message={errors.owner_id} className="mt-2" />
                                    </div>
                                )}
                                <div>
                                    <InputLabel htmlFor="project_manager_id" value="Project manager" />
                                    <select
                                        id="project_manager_id"
                                        value={data.project_manager_id}
                                        onChange={(event) => setData('project_manager_id', event.target.value)}
                                        required
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="" disabled>Select a company owner or project manager</option>
                                        {projectManagers.map((manager) => (
                                            <option key={manager.id} value={manager.id}>{manager.name}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.project_manager_id} className="mt-2" />
                                </div>
                                <Field label="Start date" name="start_date" type="date" data={data} setData={setData} errors={errors} />
                                <Field label="Expected completion" name="expected_completion_date" type="date" data={data} setData={setData} errors={errors} />
                                <div className="sm:col-span-2">
                                    <InputLabel htmlFor="description" value="Description" />
                                    <textarea
                                        id="description"
                                        value={data.description}
                                        onChange={(event) => setData('description', event.target.value)}
                                        rows="4"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <InputError message={errors.description} className="mt-2" />
                                </div>
                            </div>
                        </section>

                        {canManageFinancials && (
                            <section className="border-t border-gray-200 pt-6">
                                <h2 className="text-base font-semibold text-gray-900">
                                    Contract terms
                                </h2>
                                <p className="mt-1 text-sm text-gray-500">
                                    Budget values are displayed on the project overview and are not changed here.
                                </p>
                                <div className="mt-5 grid gap-5 sm:grid-cols-2">
                                    <Field
                                        label={`Contract value (${company.currency})`}
                                        name="contract_value"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        data={data}
                                        setData={setData}
                                        errors={errors}
                                    />
                                    <div>
                                        <InputLabel htmlFor="payment_terms" value="Payment terms" />
                                        <textarea
                                            id="payment_terms"
                                            value={data.payment_terms}
                                            onChange={(event) => setData('payment_terms', event.target.value)}
                                            rows="2"
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <InputError message={errors.payment_terms} className="mt-2" />
                                    </div>
                                </div>
                            </section>
                        )}

                        {canManageCompany && (
                            <section className="border-t border-gray-200 pt-6">
                                <label className="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        checked={data.showcase_public}
                                        onChange={(event) => setData('showcase_public', event.target.checked)}
                                        className="mt-1 rounded border-gray-300 text-blue-700 focus:ring-blue-600"
                                    />
                                    <span className="text-sm text-gray-700">
                                        Feature this project on the public homepage. Only the project name, location, construction type, and status are shared.
                                    </span>
                                </label>
                                <InputError message={errors.showcase_public} className="mt-2" />
                            </section>
                        )}

                        <div className="flex justify-end">
                            <PrimaryButton disabled={processing}>Save project</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Field({
    label,
    name,
    data,
    setData,
    errors,
    type = 'text',
    required = false,
    ...attributes
}) {
    return (
        <div>
            <InputLabel htmlFor={name} value={label} />
            <TextInput
                id={name}
                name={name}
                type={type}
                value={data[name]}
                onChange={(event) => setData(name, event.target.value)}
                className="mt-1 block w-full"
                required={required}
                {...attributes}
            />
            <InputError message={errors[name]} className="mt-2" />
        </div>
    );
}
