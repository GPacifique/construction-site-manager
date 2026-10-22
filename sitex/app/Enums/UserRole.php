<?php

namespace App\Enums;

enum UserRole: string
{
    case SystemAdministrator = 'system_admin';
    case CompanyOwner = 'company_owner';
    case ProjectManager = 'project_manager';
    case CivilEngineer = 'civil_engineer';
    case SiteForeman = 'site_foreman';
    case Storekeeper = 'storekeeper';
    case ProcurementOfficer = 'procurement_officer';
    case Accountant = 'accountant';
    case Worker = 'worker';
    case Client = 'client';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<string>
     */
    public static function projectValues(): array
    {
        return array_values(array_diff(self::values(), [
            self::SystemAdministrator->value,
            self::CompanyOwner->value,
        ]));
    }

    public function label(): string
    {
        return match ($this) {
            self::SystemAdministrator => 'System Administrator',
            self::CompanyOwner => 'Company Owner',
            self::ProjectManager => 'Project Manager',
            self::CivilEngineer => 'Civil Engineer / Site Engineer',
            self::SiteForeman => 'Site Foreman',
            self::Storekeeper => 'Storekeeper',
            self::ProcurementOfficer => 'Procurement Officer',
            self::Accountant => 'Accountant / Finance Officer',
            self::Worker => 'Worker / Contractor',
            self::Client => 'Client / Project Owner',
        };
    }

    public function allows(ProjectPermission $permission): bool
    {
        return in_array($permission, match ($this) {
            self::SystemAdministrator => [],
            self::CompanyOwner => ProjectPermission::cases(),
            self::ProjectManager => [
                ProjectPermission::ViewProject,
                ProjectPermission::ManageProject,
                ProjectPermission::ManageTeam,
                ProjectPermission::ManageBoq,
                ProjectPermission::RecordSiteActivity,
                ProjectPermission::ManageInventory,
                ProjectPermission::ManageProcurement,
                ProjectPermission::ViewFinancials,
            ],
            self::CivilEngineer => [
                ProjectPermission::ViewProject,
                ProjectPermission::ManageBoq,
            ],
            self::SiteForeman => [
                ProjectPermission::ViewProject,
                ProjectPermission::RecordSiteActivity,
            ],
            self::Storekeeper => [
                ProjectPermission::ViewProject,
                ProjectPermission::ManageInventory,
            ],
            self::ProcurementOfficer => [
                ProjectPermission::ViewProject,
                ProjectPermission::ManageProcurement,
            ],
            self::Accountant => [
                ProjectPermission::ViewProject,
                ProjectPermission::ManageFinance,
                ProjectPermission::ViewFinancials,
            ],
            self::Worker, self::Client => [
                ProjectPermission::ViewProject,
            ],
        }, true);
    }
}
