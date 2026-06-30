<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Display a listing of testimonials
     */
    public function index()
    {
        $testimonials = Testimonial::with('project')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $testimonials
        ]);
    }

    /**
     * Get featured testimonials for the homepage
     */
    public function featured()
    {
        $testimonials = Testimonial::featured()
            ->with('project')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $testimonials
        ]);
    }

    /**
     * Store a new testimonial
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'author' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'content' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'featured' => 'boolean'
        ]);

        $testimonial = Testimonial::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial added successfully!',
            'data' => $testimonial
        ], 201);
    }

    /**
     * Display a specific testimonial
     */
    public function show($id)
    {
        $testimonial = Testimonial::with('project')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $testimonial
        ]);
    }

    /**
     * Update a testimonial
     */
    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $validated = $request->validate([
            'author' => 'string|max:255',
            'role' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'content' => 'string|max:1000',
            'rating' => 'integer|min:1|max:5',
            'featured' => 'boolean'
        ]);

        $testimonial->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully!',
            'data' => $testimonial
        ]);
    }

    /**
     * Delete a testimonial
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully!'
        ]);
    }
}