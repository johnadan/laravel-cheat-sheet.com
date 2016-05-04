<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyInClausesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clauses', function (Blueprint $table) {
            $table->integer('section_id')->unsigned();
        });

        Schema::table('clauses', function (Blueprint $table) {
             $table->foreign('section_id')->references('id')->on('sections');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clauses', function (Blueprint $table) {
            //
        });
    }
}
