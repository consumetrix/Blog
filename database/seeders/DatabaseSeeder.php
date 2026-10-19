<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Fake articles so a fresh install has something to show. They are ordinary posts: edit or delete them
 * from Dashboard → Posts. Safe to run again (posts are matched by slug, so deleted ones come back; run it once).
 *
 *   php artisan db:seed --force
 *
 * The dashboard account is created with `php artisan blog:admin you@example.com`.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        foreach ($this->articles() as $i => $article) {
            Post::updateOrCreate(['slug' => $article['slug']], [
                'category_id' => $categories[$article['category']] ?? null,
                'title' => $article['title'],
                'excerpt' => $article['excerpt'],
                'body' => $article['body'],
                'status' => $article['status'] ?? 'published',
                // Newest first; a draft has no date and a scheduled one is in the future.
                'published_at' => match ($article['status'] ?? 'published') {
                    'draft' => null,
                    default => $article['days'] >= 0 ? now()->subDays($article['days']) : now()->addDays(-$article['days']),
                },
            ]);
        }
    }

    /** @return list<array<string, mixed>> */
    private function articles(): array
    {
        return [
            [
                'slug' => 'sample-why-small-teams-need-a-data-policy',
                'category' => 'article', 'days' => 3,
                'title' => 'Sample: Why Small Teams Need a Data Policy',
                'excerpt' => 'A one-page policy beats a forty-page binder nobody reads.',
                'body' => '<p>This is a sample article. Edit or delete it from the dashboard.</p><p>Most small teams treat data policy as something for big companies. In practice, the questions are the same at any size: who can see customer records, how long do we keep them, and what happens when someone leaves?</p><h2>Start with three rules</h2><ul><li>Name an owner for every system that holds customer data.</li><li>Decide how long each kind of record is kept.</li><li>Remove access the day someone leaves.</li></ul><p>Write them on one page and review them twice a year. A short policy that is followed is worth more than a long one that is not.</p>',
            ],
            [
                'slug' => 'sample-five-questions-before-buying-software',
                'category' => 'article', 'days' => 10,
                'title' => 'Sample: Five Questions to Ask Before Buying Software',
                'excerpt' => 'Before the demo, agree what problem the tool must solve.',
                'body' => '<p>This is a sample article about choosing software.</p><p>Demos are designed to impress. To judge a tool fairly, bring your own questions and make the vendor answer them.</p><ol><li>Which process does this replace, and who owns it today?</li><li>What does it cost after year one?</li><li>How do we get our data out if we leave?</li><li>Who inside the business will run it day to day?</li><li>How will we know in six months that it worked?</li></ol><blockquote>If nobody can say who will own it, do not buy it yet.</blockquote>',
            ],
            [
                'slug' => 'sample-reading-a-vendor-contract',
                'category' => 'analysis', 'days' => 17,
                'title' => 'Sample: Reading a Vendor Contract Without a Lawyer',
                'excerpt' => 'The clauses that decide who pays when something goes wrong.',
                'body' => '<p>This is a sample analysis piece.</p><p>Contracts look uniform, but three clauses decide most disputes: the service levels, the limit of liability and the exit terms.</p><h2>Service levels</h2><p>A promise of "99.9% uptime" means little without a remedy. Look for the credit you receive when it is missed, and how you claim it.</p><h2>Limit of liability</h2><p>Many contracts cap damages at a few months of fees. That may be far below what an outage costs you, so decide whether the cap is acceptable before you sign.</p><h2>Exit terms</h2><p>Check notice periods, data return and any fees for leaving early.</p>',
            ],
            [
                'slug' => 'sample-the-cost-of-manual-reporting',
                'category' => 'analysis', 'days' => 24,
                'title' => 'Sample: The Real Cost of Manual Reporting',
                'excerpt' => 'Hours spent copying numbers between spreadsheets add up quickly.',
                'body' => '<p>This is a sample analysis article.</p><p>Take a monthly report that needs six hours of copying, checking and formatting. Over a year that is nine working days, for one report, before counting the errors that slip through.</p><p>Automating the repeated steps rarely needs a large project. Start with the report that hurts most and measure the hours before and after.</p>',
            ],
            [
                'slug' => 'sample-notes-from-a-warehouse-visit',
                'category' => 'field-notes', 'days' => 31,
                'title' => 'Sample: Notes From a Warehouse Visit',
                'excerpt' => 'What a morning on the floor taught us about stock counts.',
                'body' => '<p>This is a sample field note.</p><p>We spent a morning with a warehouse team that counted stock on paper and typed the numbers in at the end of the day. Nobody was careless. The process simply made mistakes easy.</p><p>Two small changes helped: counting against a printed list by location, and entering numbers on a phone at the shelf.</p>',
            ],
            [
                'slug' => 'sample-what-we-learned-in-a-board-workshop',
                'category' => 'field-notes', 'days' => 38,
                'title' => 'Sample: What We Learned in a Board Workshop',
                'excerpt' => 'Directors do not need more data. They need the right three numbers.',
                'body' => '<p>This is a sample field note from a workshop.</p><p>The board had a forty-slide pack every quarter. When we asked which slide changed a decision last year, the room went quiet.</p><p>We replaced the pack with one page: three numbers, two risks and one decision needed.</p>',
            ],
            [
                'slug' => 'sample-moving-a-clinic-to-digital-records',
                'category' => 'case-study', 'days' => 45,
                'title' => 'Sample: Moving a Clinic to Digital Records',
                'excerpt' => 'A made-up example of how a staged rollout reduces risk.',
                'body' => '<p>This is a fictional case study, written as a sample.</p><h2>The situation</h2><p>A small clinic kept patient files on paper. Finding a record could take ten minutes, and files were sometimes misplaced.</p><h2>The approach</h2><p>We scanned new patients first, ran paper and digital side by side for a month, then migrated older records by appointment date.</p><h2>The result</h2><p>Records were found in seconds, and the paper store was emptied within a quarter.</p>',
            ],
            [
                'slug' => 'sample-a-draft-you-can-publish',
                'category' => 'article', 'days' => 0, 'status' => 'draft',
                'title' => 'Sample: A Draft You Can Publish',
                'excerpt' => 'This one is saved as a draft, so visitors cannot see it.',
                'body' => '<p>This sample is a draft. Open it in the dashboard and press Publish to see how it appears on the blog.</p>',
            ],
            [
                'slug' => 'sample-a-scheduled-post',
                'category' => 'article', 'days' => -7,
                'title' => 'Sample: A Scheduled Post',
                'excerpt' => 'Published, but dated a week ahead, so it goes live later.',
                'body' => '<p>This sample is scheduled. It is marked as published with a date in the future, so it appears on the blog when that date arrives.</p>',
            ],
        ];
    }
}
