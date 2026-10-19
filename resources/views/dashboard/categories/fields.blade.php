{{-- Shared by the "Add category" card and the edit screen. $category is a Category (new or existing); $prefix keeps ids unique. --}}
@php
    $prefix = $prefix ?? 'category';
    $colour = old('color', $category->color ?: \App\Models\Category::COLORS[0]);
@endphp

<div class="dash-row">
    <div class="dash-field">
        <label class="dash-label" for="{{ $prefix }}-name">Name</label>
        <input class="input" type="text" id="{{ $prefix }}-name" name="name" value="{{ old('name', $category->name) }}" required maxlength="80" placeholder="e.g. Field Notes">
    </div>
    <div class="dash-field">
        <label class="dash-label" for="{{ $prefix }}-slug">Web address <span>optional</span></label>
        <div class="dash-affix">
            <span>?category=</span>
            <input class="input" type="text" id="{{ $prefix }}-slug" name="slug" value="{{ old('slug', $category->slug) }}" maxlength="120" placeholder="from-the-name">
        </div>
    </div>
</div>

<div class="dash-field">
    <label class="dash-label" for="{{ $prefix }}-description">Description <span>optional</span></label>
    <textarea class="input" id="{{ $prefix }}-description" name="description" rows="2" maxlength="300" placeholder="One sentence shown on the blog when this category is selected.">{{ old('description', $category->description) }}</textarea>
</div>

<div class="dash-row">
    <div class="dash-field">
        <label class="dash-label" for="{{ $prefix }}-color">Colour</label>
        <input class="input" type="color" id="{{ $prefix }}-color" name="color" value="{{ $colour }}" list="{{ $prefix }}-colors" style="height: 2.75rem; padding: 0.2rem; max-width: 8rem">
        <datalist id="{{ $prefix }}-colors">
            @foreach (\App\Models\Category::COLORS as $swatch)
                <option value="{{ $swatch }}">
            @endforeach
        </datalist>
        <p class="dash-hint">The dot next to the name on the blog and in the dashboard.</p>
    </div>
    <div class="dash-field">
        <label class="dash-label" for="{{ $prefix }}-order">Order <span>optional</span></label>
        <input class="input" type="number" id="{{ $prefix }}-order" name="sort_order" value="{{ old('sort_order', $category->exists ? $category->sort_order : '') }}" min="0" max="9999" placeholder="Last">
        <p class="dash-hint">Smaller numbers come first in lists and filters.</p>
    </div>
</div>
