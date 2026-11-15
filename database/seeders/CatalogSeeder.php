<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Delivery zones, the 4 categories and sample products.
 * Safe to run more than once (updateOrCreate on a unique key).
 * Replace the sample products with the real list through /admin later.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['Inside Dhaka', 'ঢাকার ভিতরে', 70, 1],
            ['Outside Dhaka', 'ঢাকার বাইরে', 130, 2],
        ];
        foreach ($zones as [$en, $bn, $charge, $sort]) {
            DeliveryZone::updateOrCreate(
                ['name_en' => $en],
                ['name_bn' => $bn, 'charge' => $charge, 'sort_order' => $sort, 'is_active' => true],
            );
        }

        $categories = [
            'vegetable-seeds' => ['Vegetable Seeds', 'সবজি বীজ', 1],
            'flower-seeds' => ['Flower Seeds', 'ফুলের বীজ', 2],
            'fruit-seeds' => ['Fruit Seeds', 'ফলের বীজ', 3],
            'herb-seeds' => ['Herb Seeds', 'ভেষজ বীজ', 4],
        ];
        $categoryIds = [];
        foreach ($categories as $slug => [$en, $bn, $sort]) {
            $categoryIds[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                ['name_en' => $en, 'name_bn' => $bn, 'sort_order' => $sort, 'is_active' => true],
            )->id;
        }

        foreach ($this->products() as $p) {
            $category = $p['category'];
            unset($p['category']);

            Product::withTrashed()->updateOrCreate(
                ['sku' => $p['sku']],
                $p + ['category_id' => $categoryIds[$category], 'is_active' => true],
            );
        }
    }

    /** Stock is varied on purpose: 0 (out-of-stock badge) and 8 (low-stock report) for testing. */
    private function products(): array
    {
        return [
            [
                'category' => 'vegetable-seeds', 'sku' => 'VEG-001', 'slug' => 'red-amaranth-10g',
                'name_en' => 'Red Amaranth (Lal Shak)', 'name_bn' => 'লাল শাক',
                'description_en' => 'Fast-growing leafy vegetable. Easy to grow in pots or beds.',
                'description_bn' => 'দ্রুত বর্ধনশীল শাক। টবে বা বেডে সহজে চাষ করা যায়।',
                'packet_size_en' => '10 g', 'packet_size_bn' => '১০ গ্রাম',
                'price' => 40, 'sale_price' => null, 'stock' => 200, 'germination_rate' => 85,
                'sowing_season_en' => 'All year', 'sowing_season_bn' => 'সারা বছর',
                'days_to_harvest' => 30, 'is_featured' => true,
            ],
            [
                'category' => 'vegetable-seeds', 'sku' => 'VEG-002', 'slug' => 'tomato-hybrid-1g',
                'name_en' => 'Tomato (Hybrid)', 'name_bn' => 'টমেটো (হাইব্রিড)',
                'description_en' => 'High-yield hybrid tomato with firm, round fruit.',
                'description_bn' => 'উচ্চ ফলনশীল হাইব্রিড টমেটো, শক্ত ও গোলাকার ফল।',
                'packet_size_en' => '1 g', 'packet_size_bn' => '১ গ্রাম',
                'price' => 120, 'sale_price' => 99, 'stock' => 150, 'germination_rate' => 90,
                'sowing_season_en' => 'September - November', 'sowing_season_bn' => 'সেপ্টেম্বর - নভেম্বর',
                'days_to_harvest' => 75, 'is_featured' => true,
            ],
            [
                'category' => 'vegetable-seeds', 'sku' => 'VEG-003', 'slug' => 'spinach-25g',
                'name_en' => 'Spinach (Palong Shak)', 'name_bn' => 'পালং শাক',
                'description_en' => 'Tender winter spinach. Pick leaves again and again.',
                'description_bn' => 'নরম শীতকালীন পালং শাক। বারবার পাতা তোলা যায়।',
                'packet_size_en' => '25 g', 'packet_size_bn' => '২৫ গ্রাম',
                'price' => 50, 'sale_price' => 45, 'stock' => 180, 'germination_rate' => 85,
                'sowing_season_en' => 'October - December', 'sowing_season_bn' => 'অক্টোবর - ডিসেম্বর',
                'days_to_harvest' => 40, 'is_featured' => true,
            ],
            [
                'category' => 'vegetable-seeds', 'sku' => 'VEG-004', 'slug' => 'bottle-gourd-10g',
                'name_en' => 'Bottle Gourd (Lau)', 'name_bn' => 'লাউ',
                'description_en' => 'Popular climbing gourd. Grows well on a trellis.',
                'description_bn' => 'জনপ্রিয় লতানো সবজি। মাচায় ভালো হয়।',
                'packet_size_en' => '10 g', 'packet_size_bn' => '১০ গ্রাম',
                'price' => 60, 'sale_price' => null, 'stock' => 120, 'germination_rate' => 80,
                'sowing_season_en' => 'August - October', 'sowing_season_bn' => 'আগস্ট - অক্টোবর',
                'days_to_harvest' => 65, 'is_featured' => false,
            ],
            [
                'category' => 'vegetable-seeds', 'sku' => 'VEG-005', 'slug' => 'green-chilli-5g',
                'name_en' => 'Green Chilli', 'name_bn' => 'কাঁচা মরিচ',
                'description_en' => 'Hot green chilli for everyday cooking.',
                'description_bn' => 'প্রতিদিনের রান্নার জন্য ঝাল কাঁচা মরিচ।',
                'packet_size_en' => '5 g', 'packet_size_bn' => '৫ গ্রাম',
                'price' => 80, 'sale_price' => null, 'stock' => 100, 'germination_rate' => 75,
                'sowing_season_en' => 'September - November', 'sowing_season_bn' => 'সেপ্টেম্বর - নভেম্বর',
                'days_to_harvest' => 90, 'is_featured' => false,
            ],
            [
                'category' => 'flower-seeds', 'sku' => 'FLW-001', 'slug' => 'marigold-50-seeds',
                'name_en' => 'Marigold', 'name_bn' => 'গাঁদা ফুল',
                'description_en' => 'Bright orange marigold. Long-lasting winter blooms.',
                'description_bn' => 'উজ্জ্বল কমলা গাঁদা ফুল। শীতে অনেক দিন ফোটে।',
                'packet_size_en' => '50 seeds', 'packet_size_bn' => '৫০টি বীজ',
                'price' => 60, 'sale_price' => null, 'stock' => 100, 'germination_rate' => 80,
                'sowing_season_en' => 'October - December', 'sowing_season_bn' => 'অক্টোবর - ডিসেম্বর',
                'days_to_harvest' => 60, 'is_featured' => false,
            ],
            [
                'category' => 'flower-seeds', 'sku' => 'FLW-002', 'slug' => 'sunflower-20-seeds',
                'name_en' => 'Sunflower', 'name_bn' => 'সূর্যমুখী',
                'description_en' => 'Tall sunflower with large yellow heads.',
                'description_bn' => 'বড় হলুদ ফুলের লম্বা সূর্যমুখী।',
                'packet_size_en' => '20 seeds', 'packet_size_bn' => '২০টি বীজ',
                'price' => 70, 'sale_price' => null, 'stock' => 90, 'germination_rate' => 85,
                'sowing_season_en' => 'November - December', 'sowing_season_bn' => 'নভেম্বর - ডিসেম্বর',
                'days_to_harvest' => 90, 'is_featured' => true,
            ],
            [
                'category' => 'fruit-seeds', 'sku' => 'FRT-001', 'slug' => 'watermelon-hybrid-10-seeds',
                'name_en' => 'Watermelon (Hybrid)', 'name_bn' => 'তরমুজ (হাইব্রিড)',
                'description_en' => 'Sweet, red-fleshed hybrid watermelon.',
                'description_bn' => 'মিষ্টি, লাল শাঁসের হাইব্রিড তরমুজ।',
                'packet_size_en' => '10 seeds', 'packet_size_bn' => '১০টি বীজ',
                'price' => 150, 'sale_price' => 130, 'stock' => 60, 'germination_rate' => 85,
                'sowing_season_en' => 'December - February', 'sowing_season_bn' => 'ডিসেম্বর - ফেব্রুয়ারি',
                'days_to_harvest' => 85, 'is_featured' => true,
            ],
            [
                'category' => 'fruit-seeds', 'sku' => 'FRT-002', 'slug' => 'papaya-20-seeds',
                'name_en' => 'Papaya', 'name_bn' => 'পেঁপে',
                'description_en' => 'Fast-fruiting papaya for home gardens.',
                'description_bn' => 'বাড়ির বাগানের জন্য দ্রুত ফল দেওয়া পেঁপে।',
                'packet_size_en' => '20 seeds', 'packet_size_bn' => '২০টি বীজ',
                'price' => 90, 'sale_price' => null, 'stock' => 70, 'germination_rate' => 75,
                'sowing_season_en' => 'September - October', 'sowing_season_bn' => 'সেপ্টেম্বর - অক্টোবর',
                'days_to_harvest' => 240, 'is_featured' => false,
            ],
            [
                'category' => 'herb-seeds', 'sku' => 'HRB-001', 'slug' => 'coriander-25g',
                'name_en' => 'Coriander (Dhonia)', 'name_bn' => 'ধনিয়া',
                'description_en' => 'Fragrant coriander leaves for curries and salads.',
                'description_bn' => 'তরকারি ও সালাদের জন্য সুগন্ধি ধনিয়া পাতা।',
                'packet_size_en' => '25 g', 'packet_size_bn' => '২৫ গ্রাম',
                'price' => 30, 'sale_price' => null, 'stock' => 250, 'germination_rate' => 80,
                'sowing_season_en' => 'October - December', 'sowing_season_bn' => 'অক্টোবর - ডিসেম্বর',
                'days_to_harvest' => 40, 'is_featured' => true,
            ],
            [
                'category' => 'herb-seeds', 'sku' => 'HRB-002', 'slug' => 'tulsi-2g',
                'name_en' => 'Holy Basil (Tulsi)', 'name_bn' => 'তুলসী',
                'description_en' => 'Holy basil for tea and home remedies.',
                'description_bn' => 'চা ও ঘরোয়া ব্যবহারের জন্য তুলসী।',
                'packet_size_en' => '2 g', 'packet_size_bn' => '২ গ্রাম',
                'price' => 45, 'sale_price' => null, 'stock' => 8, 'germination_rate' => 80,
                'sowing_season_en' => 'February - April', 'sowing_season_bn' => 'ফেব্রুয়ারি - এপ্রিল',
                'days_to_harvest' => 60, 'is_featured' => false,
            ],
            [
                'category' => 'herb-seeds', 'sku' => 'HRB-003', 'slug' => 'mint-half-g',
                'name_en' => 'Mint (Pudina)', 'name_bn' => 'পুদিনা',
                'description_en' => 'Cool, fresh mint. Grows well in pots.',
                'description_bn' => 'ঠান্ডা, সতেজ পুদিনা। টবে ভালো হয়।',
                'packet_size_en' => '0.5 g', 'packet_size_bn' => '০.৫ গ্রাম',
                'price' => 60, 'sale_price' => null, 'stock' => 0, 'germination_rate' => 70,
                'sowing_season_en' => 'All year', 'sowing_season_bn' => 'সারা বছর',
                'days_to_harvest' => 70, 'is_featured' => false,
            ],
        ];
    }
}
