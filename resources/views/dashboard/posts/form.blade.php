@php
    $editing = $post->exists;
    $publishedAt = old('published_at', $post->published_at?->format('Y-m-d\TH:i'));
    $status = old('status', $post->status);
    $categoryId = (string) old('category_id', $post->category_id);
    $state = $editing ? $post->state() : null;
    $stateLabels = ['live' => 'Live', 'scheduled' => 'Scheduled', 'draft' => 'Draft'];
@endphp

<x-layouts.dashboard :title="$editing ? 'Edit post' : 'New post'">
    @push('head')
        @vite('resources/js/post-editor.js')
    @endpush

    <form class="form dash-editor" method="POST" enctype="multipart/form-data"
        action="{{ $editing ? route('dashboard.posts.update', $post) : route('dashboard.posts.store') }}" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="dash-actionbar">
            <a class="dash-back" href="{{ route('dashboard.posts.index') }}"><x-dash.icon name="back" /> Posts</a>
            <div class="dash-actionbar__title">
                <h1>{{ $editing ? 'Edit post' : 'New post' }}</h1>
                @if ($state)
                    <span class="dash-pill dash-pill--{{ $state }}">{{ $stateLabels[$state] }}</span>
                @endif
            </div>
            <div class="dash-actionbar__actions">
                @if ($state === 'live')
                    <a class="dash-btn dash-btn--ghost" href="{{ route('posts.show', $post->slug) }}" target="_blank" rel="noopener"><x-dash.icon name="external" /> View on site</a>
                @endif
                <button type="submit" class="dash-btn dash-btn--primary"><x-dash.icon name="check" /> {{ $editing ? 'Save post' : 'Create post' }}</button>
            </div>
        </div>

        @if ($errors->any())
            <div class="dash-callout dash-callout--error" role="alert" data-error-summary tabindex="-1">
                <x-dash.icon name="alert" />
                <div>
                    <h2>Please check the form.</h2>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="dash-editor__grid">
            <div class="dash-card dash-writer">
                <label class="visually-hidden" for="title">Title</label>
                <textarea class="dash-writer__title" id="title" name="title" rows="2" required maxlength="200" placeholder="Post title" data-autosize>{{ old('title', $post->title) }}</textarea>

                <div class="dash-field">
                    <label class="dash-label" for="excerpt">Summary <span>optional</span></label>
                    <textarea class="input" id="excerpt" name="excerpt" rows="2" maxlength="300" placeholder="One or two sentences for the list. Leave empty to use the first paragraph.">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>

                <div class="dash-field">
                    <div class="dash-writer__bar">
                        <label class="dash-label" for="body">Text</label>
                        <span class="dash-writer__count" id="body-count" aria-live="polite"></span>
                    </div>
                    {{-- Rich text editor (resources/js/post-editor.js) over the field it fills. --}}
                    <textarea class="input dash-writer__body" id="body" name="body" rows="20" required data-richtext data-richtext-count="#body-count" placeholder="Start writing…">{{ old('body', $editing ? $post->bodyHtml() : '') }}</textarea>
                    <p class="dash-hint">Use the toolbar for headings, bold and italic, lists, quotes and links. Pasted text keeps its paragraphs.</p>
                </div>
            </div>

            <aside class="dash-editor__side">
                <section class="dash-card" aria-labelledby="publishing-title">
                    <h2 class="dash-card__title" id="publishing-title">Publishing</h2>
                    <fieldset class="dash-field">
                        <legend class="dash-label">Status</legend>
                        <div class="dash-segment">
                            @foreach (\App\Models\Post::STATUSES as $key => $label)
                                <label><input type="radio" name="status" value="{{ $key }}" @checked($status === $key)><span>{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="dash-field">
                        <label class="dash-label" for="published_at">Publish date <span>optional</span></label>
                        <input class="input" type="datetime-local" id="published_at" name="published_at" value="{{ $publishedAt }}">
                        <p class="dash-hint">Empty means “now” when published. A future date schedules the post.</p>
                    </div>
                </section>

                <section class="dash-card" aria-labelledby="category-title">
                    <h2 class="dash-card__title" id="category-title">Category <a class="dash-link" href="{{ route('dashboard.categories.index') }}" style="font-weight: 400; font-size: 0.8125rem">Manage</a></h2>
                    <fieldset class="dash-chips">
                        <legend class="visually-hidden">Category</legend>
                        @foreach ($categories as $item)
                            <label><input type="radio" name="category_id" value="{{ $item->id }}" @checked($categoryId === (string) $item->id)><span><span class="cat-dot" style="background: {{ $item->dot() }}"></span> {{ $item->name }}</span></label>
                        @endforeach
                        <label><input type="radio" name="category_id" value="" @checked($categoryId === '')><span>None</span></label>
                    </fieldset>
                </section>

                <section class="dash-card" aria-labelledby="image-title">
                    <h2 class="dash-card__title" id="image-title">Image</h2>
                    <label @class(['dash-drop', 'has-file' => $post->imageUrl()]) data-dash-drop>
                        <img class="dash-drop__preview" src="{{ $post->imageUrl() }}" alt="" data-drop-preview @unless ($post->imageUrl()) hidden @endunless>
                        <span class="dash-drop__empty">
                            <x-dash.icon name="upload" />
                            <strong>Drop an image or <u>browse</u></strong>
                            <span>JPG, PNG or WebP · 600px+ wide · up to 4 MB</span>
                        </span>
                        <span class="dash-drop__change"><x-dash.icon name="upload" /> Replace image</span>
                        <input class="dash-drop__input" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" aria-label="{{ $post->imageUrl() ? 'Replace image' : 'Upload image' }}">
                    </label>
                    @if ($post->imageUrl())
                        <label class="dash-check"><input type="checkbox" name="remove_image" value="1"> Remove this image</label>
                    @endif
                    <div class="dash-field">
                        <label class="dash-label" for="image_alt">Image description</label>
                        <input class="input" type="text" id="image_alt" name="image_alt" value="{{ old('image_alt', $post->image_alt) }}" maxlength="200" placeholder="One sentence for screen readers">
                        <p class="dash-hint">Shown to the left of the text on the site.</p>
                    </div>
                </section>

                <section class="dash-card" aria-labelledby="settings-title">
                    <h2 class="dash-card__title" id="settings-title">Settings</h2>
                    <div class="dash-field">
                        <label class="dash-label" for="slug">Web address <span>optional</span></label>
                        <div class="dash-affix">
                            <span>/blog/</span>
                            <input class="input" type="text" id="slug" name="slug" value="{{ old('slug', $post->slug) }}" maxlength="190" placeholder="from-the-title">
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </form>

    @if ($editing)
        <form class="dash-danger" method="POST" action="{{ route('dashboard.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <div>
                <h2>Delete this post</h2>
                <p>It is removed from the site and the dashboard, together with its image. This cannot be undone.</p>
            </div>
            <button type="submit" class="dash-btn dash-btn--danger"><x-dash.icon name="trash" /> Delete post</button>
        </form>
    @endif
</x-layouts.dashboard>
