<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('dashboard.categories.index', [
            'categories' => Category::ordered()->withCount('posts')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = new Category;
        $this->save($category, $request, creating: true);

        return redirect()->route('dashboard.categories.index')->with('status', "Category “{$category->name}” added.");
    }

    public function edit(Category $category): View
    {
        return view('dashboard.categories.form', [
            'category' => $category->loadCount('posts'),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->save($category, $request, creating: false);

        return redirect()->route('dashboard.categories.index')->with('status', 'Category saved.');
    }

    /** Its posts stay, uncategorised (the foreign key sets category_id to null). */
    public function destroy(Category $category): RedirectResponse
    {
        $posts = $category->posts()->count();
        $name = $category->name;
        $category->delete();
        ActivityLog::record('category.deleted', $name, ['posts' => $posts]);

        return redirect()->route('dashboard.categories.index')->with('status', $posts
            ? "Category “{$name}” deleted. Its {$posts} ".Str::plural('post', $posts).' now '.($posts === 1 ? 'has' : 'have').' no category.'
            : "Category “{$name}” deleted.");
    }

    private function save(Category $category, Request $request, bool $creating): void
    {
        $request->merge([
            'name' => Str::squish((string) $request->input('name')),
            'slug' => $request->filled('slug') ? Str::slug((string) $request->input('slug')) : null,
            'color' => $request->filled('color') ? strtoupper((string) $request->input('color')) : null,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:300'],
            'color' => ['nullable', 'regex:/^#[0-9A-F]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'color.regex' => 'Pick a colour or enter one like #2BA3A0.',
            'slug.unique' => 'Another category already uses that web address.',
        ]);

        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        }
        $data['sort_order'] ??= $creating ? ((int) Category::max('sort_order') + 1) : $category->sort_order;
        $data['description'] = filled($data['description'] ?? null) ? trim($data['description']) : null;

        $category->fill($data);
        $changed = array_keys($category->getDirty());
        $category->save();

        ActivityLog::record($creating ? 'category.created' : 'category.updated', $category->name, $creating
            ? ['category' => $category->id]
            : ['category' => $category->id, 'changed' => array_values(array_diff($changed, ['updated_at']))]);
    }
}
