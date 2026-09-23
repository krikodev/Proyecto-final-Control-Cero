<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración "puente" de las tablas de roles/permisos propias del proyecto
 * al esquema esperado por spatie/laravel-permission.
 *
 * - roles: se conserva (slug y description) y se agrega guard_name.
 * - permissions: el slug pasa a llamarse "name" (esa es la habilidad que
 *   consulta Spatie) y el nombre visible pasa a "title".
 * - permission_role: sirve como pivote role_has_permissions (config).
 * - Se crean model_has_roles / model_has_permissions.
 * - La asignación users.role_id se traslada a model_has_roles y se elimina.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        throw_if(
            empty($tableNames),
            Exception::class,
            'Error: config/permission.php no está cargado. Ejecuta [php artisan config:clear] e inténtalo de nuevo.'
        );

        // ---------------------------------------------------------------
        // 1) roles
        // ---------------------------------------------------------------
        if (! Schema::hasTable($tableNames['roles'])) {
            Schema::create($tableNames['roles'], static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->string('slug', 50)->nullable();
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn($tableNames['roles'], 'guard_name')) {
            Schema::table($tableNames['roles'], static function (Blueprint $table) {
                $table->string('guard_name', 25)->default('web');
            });
        }

        Schema::table($tableNames['roles'], static function (Blueprint $table) {
            $table->unique(['name', 'guard_name']);
        });

        // ---------------------------------------------------------------
        // 2) permissions: slug -> name (habilidad), name -> title (visible)
        // ---------------------------------------------------------------
        if (! Schema::hasTable($tableNames['permissions'])) {
            Schema::create($tableNames['permissions'], static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->string('title', 100)->nullable();
                $table->string('module', 50)->nullable()->index();
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn($tableNames['permissions'], 'guard_name')) {
            Schema::table($tableNames['permissions'], static function (Blueprint $table) {
                $table->string('guard_name', 25)->default('web');
            });
        }

        if (! Schema::hasColumn($tableNames['permissions'], 'title')) {
            Schema::table($tableNames['permissions'], static function (Blueprint $table) {
                $table->string('title', 100)->nullable();
            });
        }

        if (Schema::hasColumn($tableNames['permissions'], 'slug')) {
            DB::table($tableNames['permissions'])->update([
                'title' => DB::raw('COALESCE(title, name)'),
                'name' => DB::raw('slug'),
            ]);

            Schema::table($tableNames['permissions'], static function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });

            Schema::table($tableNames['permissions'], static function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }

        Schema::table($tableNames['permissions'], static function (Blueprint $table) {
            $table->unique(['name', 'guard_name']);
        });

        // ---------------------------------------------------------------
        // 3) pivote rol <-> permiso (la tabla propia "permission_role"
        //    se reutiliza como role_has_permissions de Spatie)
        // ---------------------------------------------------------------
        if (! Schema::hasTable($tableNames['role_has_permissions'])) {
            Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
                $table->unsignedBigInteger($pivotPermission);
                $table->unsignedBigInteger($pivotRole);

                $table->foreign($pivotPermission)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->cascadeOnDelete();

                $table->foreign($pivotRole)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->cascadeOnDelete();

                $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_role_primary');
            });
        }

        // ---------------------------------------------------------------
        // 4) asignación de roles/permisos directos por modelo (morph)
        // ---------------------------------------------------------------
        if (! Schema::hasTable($tableNames['model_has_permissions'])) {
            Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotPermission, $modelMorphKey) {
                $table->unsignedBigInteger($pivotPermission);
                $table->string('model_type');
                $table->unsignedBigInteger($modelMorphKey);

                $table->index([$modelMorphKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');

                $table->foreign($pivotPermission)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->cascadeOnDelete();

                $table->primary([$pivotPermission, $modelMorphKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');
            });
        }

        if (! Schema::hasTable($tableNames['model_has_roles'])) {
            Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $pivotRole, $modelMorphKey) {
                $table->unsignedBigInteger($pivotRole);
                $table->string('model_type');
                $table->unsignedBigInteger($modelMorphKey);

                $table->index([$modelMorphKey, 'model_type'], 'model_has_roles_model_id_model_type_index');

                $table->foreign($pivotRole)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->cascadeOnDelete();

                $table->primary([$pivotRole, $modelMorphKey, 'model_type'], 'model_has_roles_role_model_type_primary');
            });
        }

        // ---------------------------------------------------------------
        // 5) users.role_id -> model_has_roles (conservando los datos)
        // ---------------------------------------------------------------
        if (Schema::hasColumn('users', 'role_id')) {
            DB::table('users')
                ->whereNotNull('role_id')
                ->orderBy('id')
                ->get(['id', 'role_id'])
                ->each(function ($user) use ($tableNames, $modelMorphKey) {
                    $exists = DB::table($tableNames['model_has_roles'])
                        ->where('role_id', $user->role_id)
                        ->where($modelMorphKey, $user->id)
                        ->where('model_type', User::class)
                        ->exists();

                    if (! $exists) {
                        DB::table($tableNames['model_has_roles'])->insert([
                            'role_id' => $user->role_id,
                            $modelMorphKey => $user->id,
                            'model_type' => User::class,
                        ]);
                    }
                });

            $this->dropUserRoleColumn();
        }

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        throw_if(
            empty($tableNames),
            Exception::class,
            'Error: config/permission.php no está cargado.'
        );

        // 1) Restaura users.role_id desde model_has_roles
        if (! Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', static function (Blueprint $table) {
                $table->foreignId('role_id')->nullable()->constrained('roles')->restrictOnDelete();
            });

            DB::table($tableNames['model_has_roles'])
                ->where('model_type', User::class)
                ->orderBy($modelMorphKey)
                ->get()
                ->groupBy($modelMorphKey)
                ->each(function ($rows, $userId) {
                    DB::table('users')
                        ->where('id', $userId)
                        ->update(['role_id' => $rows->first()->role_id]);
                });
        }

        // 2) Devuelve permissions al esquema propio (name visible + slug)
        Schema::table($tableNames['permissions'], static function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
        });

        if (! Schema::hasColumn($tableNames['permissions'], 'slug')) {
            Schema::table($tableNames['permissions'], static function (Blueprint $table) {
                $table->string('slug', 100)->nullable();
            });

            DB::table($tableNames['permissions'])->update([
                'slug' => DB::raw('name'),
                'name' => DB::raw('COALESCE(title, name)'),
            ]);

            Schema::table($tableNames['permissions'], static function (Blueprint $table) {
                $table->unique(['slug']);
            });
        }

        if (Schema::hasColumn($tableNames['permissions'], 'title')) {
            Schema::table($tableNames['permissions'], static function (Blueprint $table) {
                $table->dropColumn('title');
            });
        }

        if (Schema::hasColumn($tableNames['permissions'], 'guard_name')) {
            Schema::table($tableNames['permissions'], static function (Blueprint $table) {
                $table->dropColumn('guard_name');
            });
        }

        // 3) Quita guard_name de roles (se conservan slug y description)
        Schema::table($tableNames['roles'], static function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
        });

        if (Schema::hasColumn($tableNames['roles'], 'guard_name')) {
            Schema::table($tableNames['roles'], static function (Blueprint $table) {
                $table->dropColumn('guard_name');
            });
        }

        // 4) Elimina las tablas que creó esta migración
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
    }

    /**
     * MySQL permite eliminar la FK y la columna de una vez; en SQLite la
     * restricción es inline y puede impedir el DROP COLUMN.
     */
    private function dropUserRoleColumn(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('users', static function (Blueprint $table) {
                $table->dropConstrainedForeignId('role_id');
            });

            return;
        }

        try {
            Schema::table('users', static function (Blueprint $table) {
                $table->dropColumn('role_id');
            });
        } catch (Throwable) {
            // La columna queda huérfana (nullable) y el modelo ya no la usa.
        }
    }
};
