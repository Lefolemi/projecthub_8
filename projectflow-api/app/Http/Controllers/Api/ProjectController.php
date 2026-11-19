<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $request->user()
            ->projects()
            ->withCount(['tasks', 'members'])
            ->with('owner:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'key' => ['required', 'string', 'max:10', 'alpha_num'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'key' => strtoupper($validated['key']),
            'description' => $validated['description'] ?? null,
            'owner_id' => $request->user()->id,
        ]);

        $project->members()->attach($request->user()->id, ['role' => 'owner']);

        return response()->json([
            'message' => 'Project created successfully',
            'data' => $project->loadCount(['tasks', 'members'])->load('owner:id,name,email'),
        ], 201);
    }

    public function show(Request $request, Project $project)
    {
        $isMember = $project->members()->where('user_id', $request->user()->id)->exists();
        if (! $isMember && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json([
            'data' => $project->load([
                'owner:id,name,email',
                'members:id,name,email,role',
                'invitations' => fn ($q) => $q->where('status', 'pending')->with('invitee:id,name,email'),
            ]),
        ]);
    }

    public function invite(Request $request, Project $project)
    {
        $isOwner = $project->members()
            ->where('user_id', $request->user()->id)
            ->wherePivot('role', 'owner')
            ->exists();

        if (! $isOwner && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Only project owners can invite members.'], 403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $invitee = User::where('email', $validated['email'])->first();

        if ($project->members()->where('user_id', $invitee->id)->exists()) {
            return response()->json(['message' => 'User is already a member of this project.'], 422);
        }

        $alreadyPending = ProjectInvitation::where('project_id', $project->id)
            ->where('invitee_id', $invitee->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return response()->json(['message' => 'A pending invitation has already been sent to this user.'], 422);
        }

        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'inviter_id' => $request->user()->id,
            'invitee_id' => $invitee->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => "Invitation sent to {$invitee->email}.",
            'data' => $invitation->load('invitee:id,name,email'),
        ], 201);
    }
}