<?php

namespace App\Enums;

enum ProjectPermission: string
{
    case ViewProject = 'view_project';
    case ManageProject = 'manage_project';
    case ManageTeam = 'manage_team';
    case ManageBoq = 'manage_boq';
    case RecordSiteActivity = 'record_site_activity';
    case ManageInventory = 'manage_inventory';
    case ManageProcurement = 'manage_procurement';
    case ManageFinance = 'manage_finance';
    case ViewFinancials = 'view_financials';
    case ApproveBudget = 'approve_budget';
}
