<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkspaceInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;

    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function build()
    {
        // Assuming frontend route is /accept-invite
        $link = url('/accept-invite?token=' . $this->invitation->token);
        $wsName = $this->invitation->inviteable->name ?? 'Workspace';
        $inviterName = $this->invitation->inviter->display_name ?? 'Someone';
        
        return $this->subject("Invitation to join {$wsName}")
                    ->html("
                        <div style='font-family: sans-serif; padding: 20px;'>
                            <h2>You have been invited!</h2>
                            <p><strong>{$inviterName}</strong> has invited you to join <strong>{$wsName}</strong> as a <strong>{$this->invitation->role}</strong>.</p>
                            <div style='margin: 24px 0;'>
                                <a href='{$link}' style='background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Accept Invitation</a>
                            </div>
                            <p style='color: #666; font-size: 12px;'>This link is valid for 7 days.</p>
                        </div>
                    ");
    }
}
