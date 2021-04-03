<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeamInviteCardFromTypeColunm extends Migration
{
    private string $willCreateColumeName = 'from_type';
    private string $requestUserId = 'invitation_card_creator';
    private string $willUpdateTableName = 'team_member_invitation_cards';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('team_member_invitation_cards', function (Blueprint $table): void {
            $hasNotColunm = Schema::hasColumn($this->willUpdateTableName, $this->willCreateColumeName) === false;
            $hadNotRequestUserId = Schema::hasColumn($this->willUpdateTableName, $this->requestUserId) === false;

            if ($hasNotColunm && $hadNotRequestUserId) {
                $table->tinyInteger($this->willCreateColumeName)->after('team_id')->comment('일반유저가 팀에 가입요청 : 1 / 팀장이 일반유저에게 오퍼 : 2');
                $table->foreignId($this->requestUserId)->after($this->willCreateColumeName)->comment('카드 만든사람');

                $table->foreign($this->requestUserId)->references('id')->on('users')->cascadeOnDelete();
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
        Schema::table('team_member_invitation_cards', function (Blueprint $table): void {
            $hasColunm = Schema::hasColumn($this->willUpdateTableName, $this->willCreateColumeName);
            $hadNotRequestUserId = Schema::hasColumn($this->willUpdateTableName, $this->requestUserId);

            if ($hasColunm && $hadNotRequestUserId) {
                $table->dropColumn($this->willCreateColumeName);
                $table->dropColumn($this->requestUserId);
            }
        });
    }
}
