<x-layouts.dashboard title="Media">
    <header class="dash-head">
        <div>
            <p class="dash-eyebrow">Blog · Images</p>
            <h1>Media</h1>
            <p class="dash-sub">Every image uploaded in the dashboard, including post images.</p>
        </div>
        <form class="dash-media-search" method="GET" action="{{ route('dashboard.media.index') }}" role="search">
            <label class="visually-hidden" for="media-search">Search images</label>
            <x-dash.icon name="search" />
            <input class="input" id="media-search" type="search" name="q" value="{{ $search }}" placeholder="Search by name or description">
        </form>
    </header>

    @if ($errors->any())
        <div class="dash-alert" role="alert"><x-dash.icon name="alert" /><span>{{ $errors->first() }}</span></div>
    @endif

        <div @class(['dash-media-layout', 'has-selection' => $selected])>
            <div>
                @can('edit-content')
                    <form class="dash-upload" method="POST" action="{{ route('dashboard.media.store') }}" enctype="multipart/form-data" data-upload-zone>
                        @csrf
                        <input class="dash-upload__input" id="media-upload" type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-auto-submit>
                        <span class="dash-upload__body">
                            <x-dash.icon name="upload" />
                            <strong>Drop images here or click to upload</strong>
                            <span>JPG, PNG, WebP or GIF, up to 8 MB each and 20 at a time. Large photos are scaled to 2400 pixels wide and their location data is removed.</span>
                        </span>
                        <span class="dash-upload__busy">Uploading…</span>
                        <noscript><button class="dash-btn dash-btn--primary dash-btn--sm" type="submit">Upload</button></noscript>
                    </form>
                @endcan

                @if ($media->isEmpty())
                    <div class="dash-empty">
                        <span class="dash-empty__icon"><x-dash.icon name="media" /></span>
                        <h2>{{ $search !== '' ? 'No images match “'.$search.'”' : 'No uploads yet' }}</h2>
                        <p>{{ $search !== '' ? 'Try another word, or clear the search.' : 'Images uploaded here or from the page editor appear in this library.' }}</p>
                    </div>
                @else
                    <ul class="dash-media">
                        @foreach ($media as $item)
                            <li @class(['dash-media__item', 'is-selected' => $selected?->is($item)])>
                                <a class="dash-media__link" href="{{ route('dashboard.media.index', array_filter(['media' => $item->id, 'q' => $search, 'page' => $media->currentPage() > 1 ? $media->currentPage() : null])) }}">
                                    <span class="dash-media__thumb"><img src="{{ $item->thumbUrl() }}" alt="" loading="lazy"></span>
                                    <span class="dash-media__name">{{ $item->name }}</span>
                                    <span class="dash-media__meta">
                                        {{ $item->width && $item->height ? $item->width.' × '.$item->height.' · ' : '' }}{{ $item->sizeLabel() }}
                                        @unless ($item->alt)
                                            <span class="dash-media__flag" title="No description for screen readers">No description</span>
                                        @endunless
                                    </span>
                                </a>
                                <span class="dash-media__actions">
                                    <a href="{{ $item->url() }}" target="_blank" rel="noopener" title="Open full size"><x-dash.icon name="external" /><span class="visually-hidden">Open {{ $item->name }} full size</span></a>
                                    <a href="{{ route('dashboard.media.download', $item) }}" title="Download"><x-dash.icon name="download" /><span class="visually-hidden">Download {{ $item->name }}</span></a>
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    @if ($media->hasPages())
                        <nav class="dash-pager" aria-label="Media pages">
                            <ul>
                                @if ($media->previousPageUrl())
                                    <li><a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ $media->previousPageUrl() }}" rel="prev">Newer</a></li>
                                @endif
                                @if ($media->nextPageUrl())
                                    <li><a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ $media->nextPageUrl() }}" rel="next">Older</a></li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                @endif
            </div>

            @if ($selected)
                <aside class="dash-card dash-media-detail" aria-label="Image details">
                    <div class="dash-media-detail__head">
                        <h2 class="dash-card__title">Image details</h2>
                        <a class="dash-iconbtn dash-iconbtn--light" href="{{ route('dashboard.media.index', array_filter(['q' => $search])) }}" title="Close"><x-dash.icon name="back" /><span class="visually-hidden">Close details</span></a>
                    </div>
                    <a class="dash-media-detail__preview" href="{{ $selected->url() }}" target="_blank" rel="noopener" title="Open full size"><img src="{{ $selected->thumbUrl() }}" alt=""></a>
                    <div class="dash-media-detail__actions">
                        <a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ $selected->url() }}" target="_blank" rel="noopener"><x-dash.icon name="external" /> View full size</a>
                        <a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ route('dashboard.media.download', $selected) }}"><x-dash.icon name="download" /> Download</a>
                        <button type="button" class="dash-btn dash-btn--ghost dash-btn--sm" data-copy="{{ url($selected->url()) }}"><x-dash.icon name="copy" /> Copy address</button>
                    </div>
                    <dl class="dash-media-detail__facts">
                        <div><dt>File</dt><dd class="dash-media-detail__file">{{ basename($selected->path) }} · {{ strtoupper(pathinfo($selected->path, PATHINFO_EXTENSION)) }}</dd></div>
                        <div><dt>Size</dt><dd>{{ $selected->width && $selected->height ? $selected->width.' × '.$selected->height.' px · ' : '' }}{{ $selected->sizeLabel() }}</dd></div>
                        <div><dt>Uploaded</dt><dd>{{ $selected->created_at?->format('j M Y, H:i') }}{{ $selected->uploader ? ' by '.$selected->uploader->name : '' }}</dd></div>
                        <div>
                            <dt>Used on</dt>
                            <dd>
                                @forelse ($usage as $use)
                                    <a href="{{ $use['url'] }}">{{ $use['label'] }}</a>@unless ($loop->last), @endunless
                                @empty
                                    Not used yet
                                @endforelse
                            </dd>
                        </div>
                    </dl>

                    @can('edit-content')
                        <form class="dash-media-detail__form" method="POST" action="{{ route('dashboard.media.update', $selected) }}">
                            @csrf
                            @method('PUT')
                            <div class="dash-field">
                                <label class="dash-label" for="media-name">Name</label>
                                <input class="input" id="media-name" name="name" value="{{ old('name', $selected->name) }}" maxlength="180" required>
                            </div>
                            <div class="dash-field">
                                <label class="dash-label" for="media-alt">Description <span>for screen readers</span></label>
                                <textarea class="input" id="media-alt" name="alt" rows="3" maxlength="300">{{ old('alt', $selected->alt) }}</textarea>
                                <p class="dash-hint">Suggested when this image is picked in the page editor. One sentence describing what it shows.</p>
                            </div>
                            <button class="dash-btn dash-btn--primary dash-btn--sm" type="submit"><x-dash.icon name="check" /> Save details</button>
                        </form>

                        <form method="POST" action="{{ route('dashboard.media.destroy', $selected) }}" onsubmit="return confirm('Delete this image? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button class="dash-btn dash-btn--danger dash-btn--sm" type="submit" @disabled($usage->isNotEmpty()) @if ($usage->isNotEmpty()) title="Replace it where it is used first" @endif><x-dash.icon name="trash" /> Delete image</button>
                        </form>
                    @endcan
                </aside>
            @endif
        </div>
</x-layouts.dashboard>
