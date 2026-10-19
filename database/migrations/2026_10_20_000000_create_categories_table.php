<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Categories become records edited in the dashboard (they were a fixed list). Posts point to one by
 * category_id; deleting a category leaves its posts uncategorised. The four original categories are
 * created here and existing posts are moved onto them.
 */
return new class extends Migration
{
    private const DEFAULTS = [
        'article' => ['Article', '#2BA3A0', 'Opinion and explanation on technology, governance and transformation.'],
        'analysis' => ['Analysis', '#C79A45', 'Deeper looks at a trend, a decision or a framework.'],
        'field-notes' => ['Field Notes', '#5B8DEF', 'Short observations from client work.'],
        'case-study' => ['Case Study', '#B4432E', 'How a real engagement was approached and what came of it.'],
    ];

    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('name', 80);
            $table->string('description', 300)->nullable();
            $table->string('color', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $order = 0;
        foreach (self::DEFAULTS as $slug => [$name, $color, $description]) {
            DB::table('categories')->insert([
                'slug' => $slug, 'name' => $name, 'color' => $color, 'description' => $description,
                'sort_order' => ++$order, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('slug')->constrained('categories')->nullOnDelete();
        });

        foreach (DB::table('categories')->pluck('id', 'slug') as $slug => $id) {
            DB::table('posts')->where('type', $slug)->update(['category_id' => $id]);
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('type', 30)->default('article')->index()->after('slug');
        });

        foreach (DB::table('categories')->pluck('slug', 'id') as $id => $slug) {
            DB::table('posts')->where('category_id', $id)->update(['type' => mb_substr($slug, 0, 30)]);
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('categories');
    }
};
