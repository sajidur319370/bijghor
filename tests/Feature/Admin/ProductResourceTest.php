<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_a_product_added_in_admin_with_a_photo_appears_in_the_shop(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'category_id' => $category->id,
                'name_en' => 'Okra',
                'name_bn' => 'ঢেঁড়স',
                'price' => 55,
                'stock' => 40,
                'packet_size_en' => '20 g',
                'packet_size_bn' => '২০ গ্রাম',
                'image' => UploadedFile::fake()->image('okra.jpg', 800, 800),
            ])
            ->assertSchemaStateSet(['slug' => 'okra'])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('slug', 'okra')->sole();
        $this->assertStringStartsWith('products/', $product->image);
        Storage::disk('uploads')->assertExists($product->image);

        $this->get(route('product', 'okra'))
            ->assertOk()
            ->assertSee('ঢেঁড়স')
            ->assertSee($product->image, false);
    }

    public function test_gallery_photos_are_saved_in_order(): void
    {
        $product = Product::factory()->create();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'images' => [
                    ['path' => [UploadedFile::fake()->image('a.jpg')]],
                    ['path' => [UploadedFile::fake()->image('b.jpg')]],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([1, 2], $product->images()->pluck('sort_order')->all());
    }

    public function test_sale_price_must_be_lower_than_the_price(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'category_id' => Category::factory()->create()->id,
                'name_en' => 'Okra', 'name_bn' => 'ঢেঁড়স', 'packet_size_en' => '20 g', 'packet_size_bn' => '২০ গ্রাম',
                'price' => 50, 'sale_price' => 60, 'stock' => 5,
            ])
            ->call('create')
            ->assertHasFormErrors(['sale_price']);
    }

    public function test_svg_files_are_not_accepted_as_photos(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'category_id' => Category::factory()->create()->id,
                'name_en' => 'Okra', 'name_bn' => 'ঢেঁড়স', 'packet_size_en' => '20 g', 'packet_size_bn' => '২০ গ্রাম',
                'price' => 50, 'stock' => 5,
                'image' => UploadedFile::fake()->create('evil.svg', 1, 'image/svg+xml'),
            ])
            ->call('create')
            ->assertHasFormErrors(['image']);
    }

    public function test_low_stock_filter_shows_only_products_with_ten_or_fewer(): void
    {
        $low = Product::factory()->create(['stock' => 8]);
        $plenty = Product::factory()->create(['stock' => 80]);

        Livewire::test(ListProducts::class)
            ->filterTable('low_stock')
            ->assertCanSeeTableRecords([$low])
            ->assertCanNotSeeTableRecords([$plenty]);
    }

    public function test_hiding_a_product_in_admin_removes_it_from_the_shop(): void
    {
        $product = Product::factory()->create(['slug' => 'basil']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get(route('product', 'basil'))->assertNotFound();
    }
}
