import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create({
    company,
    companyUsers,
    companyOwners,
    currentUserId,
    projectRoles,
}) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        code: '',
        client_name: '',
        location: '',
        construction_type: '',
        description: '',
        start_date: '',
        expected_completion_date: '',
        original_budget: '0.00',
        contract_value: '',
        payment_terms: '',
        owner_id:
            companyOwners.find((owner) => owner.id === currentUserId)?.id ??
            companyOwners[0]?.id ??
            '',
        project_manager_id: '',
        members: [],
        showcase_public: false,
    });

    function setMemberRole(userId, role) {
        const members = data.members.filter((member) => member.user_id !== userId);

        if (role) {
            members.push({ user_id: userId, role });
        }

        setData('members', members);
    }

    function submit(event) {
        event.preventDefault();
        post(route('companies.projects.store', company.id));
    }

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <Link
                        href={route('companies.show', company.id)}
                        className="text-sm text-blue-700 hover:underline"
                    >
                        {company.name}
                    </Link>
                    <h1 className="mt-1 text-xl font-semibold leading-tight text-gray-800">
                        Create project
                    </h1>
                </div>
            }
        >
            <Head title="Create project" />

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
                                <Field
                                    label="Project name"
                                    name="name"
                                    data={data}
                                    setData={setData}
                                    errors={errors}
                                    required
                                />
                                <Field
                                    label="Project code"
                                    name="code"
                                    data={data}
                                    setData={setData}
                                    errors={errors}
                                    required
                                />
                                <Field
                                    label="Client"
                                    name="client_name"
                                    data={data}
                                    setData={setData}
                                    errors={errors}
                                />
                                <Field
                                    label="Location"
                                    name="location"
                                    data={data}
                                    setData={setData}
                                    errors={errors}
                                />
                                <Field
                                    label="Construction type"
                                    name="construction_type"
                                    data={data}
                                    setData={setData}
                                    errors={errors}
                                />
                                <div>
                                    <InputLabel
                                        htmlFor="owner_id"
                                        value="Project owner"
                                    />
                                    <select
                                        id="owner_id"
                                        value={data.owner_id}
                                        onChange={(event) =>
                                            setData('owner_id', event.target.value)
                                        }
                                        required
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="" disabled>
                                            Select a company owner
                                        </option>
                                        {companyOwners.map((owner) => (
                                            <option key={owner.id} value={owner.id}>
                                                {owner.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.owner_id}
                                        className="mt-2"
                                    />
                                </div>
                                <div>
                                    <InputLabel
                                        htmlFor="project_manager_id"
                                        value="Project manager"
                                    />
                                    <select
                                        id="project_manager_id"
                                        value={data.project_manager_id}
                                        onChange={(event) =>
                                            setData(
                                                'project_manager_id',
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">
                                            Assign me as project manager
                                        </option>
                                        {companyUsers
                                            .filter((manager) =>
                                                [
                                                    'company_owner',
                                                    'project_manager',
                                                ].includes(
                                                    manager.company_role,
                                                ),
                                            )
                                            .map((manager) => (
                                                <option
                                                    key={manager.id}
                                                    value={manager.id}
                                                >
                                                    {manager.name} (
                                                    {manager.email})
                                                </option>
                                            ))}
                                    </select>
                                    <InputError
                                        message={errors.project_manager_id}
                                        className="mt-2"
                                    />
                                </div>
                                <div className="sm:col-span-2">
                                    <InputLabel
                                        htmlFor="project_team"
                                        value="Project team"
                                    />
                                    <div
                                        id="project_team"
                                        className="mt-2 divide-y divide-gray-100 rounded-md border border-gray-200"
                                    >
                                        {companyUsers.map((member) => {
                                            const assignment = data.members.find(
                                                (entry) =>
                                                    entry.user_id === member.id,
                                            );

                                            return (
                                                <div
                                                    key={member.id}
                                                    className="flex flex-wrap items-center justify-between gap-3 p-3"
                                                >
                                                    <div>
                                                        <p className="text-sm font-medium text-gray-900">
                                                            {member.name}
                                                        </p>
                                                        <p className="text-xs text-gray-500">
                                                            {member.email}
                                                        </p>
                                                    </div>
                                                    <select
                                                        aria-label={`Project role for ${member.name}`}
                                                        value={
                                                            assignment?.role ??
                                                            ''
                                                        }
                                                        onChange={(event) =>
                                                            setMemberRole(
                                                                member.id,
                                                                event.target
                                                                    .value,
                                                            )
                                                        }
                                                        className="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    >
                                                        <option value="">
                                                            Not assigned
                                                        </option>
                                                        {projectRoles.map(
                                                            (role) => (
                                                                <option
                                                                    key={role.value}
                                                                    value={role.value}
                                                                >
                                                                    {role.label}
                                                                </option>
                                                            ),
                                                        )}
                                                    </select>
                                                </div>
                                            );
                                        })}
                                    </div>
                                    <InputError
                                        message={errors.members}
                                        className="mt-2"
                                    />
                                    {Object.keys(errors)
                                        .filter((key) =>
                                            key.startsWith('members.'),
                                        )
                                        .map((key) => (
                                            <InputError
                                                key={key}
                                                message={errors[key]}
                                                className="mt-2"
                                            />
                                        ))}
                                </div>
                                <div className="sm:col-span-2">
                                    <label className="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4">
                                        <input
                                            type="checkbox"
                                            checked={data.showcase_public}
                                            onChange={(event) =>
                                                setData(
                                                    'showcase_public',
                                                    event.target.checked,
                                                )
                                            }
                                            className="mt-1 rounded border-gray-300 text-blue-700 focus:ring-blue-600"
                                        />
                                        <span>
                                            <span className="block text-sm font-semibold text-gray-900">
                                                Feature this project on the
                                                public homepage
                                            </span>
                                            <span className="mt-1 block text-sm text-gray-600">
                                                The project name, location,
                                                construction type, and status
                                                will be visible to visitors.
                                                Company and client names,
                                                budgets, and team details stay
                                                private.
                                            </span>
                                        </span>
                                    </label>
                                    <InputError
                                        message={errors.showcase_public}
                                        className="mt-2"
                                    />
                                </div>
                                <div className="sm:col-span-2">
                                    <InputLabel
                                        htmlFor="description"
                                        value="Description"
                                    />
                                    <textarea
                                        id="description"
                                        value={data.description}
                                        onChange={(event) =>
                                            setData(
                                                'description',
                                                event.target.value,
                                            )
                                        }
                                        rows="3"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <InputError
                                        message={errors.description}
                                        className="mt-2"
                                    />
                                </div>
                            </div>
                        </section>

                        <section className="border-t border-gray-200 pt-6">
                            <h2 className="text-base font-semibold text-gray-900">
                                Dates and contract
                            </h2>
                            <div className="mt-5 grid gap-5 sm:grid-cols-2">
                                <Field
                                    label="Start date"
                                    name="start_date"
                                    type="date"
                                    data={data}
                                    setData={setData}
                                    errors={errors}
                                />
                                <Field
                                    label="Expected completion"
                                    name="expected_completion_date"
                                    type="date"
                                    data={data}
                                    setData={setData}
                                    errors={errors}
                                />
                                <Field
                                    label={`Original budget (${company.currency})`}
                                    name="original_budget"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    data={data}
                                    setData={setData}
                                    errors={errors}
                                    required
                                />
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
                                <div className="sm:col-span-2">
                                    <InputLabel
                                        htmlFor="payment_terms"
                                        value="Payment terms"
                                    />
                                    <textarea
                                        id="payment_terms"
                                        value={data.payment_terms}
                                        onChange={(event) =>
                                            setData(
                                                'payment_terms',
                                                event.target.value,
                                            )
                                        }
                                        rows="2"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <InputError
                                        message={errors.payment_terms}
                                        className="mt-2"
                                    />
                                </div>
                            </div>
                        </section>

                        <div className="flex justify-end">
                            <PrimaryButton disabled={processing}>
                                Create project
                            </PrimaryButton>
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
