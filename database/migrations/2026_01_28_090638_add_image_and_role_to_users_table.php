<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('image')->nullable()->after('password');
            $table->tinyInteger('type')->default(0)->comment('0 = User, 1 = Admin, 2 = Merchant')->after('image');
            $table->string('phone')->nullable()->after('type');
            $table->string('role')->nullable()->after('phone');
            $table->string('status')->nullable()->comment('0 = Inactive, 1 = Active')->after('role');
            $table->enum('business_category', ['salon_beauty','home_services','health','fitness_gym','others',])->nullable()->after('status');
            $table->string('website_domain')->nullable()->after('business_category');
            $table->string('address')->nullable()->after('website_domain');
            $table->string('platform_access')->nullable()->comment('0 = Disabled, 1 = Enabled')->after('address');
            $table->string('current_package')->nullable()->after('platform_access');
            $table->string('package_duration')->nullable()->after('current_package');
            $table->string('package_start_date')->nullable()->after('package_duration');
            $table->string('package_end_date')->nullable()->after('package_start_date');
            $table->string('package_expire_date')->nullable()->after('package_end_date');
            $table->string('remaining_day')->nullable()->after('package_expire_date');
            $table->string('package_status')->nullable()->comment('0 = Inactive, 1 = Active')->after('remaining_day');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
