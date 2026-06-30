<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    // ── Public Methods ──────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = Project::with('tags')->where('active', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $projects = $query->orderBy('featured', 'desc')
                          ->orderBy('year', 'desc')
                          ->get();

        return response()->json([
            'success' => true,
            'data'    => $projects,
        ]);
    }

    public function show(Project $project): JsonResponse
    {
        $project->load(['tags', 'testimonial']);
        $project->increment('views');

        return response()->json([
            'success' => true,
            'data'    => $project,
        ]);
    }

    public function featured(): JsonResponse
    {
        $projects = Project::with('tags')
            ->where('active', true)
            ->where('featured', true)
            ->orderBy('year', 'desc')
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $projects,
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Project::where('active', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }

    // ── Admin Methods ──────────────────────────────────────────────────

    /**
     * Admin: Get all projects (including inactive)
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $projects = Project::with('tags')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    /**
     * Admin: Create a new project
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'featured' => 'boolean',
            'active' => 'boolean',
            'year' => 'nullable|integer|min:2000|max:' . date('Y'),
            'live_url' => 'nullable|url',
            'github_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $project = Project::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $project
        ], 201);
    }

    /**
     * Admin: Update an existing project
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'featured' => 'boolean',
            'active' => 'boolean',
            'year' => 'nullable|integer|min:2000|max:' . date('Y'),
            'live_url' => 'nullable|url',
            'github_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $project->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $project
        ]);
    }

    /**
     * Admin: Delete a project
     */
    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully'
        ]);
    }
}