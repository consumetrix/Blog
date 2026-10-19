<x-layouts.dashboard title="Activity">
    <header class="dash-head">
        <div>
            <p class="dash-eyebrow">Admin</p>
            <h1>Activity</h1>
            <p class="dash-sub">Who changed what, newest first. Times are Accra time (GMT).</p>
        </div>
        <a class="dash-btn dash-btn--ghost" href="{{ request()->fullUrlWithQuery(['export' => 'csv', 'page' => null]) }}"><x-dash.icon name="download" /> Download as CSV</a>
    </header>

    <form method="GET" class="dash-filters" role="search" aria-label="Filter activity">
        <div class="dash-field">
            <label for="f-person">Person</label>
            <select id="f-person" name="person" class="input">
                <option value="">Everyone</option>
                @foreach ($people as $person)
                    <option value="{{ $person->id }}" @selected(($filters['person'] ?? '') === (string) $person->id)>{{ $person->name }}</option>
                @endforeach
                <option value="system" @selected(($filters['person'] ?? '') === 'system')>System and failed sign-ins</option>
            </select>
        </div>
        <div class="dash-field">
            <label for="f-area">Area</label>
            <select id="f-area" name="area" class="input">
                <option value="">Everything</option>
                @foreach ($areas as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['area'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="dash-field">
            <label for="f-from">From</label>
            <input id="f-from" type="date" name="from" class="input" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="dash-field">
            <label for="f-to">To</label>
            <input id="f-to" type="date" name="to" class="input" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="dash-field dash-filters__search">
            <label for="f-q">Page, post, image or email</label>
            <input id="f-q" type="search" name="q" class="input" value="{{ $filters['q'] ?? '' }}" maxlength="100">
        </div>
        <div class="dash-filters__actions">
            <button type="submit" class="dash-btn dash-btn--primary dash-btn--sm">Filter</button>
            @if (array_filter($filters))
                <a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ route('dashboard.activity') }}">Clear</a>
            @endif
        </div>
    </form>

    @if ($entries->isEmpty())
        <div class="dash-empty">
            <span class="dash-empty__icon"><x-dash.icon name="activity" /></span>
            @if (array_filter($filters))
                <h2>Nothing matches</h2>
                <p>Try a wider date range or clear the filters.</p>
            @else
                <h2>Nothing yet</h2>
                <p>Sign-ins, post edits, publishing, uploads and user changes will appear here.</p>
            @endif
        </div>
    @else
        <p class="dash-activity__count">{{ number_format($entries->total()) }} {{ Str::plural('entry', $entries->total()) }}</p>
        <ol class="dash-activity">
            @foreach ($entries as $entry)
                @php($link = $entry->link($existing))
                <li>
                    <time datetime="{{ $entry->created_at?->toIso8601String() }}" title="{{ $entry->created_at?->format('j M Y, H:i') }}">{{ $entry->created_at?->diffForHumans() }}</time>
                    <p @if ($entry->ip_address) title="From {{ $entry->ip_address }}" @endif>
                        <strong>{{ $entry->user?->name ?? ($entry->user_id ? 'A deleted user' : 'Someone') }}</strong>
                        {{ $entry->label() }}
                        @if ($entry->subject)
                            @if ($link)
                                <a href="{{ $link }}"><em>{{ $entry->subject }}</em></a>
                            @else
                                <em>{{ $entry->subject }}</em>
                            @endif
                        @endif
                        @if ($detail = $entry->detail())
                            <span class="dash-activity__detail">{{ $detail }}</span>
                        @endif
                    </p>
                </li>
            @endforeach
        </ol>
        @if ($entries->hasPages())
            <nav class="dash-pager" aria-label="Activity pages">
                <ul>
                    @if ($entries->previousPageUrl())
                        <li><a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ $entries->previousPageUrl() }}" rel="prev">Newer</a></li>
                    @endif
                    @if ($entries->nextPageUrl())
                        <li><a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ $entries->nextPageUrl() }}" rel="next">Older</a></li>
                    @endif
                </ul>
            </nav>
        @endif
    @endif
</x-layouts.dashboard>
