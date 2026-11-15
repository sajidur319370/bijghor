<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_list_shows_categories_with_product_counts(): void
    {
        $category = Category::factory()->create(['name_en' => 'Vegetable Seeds']);
        Product::factory()->count(2)->for($category)->create();

        Livewire::test(ListCategories::class)
            ->assertCanSeeTableRecords([$category])
            ->assertSee('Vegetable Seeds');
    }

    public function test_creating_a_category_fills_the_slug_from_the_english_name(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm(['name_en' => 'Herb Seeds', 'name_bn' => 'ভেষজ বীজ'])
            ->assertSchemaStateSet(['slug' => 'herb-seeds'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', ['slug' => 'herb-seeds', 'name_bn' => 'ভেষজ বীজ', 'is_active' => true]);
    }

    public function test_slug_must_be_unique(): void
    {
        Category::factory()->create(['slug' => 'herb-seeds']);

        Livewire::test(CreateCategory::class)
            ->fillForm(['name_en' => 'Herb Seeds', 'name_bn' => 'ভেষজ', 'slug' => 'herb-seeds'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_a_category_with_products_cannot_be_deleted_but_an_empty_one_can(): void
    {
        $used = Category::factory()->create();
        Product::factory()->for($used)->create()->delete();   // even a soft-deleted product blocks it
        $empty = Category::factory()->create();

        Livewire::test(ListCategories::class)
            ->assertActionDisabled(TestAction::make(DeleteAction::class)->table($used))
            ->callAction(TestAction::make(DeleteAction::class)->table($empty));

        $this->assertModelExists($used);
        $this->assertModelMissing($empty);
    }
}
