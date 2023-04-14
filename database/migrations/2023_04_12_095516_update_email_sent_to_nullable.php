<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEmailSentToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('PO_Log_Sending_Email', function (Blueprint $table) {
            $table->string('email_sent')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('PO_Log_Sending_Email', function (Blueprint $table) {
            $table->string('email_sent')->nullable(false)->change();
        });
    }
}
