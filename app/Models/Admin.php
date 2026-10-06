<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'Super Admin';
    public const ROLE_MANAGEMENT = 'Management';
    public const ROLE_PRODUCT_MANAGER = 'Product Manager';
    public const ROLE_HEALTH_REVIEWER = 'Health Content Reviewer';
    public const ROLE_SUBMISSION_REVIEWER = 'Submission Reviewer';
    public const ROLE_MARKETING_MANAGER = 'Marketing Manager';
    public const ROLE_SUPPORT_VIEWER = 'Support Viewer';

    public const ALL_ROLES = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_MANAGEMENT,
        self::ROLE_PRODUCT_MANAGER,
        self::ROLE_HEALTH_REVIEWER,
        self::ROLE_SUBMISSION_REVIEWER,
        self::ROLE_MARKETING_MANAGER,
        self::ROLE_SUPPORT_VIEWER,
    ];

    public const MATRIX_ACTIONS = [
        'view' => 'VIEW',
        'create' => 'CREATE',
        'edit_own' => 'EDIT OWN',
        'edit_all' => 'EDIT ALL',
        'delete' => 'DELETE',
        'assign' => 'ASSIGN',
        'link' => 'LINK',
        'export' => 'EXPORT',
    ];

    public const MATRIX_MODULES = [
        'dashboard' => [
            'name' => 'Dashboard',
            'category' => 'General',
        ],
        'report' => [
            'name' => 'Report',
            'category' => 'General',
        ],
        'notifications' => [
            'name' => 'Notifications',
            'category' => 'General',
        ],
        'clients' => [
            'name' => 'Clients',
            'category' => 'Commercial',
        ],
        'inquiries' => [
            'name' => 'Inquiries',
            'category' => 'Commercial',
        ],
        'orders' => [
            'name' => 'Orders',
            'category' => 'Commercial',
        ],
        'products' => [
            'name' => 'Products',
            'category' => 'Catalogue',
        ],
        'product_categories' => [
            'name' => 'Product Categories',
            'category' => 'Catalogue',
        ],
        'health_intelligence' => [
            'name' => 'Health Intelligence',
            'category' => 'Health & Content',
        ],
        'health_concerns' => [
            'name' => 'Health Concerns',
            'category' => 'Health & Content',
        ],
        'personalized_alerts' => [
            'name' => 'Personalized Alerts',
            'category' => 'Health & Content',
        ],
        'ads' => [
            'name' => 'Ads & Promotions',
            'category' => 'Marketing',
        ],
        'content' => [
            'name' => 'App Content',
            'category' => 'Marketing',
        ],
        'app_control' => [
            'name' => 'App Control',
            'category' => 'Operations',
        ],
        'security' => [
            'name' => 'Admin & Security',
            'category' => 'Operations',
        ],
    ];

    public const ROLE_DESCRIPTIONS = [
        self::ROLE_SUPER_ADMIN => 'Full administrative access: System owner with permanent unalterable access to all modules and configurations.',
        self::ROLE_MANAGEMENT => 'Organization-wide visibility and exception management.',
        self::ROLE_PRODUCT_MANAGER => 'Catalog and master taxonomies: Add/edit/delete products, nutrition declaration, verification queue, and master data.',
        self::ROLE_HEALTH_REVIEWER => 'Health intelligence and clinical rules: Health scoring algorithms, nutritional thresholds, and personalized alerts.',
        self::ROLE_SUBMISSION_REVIEWER => 'Crowd-sourced review operations: Review OCR-extracted submissions, verify packaging photos, and resolve product corrections.',
        self::ROLE_MARKETING_MANAGER => 'Growth and engagement: Sponsored ad campaigns, banner placements, dynamic mobile app content, and push notifications.',
        self::ROLE_SUPPORT_VIEWER => 'Strictly read-only access: View products, user profiles, and contribution ticket status without create/edit/delete permissions.',
    ];

    /**
     * Role to granular permissions matrix.
     */
    public const ROLE_PERMISSIONS = [
        self::ROLE_SUPER_ADMIN => [
            '*',
        ],
        self::ROLE_PRODUCT_MANAGER => [
            'dashboard.view',
            'products.view',
            'products.manage',
            'duplicates.manage',
            'master_data.manage',
            'submissions.view',
            'corrections.view',
            'users.view',
        ],
        self::ROLE_HEALTH_REVIEWER => [
            'dashboard.view',
            'health.view',
            'health.manage',
            'products.view',
            'master_data.view',
            'users.view',
        ],
        self::ROLE_SUBMISSION_REVIEWER => [
            'dashboard.view',
            'submissions.view',
            'submissions.manage',
            'corrections.view',
            'corrections.manage',
            'duplicates.manage',
            'products.view',
            'products.edit',
            'users.view',
        ],
        self::ROLE_MARKETING_MANAGER => [
            'dashboard.view',
            'marketing.view',
            'marketing.manage',
            'products.view',
            'users.view',
        ],
        self::ROLE_SUPPORT_VIEWER => [
            'dashboard.view',
            'products.view',
            'submissions.view',
            'corrections.view',
            'users.view',
            'audit.view',
        ],
    ];

    /**
     * Module access mapping for sidebar and high-level routing.
     */
    public const MODULE_ACCESS = [
        'dashboard' => self::ALL_ROLES,
        'products' => self::ALL_ROLES,
        'submissions' => [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_PRODUCT_MANAGER,
            self::ROLE_SUBMISSION_REVIEWER,
            self::ROLE_SUPPORT_VIEWER,
        ],
        'corrections' => [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_PRODUCT_MANAGER,
            self::ROLE_SUBMISSION_REVIEWER,
            self::ROLE_SUPPORT_VIEWER,
        ],
        'duplicates' => [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_PRODUCT_MANAGER,
            self::ROLE_SUBMISSION_REVIEWER,
        ],
        'health' => [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_HEALTH_REVIEWER,
        ],
        'marketing' => [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_MARKETING_MANAGER,
        ],
        'users' => self::ALL_ROLES,
        'master_data' => [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_PRODUCT_MANAGER,
        ],
        'app_control' => [
            self::ROLE_SUPER_ADMIN,
        ],
        'security' => [
            self::ROLE_SUPER_ADMIN,
        ],
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Check if admin has Super Admin role.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if admin role is strictly read-only.
     */
    public function isReadOnly(): bool
    {
        return $this->role === self::ROLE_SUPPORT_VIEWER;
    }

    /**
     * Check if admin matches one or more given roles.
     *
     * @param string|array $roles
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }
        return $this->role === $roles;
    }

    /**
     * Default permission matrix mapping per role.
     */
    public static function getDefaultPermissionMatrix(): array
    {
        $matrix = [];
        $modules = array_keys(self::MATRIX_MODULES);
        $actions = array_keys(self::MATRIX_ACTIONS);

        foreach (self::ALL_ROLES as $r) {
            $matrix[$r] = [];
            foreach ($modules as $m) {
                $matrix[$r][$m] = [];
                foreach ($actions as $a) {
                    $matrix[$r][$m][$a] = false;
                }
            }
        }

        // 1. Super Admin: full access to everything (100% true, immutable)
        foreach ($modules as $m) {
            foreach ($actions as $a) {
                $matrix[self::ROLE_SUPER_ADMIN][$m][$a] = true;
            }
        }

        // 2. Management (exact match to reference design)
        $matrix[self::ROLE_MANAGEMENT]['dashboard']['view'] = true;
        $matrix[self::ROLE_MANAGEMENT]['report']['view'] = true;
        $matrix[self::ROLE_MANAGEMENT]['report']['export'] = true;
        $matrix[self::ROLE_MANAGEMENT]['notifications']['view'] = true;
        foreach (['clients', 'inquiries', 'orders'] as $mod) {
            $matrix[self::ROLE_MANAGEMENT][$mod]['view'] = true;
            $matrix[self::ROLE_MANAGEMENT][$mod]['create'] = true;
            $matrix[self::ROLE_MANAGEMENT][$mod]['edit_own'] = true;
            $matrix[self::ROLE_MANAGEMENT][$mod]['edit_all'] = true;
            $matrix[self::ROLE_MANAGEMENT][$mod]['assign'] = true;
        }

        // 3. Product Manager
        $matrix[self::ROLE_PRODUCT_MANAGER]['dashboard']['view'] = true;
        $matrix[self::ROLE_PRODUCT_MANAGER]['report']['view'] = true;
        $matrix[self::ROLE_PRODUCT_MANAGER]['report']['export'] = true;
        foreach (['products', 'product_categories'] as $mod) {
            foreach (['view', 'create', 'edit_own', 'edit_all', 'delete', 'export'] as $act) {
                $matrix[self::ROLE_PRODUCT_MANAGER][$mod][$act] = true;
            }
        }
        foreach (['inquiries', 'orders'] as $mod) {
            foreach (['view', 'create', 'edit_own', 'edit_all', 'assign'] as $act) {
                $matrix[self::ROLE_PRODUCT_MANAGER][$mod][$act] = true;
            }
        }
        $matrix[self::ROLE_PRODUCT_MANAGER]['clients']['view'] = true;

        // 4. Health Content Reviewer
        $matrix[self::ROLE_HEALTH_REVIEWER]['dashboard']['view'] = true;
        foreach (['health_intelligence', 'health_concerns', 'personalized_alerts'] as $mod) {
            foreach (['view', 'create', 'edit_own', 'edit_all', 'delete', 'export'] as $act) {
                $matrix[self::ROLE_HEALTH_REVIEWER][$mod][$act] = true;
            }
        }
        $matrix[self::ROLE_HEALTH_REVIEWER]['products']['view'] = true;
        $matrix[self::ROLE_HEALTH_REVIEWER]['clients']['view'] = true;

        // 5. Submission Reviewer
        $matrix[self::ROLE_SUBMISSION_REVIEWER]['dashboard']['view'] = true;
        foreach (['inquiries', 'orders'] as $mod) {
            foreach (['view', 'create', 'edit_own', 'edit_all', 'assign'] as $act) {
                $matrix[self::ROLE_SUBMISSION_REVIEWER][$mod][$act] = true;
            }
        }
        $matrix[self::ROLE_SUBMISSION_REVIEWER]['products']['view'] = true;
        $matrix[self::ROLE_SUBMISSION_REVIEWER]['products']['edit_own'] = true;
        $matrix[self::ROLE_SUBMISSION_REVIEWER]['clients']['view'] = true;

        // 6. Marketing Manager
        $matrix[self::ROLE_MARKETING_MANAGER]['dashboard']['view'] = true;
        foreach (['ads', 'content'] as $mod) {
            foreach (['view', 'create', 'edit_own', 'edit_all', 'delete'] as $act) {
                $matrix[self::ROLE_MARKETING_MANAGER][$mod][$act] = true;
            }
        }
        $matrix[self::ROLE_MARKETING_MANAGER]['notifications']['view'] = true;
        $matrix[self::ROLE_MARKETING_MANAGER]['notifications']['create'] = true;
        $matrix[self::ROLE_MARKETING_MANAGER]['notifications']['edit_own'] = true;
        $matrix[self::ROLE_MARKETING_MANAGER]['notifications']['edit_all'] = true;
        $matrix[self::ROLE_MARKETING_MANAGER]['products']['view'] = true;
        $matrix[self::ROLE_MARKETING_MANAGER]['clients']['view'] = true;

        // 7. Support Viewer
        $matrix[self::ROLE_SUPPORT_VIEWER]['dashboard']['view'] = true;
        $matrix[self::ROLE_SUPPORT_VIEWER]['report']['view'] = true;
        $matrix[self::ROLE_SUPPORT_VIEWER]['products']['view'] = true;
        $matrix[self::ROLE_SUPPORT_VIEWER]['inquiries']['view'] = true;
        $matrix[self::ROLE_SUPPORT_VIEWER]['orders']['view'] = true;
        $matrix[self::ROLE_SUPPORT_VIEWER]['clients']['view'] = true;

        return $matrix;
    }

    /**
     * Retrieve the effective permission matrix, merging defaults with dynamic DB settings.
     */
    public static function getEffectiveMatrix(?string $role = null): array
    {
        $defaults = self::getDefaultPermissionMatrix();
        $stored = AppControl::getVal('role_permission_matrix', []);

        if (is_string($stored)) {
            $stored = json_decode($stored, true) ?: [];
        }

        $effective = $defaults;
        if (is_array($stored)) {
            foreach ($stored as $r => $modPerms) {
                // Never allow overriding Super Admin from stored data
                if ($r === self::ROLE_SUPER_ADMIN) {
                    continue;
                }
                if (isset($effective[$r]) && is_array($modPerms)) {
                    foreach ($modPerms as $m => $actPerms) {
                        if (isset($effective[$r][$m]) && is_array($actPerms)) {
                            foreach ($actPerms as $a => $val) {
                                $effective[$r][$m][$a] = (bool) $val;
                            }
                        }
                    }
                }
            }
        }

        // Guarantee Super Admin remains 100% full access
        $modules = array_keys(self::MATRIX_MODULES);
        $actions = array_keys(self::MATRIX_ACTIONS);
        foreach ($modules as $m) {
            foreach ($actions as $a) {
                $effective[self::ROLE_SUPER_ADMIN][$m][$a] = true;
            }
        }

        if ($role !== null) {
            return $effective[$role] ?? ($defaults[$role] ?? []);
        }

        return $effective;
    }

    /**
     * Map a permission name against dynamic matrix permissions.
     */
    protected static function checkMatrixPermission(array $matrixForRole, string $permission): ?bool
    {
        return match ($permission) {
            'dashboard.view' => $matrixForRole['dashboard']['view'] ?? false,
            'products.view' => $matrixForRole['products']['view'] ?? false,
            'products.manage' => ($matrixForRole['products']['edit_all'] ?? false) || ($matrixForRole['products']['create'] ?? false) || ($matrixForRole['products']['delete'] ?? false),
            'products.edit' => ($matrixForRole['products']['edit_own'] ?? false) || ($matrixForRole['products']['edit_all'] ?? false),
            'duplicates.manage' => ($matrixForRole['products']['edit_all'] ?? false) || ($matrixForRole['orders']['edit_all'] ?? false),
            'submissions.view' => $matrixForRole['inquiries']['view'] ?? false,
            'submissions.manage' => ($matrixForRole['inquiries']['edit_all'] ?? false) || ($matrixForRole['inquiries']['create'] ?? false),
            'corrections.view' => $matrixForRole['orders']['view'] ?? false,
            'corrections.manage' => ($matrixForRole['orders']['edit_all'] ?? false) || ($matrixForRole['orders']['create'] ?? false),
            'health.view' => ($matrixForRole['health_intelligence']['view'] ?? false) || ($matrixForRole['health_concerns']['view'] ?? false),
            'health.manage' => ($matrixForRole['health_intelligence']['edit_all'] ?? false) || ($matrixForRole['health_intelligence']['create'] ?? false),
            'marketing.view' => ($matrixForRole['ads']['view'] ?? false) || ($matrixForRole['content']['view'] ?? false) || ($matrixForRole['notifications']['view'] ?? false),
            'marketing.manage' => ($matrixForRole['ads']['edit_all'] ?? false) || ($matrixForRole['content']['edit_all'] ?? false) || ($matrixForRole['notifications']['edit_all'] ?? false),
            'users.view' => $matrixForRole['clients']['view'] ?? false,
            'users.manage' => ($matrixForRole['clients']['edit_all'] ?? false) || ($matrixForRole['clients']['create'] ?? false),
            'master_data.view' => $matrixForRole['product_categories']['view'] ?? false,
            'master_data.manage' => ($matrixForRole['product_categories']['edit_all'] ?? false) || ($matrixForRole['product_categories']['create'] ?? false),
            'app_control.manage' => ($matrixForRole['app_control']['edit_all'] ?? false) || ($matrixForRole['app_control']['view'] ?? false),
            'security.manage' => ($matrixForRole['security']['edit_all'] ?? false) || ($matrixForRole['security']['view'] ?? false),
            'audit.view' => ($matrixForRole['security']['view'] ?? false) || ($matrixForRole['report']['view'] ?? false),
            default => null,
        };
    }

    /**
     * Check if admin has a specific granular permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Inactive accounts have no permissions
        if ($this->status !== 'Active') {
            return false;
        }

        // Super Admin has unconditional access to everything
        if ($this->isSuperAdmin()) {
            return true;
        }

        $matrixForRole = self::getEffectiveMatrix($this->role);
        if (! empty($matrixForRole)) {
            $mapped = self::checkMatrixPermission($matrixForRole, $permission);
            if ($mapped !== null) {
                return $mapped;
            }
        }

        $perms = self::ROLE_PERMISSIONS[$this->role] ?? [];
        if (in_array('*', $perms, true)) {
            return true;
        }

        return in_array($permission, $perms, true);
    }

    /**
     * Check if admin has access to a functional navigation module.
     */
    public function canAccessModule(string $module): bool
    {
        if ($this->status !== 'Active') {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        $matrixForRole = self::getEffectiveMatrix($this->role);
        if (! empty($matrixForRole)) {
            $matrixKey = match ($module) {
                'dashboard' => 'dashboard',
                'products' => 'products',
                'submissions' => 'inquiries',
                'corrections' => 'orders',
                'duplicates' => 'products',
                'health' => 'health_intelligence',
                'marketing' => 'ads',
                'users' => 'clients',
                'master_data' => 'product_categories',
                'app_control' => 'app_control',
                'security' => 'security',
                default => $module,
            };

            if (isset($matrixForRole[$matrixKey]) && is_array($matrixForRole[$matrixKey])) {
                return in_array(true, $matrixForRole[$matrixKey], true);
            }
        }

        $allowedRoles = self::MODULE_ACCESS[$module] ?? [];
        return in_array($this->role, $allowedRoles, true);
    }
}
