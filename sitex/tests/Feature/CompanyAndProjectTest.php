<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class CompanyAndProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_company_with_an_active_owner_membership(): void
    {
        $this->post('/register', [
            'name' => 'Aline Uwase',
            'email' => 'aline@example.com',
            'company_name' => 'Build Rwanda',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'aline@example.com')->firstOrFail();
        $company = Company::query()->where('name', 'Build Rwanda')->firstOrFail();

        $this->assertDatabaseHas('company_user', [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => UserRole::CompanyOwner->value,
            'status' => 'active',
        ]);
    }

    public function test_company_owner_can_create_a_project_and_becomes_its_manager(): void
    {
        [$user, $company] = $this->makeCompanyOwner();
        $engineer = User::factory()->create();
        $company->users()->attach($engineer->id, [
            'role' => UserRole::CivilEngineer->value,
            'status' => 'active',
        ]);

        $this->actingAs($user)->post(route('companies.projects.store', $company), [
            'name' => 'Kigali Office',
            'code' => 'KGL-001',
            'client_name' => 'Example Client',
            'location' => 'Kigali',
            'original_budget' => '1250000.00',
            'members' => [
                [
                    'user_id' => $engineer->id,
                    'role' => UserRole::CivilEngineer->value,
                ],
            ],
        ])->assertRedirect();

        $project = Project::query()->where('code', 'KGL-001')->firstOrFail();

        $this->assertSame('0.00', $project->approved_revised_budget);
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role' => UserRole::ProjectManager->value,
        ]);
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $engineer->id,
            'role' => UserRole::CivilEngineer->value,
        ]);
    }

    public function test_project_creation_page_receives_a_json_list_of_project_roles(): void
    {
        [$owner, $company] = $this->makeCompanyOwner();

        $this->actingAs($owner)
            ->get(route('companies.projects.create', $company))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Projects/Create')
                ->has('projectRoles', count(UserRole::projectValues()))
                ->where('projectRoles.0.value', UserRole::ProjectManager->value));
    }

    public function test_each_project_role_is_limited_to_its_authorized_workstream(): void
    {
        [, $company] = $this->makeCompanyOwner();
        $project = $company->projects()->create([
            'name' => 'Role Test Site',
            'code' => 'ROLE-001',
            'original_budget' => 1000,
            'approved_revised_budget' => 1000,
            'status' => 'planning',
        ]);

        $allowedAbilities = [
            UserRole::CompanyOwner->value => [
                'view',
                'update',
                'delete',
                'manageTeam',
                'manageBoq',
                'recordSiteActivity',
                'manageInventory',
                'manageProcurement',
                'manageFinance',
                'viewFinancials',
                'approveBudget',
            ],
            UserRole::ProjectManager->value => [
                'view',
                'update',
                'manageTeam',
                'manageBoq',
                'recordSiteActivity',
                'manageInventory',
                'manageProcurement',
                'viewFinancials',
            ],
            UserRole::CivilEngineer->value => ['view', 'manageBoq'],
            UserRole::SiteForeman->value => ['view', 'recordSiteActivity'],
            UserRole::Storekeeper->value => ['view', 'manageInventory'],
            UserRole::ProcurementOfficer->value => ['view', 'manageProcurement'],
            UserRole::Accountant->value => ['view', 'manageFinance', 'viewFinancials'],
            UserRole::Worker->value => ['view'],
            UserRole::Client->value => ['view'],
        ];

        foreach ($allowedAbilities as $role => $allowed) {
            $user = User::factory()->create();
            $company->users()->attach($user->id, [
                'role' => $role,
                'status' => 'active',
            ]);
            $project->members()->attach($user->id, ['role' => $role]);

            foreach ([
                'view',
                'update',
                'delete',
                'manageTeam',
                'manageBoq',
                'recordSiteActivity',
                'manageInventory',
                'manageProcurement',
                'manageFinance',
                'viewFinancials',
                'approveBudget',
            ] as $ability) {
                $this->assertSame(
                    in_array($ability, $allowed, true),
                    $user->can($ability, $project),
                    "{$role} authorization mismatch for {$ability}.",
                );
            }
        }
    }

    public function test_project_financials_and_team_emails_are_hidden_from_workers(): void
    {
        [, $company] = $this->makeCompanyOwner();
        $worker = User::factory()->create();
        $company->users()->attach($worker->id, [
            'role' => UserRole::Worker->value,
            'status' => 'active',
        ]);
        $project = $company->projects()->create([
            'name' => 'Worker View Site',
            'code' => 'WORKER-001',
            'original_budget' => 1000,
            'approved_revised_budget' => 900,
            'contract_value' => 1200,
            'payment_terms' => 'Private payment terms',
            'status' => 'active',
        ]);
        $project->members()->attach($worker->id, [
            'role' => UserRole::Worker->value,
        ]);

        $this->actingAs($worker)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Projects/Show')
                ->where('canViewFinancials', false)
                ->missing('project.original_budget')
                ->missing('project.approved_revised_budget')
                ->missing('project.contract_value')
                ->missing('project.payment_terms')
                ->where('members.0.email', null));
    }

    public function test_only_company_owners_can_enable_public_project_showcase(): void
    {
        [, $company] = $this->makeCompanyOwner();
        $manager = User::factory()->create();
        $company->users()->attach($manager->id, [
            'role' => UserRole::ProjectManager->value,
            'status' => 'active',
        ]);

        $this->actingAs($manager)
            ->from(route('companies.projects.create', $company))
            ->post(route('companies.projects.store', $company), [
                'name' => 'Manager Site',
                'code' => 'MGR-001',
                'original_budget' => '100.00',
                'showcase_public' => true,
            ])
            ->assertSessionHasErrors('showcase_public');
    }

    public function test_users_cannot_view_another_companys_project(): void
    {
        [$owner, $company] = $this->makeCompanyOwner();
        $project = $company->projects()->create([
            'name' => 'Restricted Site',
            'code' => 'RST-001',
            'original_budget' => 1000,
            'approved_revised_budget' => 1000,
            'status' => 'planning',
        ]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    public function test_system_administrator_has_platform_access_without_company_membership(): void
    {
        [, $company] = $this->makeCompanyOwner();
        $project = $company->projects()->create([
            'name' => 'Admin Site',
            'code' => 'ADMIN-001',
            'original_budget' => 1000,
            'approved_revised_budget' => 900,
            'status' => 'planning',
        ]);
        $admin = User::factory()->create();
        $admin->forceFill(['system_role' => UserRole::SystemAdministrator->value])->save();

        $this->actingAs($admin)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Projects/Show')
                ->where('canViewFinancials', true));

        $this->get(route('companies.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Companies/Index')
                ->has('companies', 1)
                ->where('companies.0.role', 'system_admin'));
    }

    public function test_homepage_only_includes_projects_explicitly_shared_by_their_owner(): void
    {
        [, $company] = $this->makeCompanyOwner();
        $privateProject = $company->projects()->create([
            'name' => 'Private Site',
            'code' => 'PRIVATE-001',
            'original_budget' => 1000,
            'approved_revised_budget' => 1000,
            'status' => 'active',
        ]);
        $publicProject = $company->projects()->create([
            'name' => 'Featured Site',
            'code' => 'PUBLIC-001',
            'location' => 'Kigali',
            'original_budget' => 2000,
            'approved_revised_budget' => 2000,
            'status' => 'active',
            'showcase_public' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Welcome')
                ->has('featuredProjects', 1)
                ->where('featuredProjects.0.id', $publicProject->id)
                ->where('featuredProjects.0.name', 'Featured Site')
                ->missing('featuredProjects.0.company'));

        $this->assertDatabaseHas('projects', [
            'id' => $privateProject->id,
            'showcase_public' => false,
        ]);
    }

    public function test_company_project_manager_cannot_view_unassigned_projects(): void
    {
        [, $company] = $this->makeCompanyOwner();
        $manager = User::factory()->create();
        $company->users()->attach($manager->id, [
            'role' => UserRole::ProjectManager->value,
            'status' => 'active',
        ]);
        $project = $company->projects()->create([
            'name' => 'Assigned Site',
            'code' => 'ASN-001',
            'original_budget' => 1000,
            'approved_revised_budget' => 1000,
            'status' => 'planning',
        ]);

        $this->actingAs($manager)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    }

    public function test_company_owner_can_add_a_verified_user_with_a_company_role(): void
    {
        [$owner, $company] = $this->makeCompanyOwner();
        $member = User::factory()->create([
            'email' => 'engineer@example.com',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($owner)
            ->post(route('companies.members.store', $company), [
                'email' => $member->email,
                'role' => UserRole::CivilEngineer->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('company_user', [
            'company_id' => $company->id,
            'user_id' => $member->id,
            'role' => UserRole::CivilEngineer->value,
            'status' => 'active',
        ]);
    }

    public function test_non_owner_cannot_add_a_company_member(): void
    {
        [$owner, $company] = $this->makeCompanyOwner();
        $member = User::factory()->create();
        $company->users()->attach($member->id, [
            'role' => UserRole::CivilEngineer->value,
            'status' => 'active',
        ]);
        $invitee = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($member)
            ->post(route('companies.members.store', $company), [
                'email' => $invitee->email,
                'role' => UserRole::Worker->value,
            ])
            ->assertForbidden();
    }

    public function test_project_manager_selection_must_be_an_active_company_owner_or_manager(): void
    {
        [$owner, $company] = $this->makeCompanyOwner();
        $worker = User::factory()->create();
        $company->users()->attach($worker->id, [
            'role' => UserRole::Worker->value,
            'status' => 'active',
        ]);

        $this->actingAs($owner)->from(route('companies.projects.create', $company))
            ->post(route('companies.projects.store', $company), [
                'name' => 'Invalid Manager Project',
                'code' => 'BAD-001',
                'original_budget' => '100.00',
                'project_manager_id' => $worker->id,
            ])
            ->assertSessionHasErrors('project_manager_id');
    }

    /**
     * @return array{User, Company}
     */
    private function makeCompanyOwner(): array
    {
        $owner = User::factory()->create();
        $company = Company::query()->create([
            'name' => 'Build Rwanda',
            'slug' => 'build-rwanda-'.uniqid(),
            'created_by' => $owner->id,
        ]);
        $company->users()->attach($owner->id, [
            'role' => UserRole::CompanyOwner->value,
            'status' => 'active',
        ]);

        return [$owner, $company];
    }
}
