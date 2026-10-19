<x-layouts.app title="Page not found" description="The page you were looking for could not be found." :noindex="true">
    <section class="not-found surface-deep bg-grid">
        <div class="container">
            <span class="not-found__code mono">Error 404 · Page not found</span>
            <h1>This page isn’t where you expected. The rest of the blog is.</h1>
            <div class="cluster">
                <x-button :href="route('home')">Read the latest posts</x-button>
            </div>
        </div>
    </section>
</x-layouts.app>
