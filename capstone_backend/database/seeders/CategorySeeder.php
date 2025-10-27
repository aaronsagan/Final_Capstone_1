<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Education',
                'description' => 'Supporting educational initiatives, scholarships, and learning programs',
                'icon' => 'GraduationCap',
                'color' => '#3B82F6',
                'sort_order' => 1,
            ],
            [
                'name' => 'Health & Medical',
                'description' => 'Healthcare services, medical equipment, and health awareness programs',
                'icon' => 'Heart',
                'color' => '#EF4444',
                'sort_order' => 2,
            ],
            [
                'name' => 'Environment',
                'description' => 'Environmental conservation, climate action, and sustainability projects',
                'icon' => 'Leaf',
                'color' => '#10B981',
                'sort_order' => 3,
            ],
            [
                'name' => 'Disaster Relief',
                'description' => 'Emergency response, disaster recovery, and humanitarian aid',
                'icon' => 'Shield',
                'color' => '#F59E0B',
                'sort_order' => 4,
            ],
            [
                'name' => 'Poverty & Hunger',
                'description' => 'Food security, poverty alleviation, and basic needs support',
                'icon' => 'Users',
                'color' => '#8B5CF6',
                'sort_order' => 5,
            ],
            [
                'name' => 'Children & Youth',
                'description' => 'Child welfare, youth development, and family support programs',
                'icon' => 'Baby',
                'color' => '#EC4899',
                'sort_order' => 6,
            ],
            [
                'name' => 'Elderly Care',
                'description' => 'Senior citizen support, elderly care facilities, and aging services',
                'icon' => 'UserCheck',
                'color' => '#6B7280',
                'sort_order' => 7,
            ],
            [
                'name' => 'Animal Welfare',
                'description' => 'Animal rescue, wildlife conservation, and pet care programs',
                'icon' => 'Dog',
                'color' => '#F97316',
                'sort_order' => 8,
            ],
            [
                'name' => 'Community Development',
                'description' => 'Infrastructure, community building, and local development projects',
                'icon' => 'Building',
                'color' => '#06B6D4',
                'sort_order' => 9,
            ],
            [
                'name' => 'Arts & Culture',
                'description' => 'Cultural preservation, arts programs, and creative initiatives',
                'icon' => 'Palette',
                'color' => '#84CC16',
                'sort_order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
