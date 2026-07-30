<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar nuevas columnas a la tabla 'users'
        Schema::table('users', function (Blueprint $table) {
            $table->integer('id_area')->unsigned()->nullable();  // Definir como nullable si no estás seguro de que todos los usuarios tendrán un valor
            $table->string('celular', 50)->nullable();
            $table->unsignedInteger('tipo_usuario');
            $table->unsignedInteger('estado'); // 1:Activo, 0:Inactivo
        });

        // Votar todos los datos de la tabla 'users'
        DB::table('users')->truncate(); // Elimina todos los registros de la tabla

        // Insertar nuevo usuario (admin)
        DB::table('users')->insert([
            [
                'name' => 'Kevin Pinillos',
                'tipo_usuario' => 1,
                'email' => 'admin@asecon.com',
                'password' => Hash::make('@asecon132'),
                'celular' => '973093350',
                'estado' => 1,
                'id_area' => 1,  // Asegúrate de que este valor exista en la tabla 'areas'
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Crear la tabla 'areas'
        Schema::create('areas', function (Blueprint $table) {
            $table->increments('id_area');
            $table->unsignedBigInteger('id_user');
            $table->string('nombre', 100);
            $table->unsignedInteger('estado'); // 1:Activo, 0:Inactivo
            $table->timestamps();

            // Crear la relación con la tabla 'users'
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });

        // Insertar un área por defecto
        DB::table('areas')->insert([
            [
                'nombre' => 'ADMINISTRACION',
                'id_user' => 1,
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        // Eliminar las tablas y los cambios realizados
        Schema::dropIfExists('areas');
        Schema::table('users', function (Blueprint $table) {
            // Aquí aseguramos que eliminamos las columnas correctas
            $table->dropColumn(['id_area', 'celular', 'tipo_usuario', 'estado']);
        });
    }

};