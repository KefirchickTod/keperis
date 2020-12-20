<?php


namespace src\Core\User;



use App\Permission\Permission;
use App\Role\Role;
use function in_array;

class UserAuth
{
    private $DB;
    /**
     * @var |null ід авторизованого користувача
     */
    private $id;
    private $permissions;

    public function __construct()
    {
        $this->DB = db();
        $this->id = $_SESSION['BCUserId'] ?? null;

        if ($this->id) {
            $this->setPermissions();
        }
    }

    /**
     * Встановлює дозволи для авторизованого користувача
     * Вибирає максимальний рівень для кожного дозволу з усіх ролей користувача
     */
    private function setPermissions()
    {
        $permissions_by_role = (new Role())->getPermissions($this->getRoles());

        // вибираємо максимальні рівні дозволів з усіх ролей, що є в користувача
        $combined_permissions = [];
        foreach ($permissions_by_role as $role) {
            foreach ($role as $permission_id => $permission_status) {
                if (!isset($combined_permissions[$permission_id]) ||
                    $combined_permissions[$permission_id] < $permission_status) {
                    $combined_permissions[$permission_id] = $permission_status;
                }
            }
        }

        // створюємо масив з дозволами, де ключем елементу є код дозволу
        $permissions = (new Permission())->get();
        $user_permissions_by_code = [];
        foreach ($permissions as $permission) {
            $user_permissions_by_code[$permission['bc_permissions_code']] =
                $combined_permissions[$permission['bc_permissions_id']] ?? 0;
        }

        $this->permissions = $user_permissions_by_code;
    }

    /**
     * Повертає масив з ід ролей користувача
     *
     * @param int|null $user_id
     *
     * @return array
     */
    public function getRoles(int $user_id = null): array
    {
        $user_id = $user_id ?: $this->id;

        $roles = [];
        if ($user_id) {
            $sql = db()->selectSql('bc_user_roles', 'bc_user_roles_role_id', "bc_user_roles_user_id = $user_id");

            if ($sql) {
                foreach ($sql as $row) {
                    $roles[] = (int)$row['bc_user_roles_role_id'];
                }
            }
        }

        return $roles;
    }

    /**
     * Оновлює ролі користувача
     *
     * @param int $user_id
     * @param array $roles
     */
    public function updateRoles(int $user_id, array $roles)
    {
        db()->getConnection()->exec("
            DELETE FROM bc_user_roles
            WHERE bc_user_roles_user_id = $user_id
              AND bc_user_roles_role_id NOT IN (
                SELECT bc_roles_id FROM bc_roles WHERE bc_roles_code IN ('super_admin', 'director')
              )
        ");

        if (!empty($roles)) {
            $db_array = [];
            foreach ($roles as $role) {
                $db_array[] = [
                    'bc_user_roles_user_id' => $user_id,
                    'bc_user_roles_role_id' => $role,
                ];
            }

            db()->insertOrUpdateSqlMany('bc_user_roles', $db_array);
        }
    }

    /**
     * Перевіряє, чи є у авторизованого користувача дозвіл на виконання певної дії
     *
     * @param string $code код дозволу, що перевіряється
     * @param bool $return_status якщо true, то повертаємо статус/рівень дозволу (0/1/2)
     *
     * @return bool|int
     */
    public function check(string $code, bool $return_status = false)
    {
        if ($return_status) {
            return (int)$this->permissions[$code];
        }

        return (bool)$this->permissions[$code];
    }

    /**
     * Перевіряє, чи є авторизований користувач супер-адміном
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return in_array((new Role)->getSuperAdminRoleId(), $this->getRoles());
    }
}