<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List all categories for the authenticated user.
     */
    public function index(Request $request)
    {
        $categories = Category::where('user_id', $request->user()->id)
            ->withCount('tasks')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $category = $request->user()->categories()->create($validated);

        $category->loadCount('tasks');

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show a single category.
     */
    public function show(Request $request, Category $category)
    {
        $this->authorizeCategory($request->user(), $category);

        $category->loadCount('tasks');

        return new CategoryResource($category);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $this->authorizeCategory($request->user(), $category);

        $validated = $request->validate([
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $category->update($validated);

        $category->loadCount('tasks');

        return new CategoryResource($category);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Request $request, Category $category)
    {
        $this->authorizeCategory($request->user(), $category);

        $category->delete();

        return response()->noContent();
    }

    /**
     * Inline ownership check (no separate Policy needed for categories).
     */
    private function authorizeCategory($user, Category $category): void
    {
        if ($category->user_id !== $user->id) {
            abort(403, 'This action is unauthorized.');
        }
    }
}
