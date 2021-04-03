<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Team\InvitationCard;

class CreateTeamMemberInvitationCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('team_member_invitation_cards', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')->comment('가입신청한 유저');
            $table->foreignId('team_id')->comment('가입신청 결정할 팀');

            $table->tinyInteger('from_type')->comment('일반유저가 팀에 가입요청 : 1 / 팀장이 일반유저에게 오퍼 : 2');
            $table->foreignId('invitation_card_creator')->comment('카드 만든사람');

            $table->tinyInteger('status')->default(InvitationCard::PENDING)->comment('수락 상태');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('invitation_card_creator')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //
    }
}
