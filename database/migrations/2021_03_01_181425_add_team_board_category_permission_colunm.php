<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Team\Team;

class AddTeamBoardCategoryPermissionColunm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    private string $willUpdateTableName = 'team_board_categories';
    private string $willCreateColumeName = 'write_permission';

    public function up(): void
    {
        Schema::table($this->willUpdateTableName, function (Blueprint $table): void {
            $hasNotColunm = Schema::hasColumn($this->willUpdateTableName, $this->willCreateColumeName) === false;

            if ($hasNotColunm) {
                $table->tinyInteger($this->willCreateColumeName)->after('is_public')->default()->comment('게시글 작성 권한');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->willUpdateTableName, function (Blueprint $table): void {
            $hasColunm = Schema::hasColumn($this->willUpdateTableName, $this->willCreateColumeName);

            if ($hasColunm) {
                $table->dropColumn($this->willCreateColumeName);
            }
        });
    }
}
