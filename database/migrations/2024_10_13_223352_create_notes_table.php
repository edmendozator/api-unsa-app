<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
	        $table->id();
	        $table->char('cui', 8);
            $table->char('nues', 3);
            $table->char('espe', 1);
	        $table->string('casi', 7);
            $table->char('anio', 4);
            $table->char('ciclo', 1);
            $table->text('descripcion');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notes');
    }
}
