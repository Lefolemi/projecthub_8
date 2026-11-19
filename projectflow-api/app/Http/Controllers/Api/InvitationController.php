<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectInvitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function index(Request $request)
    {
        $invitations = ProjectInvitation::where('invitee_id', $request->user()->id)
            ->where('status', 'pending')
            ->with(['project:id,name,key,description', 'inviter:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $invitations,
        ]);
    }

    public function accept(Request $request, ProjectInvitation $invitation)
    {
        if ($invitation->invitee_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($invitation->status !== 'pending') {
            return response()->json(['message' => 'Invitation is no longer pending.'], 422);
        }

        $invitation->update(['status' => 'accepted']);

        $invitation->project->members()->syncWithoutDetaching([
            $request->user()->id => ['role' => 'member'],
        ]);

        return response()->json([
            'message' => "You have joined {$invitation->project->name}.",
            'project' => $invitation->project,
        ]);
    }

    public function decline(Request $request, ProjectInvitation $invitation)
    {
        if ($invitation->invitee_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $invitation->update(['status' => 'declined']);

        return response()->json([
            'message' => 'Invitation declined.',
        ]);
    }
}