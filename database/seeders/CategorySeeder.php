<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $categories = [
            ['name' => 'Infrastructure', 'name_hi' => 'बुनियादी ढांचा', 'slug' => 'infrastructure', 'icon' => 'building', 'description' => 'Roads, bridges, public buildings, and urban development', 'description_hi' => 'सड़कें, पुल, सार्वजनिक भवन और शहरी विकास', 'sort_order' => 0],
            ['name' => 'Water & Sanitation', 'name_hi' => 'जल एवं स्वच्छता', 'slug' => 'water-sanitation', 'icon' => 'droplets', 'description' => 'Water supply, drainage, sewage, and cleanliness', 'description_hi' => 'जल आपूर्ति, जल निकासी, सीवेज और स्वच्छता', 'sort_order' => 1],
            ['name' => 'Education', 'name_hi' => 'शिक्षा', 'slug' => 'education', 'icon' => 'graduation-cap', 'description' => 'Schools, colleges, educational policies, and literacy', 'description_hi' => 'विद्यालय, महाविद्यालय, शैक्षिक नीतियां और साक्षरता', 'sort_order' => 2],
            ['name' => 'Healthcare', 'name_hi' => 'स्वास्थ्य', 'slug' => 'healthcare', 'icon' => 'heart-pulse', 'description' => 'Hospitals, public health, medical facilities, and health policies', 'description_hi' => 'अस्पताल, सार्वजनिक स्वास्थ्य, चिकित्सा सुविधाएं और स्वास्थ्य नीतियां', 'sort_order' => 3],
            ['name' => 'Environment', 'name_hi' => 'पर्यावरण', 'slug' => 'environment', 'icon' => 'trees', 'description' => 'Pollution, waste management, green initiatives, and conservation', 'description_hi' => 'प्रदूषण, कचरा प्रबंधन, हरित पहल और संरक्षण', 'sort_order' => 4],
            ['name' => 'Corruption', 'name_hi' => 'भ्रष्टाचार', 'slug' => 'corruption', 'icon' => 'shield-alert', 'description' => 'Bribery, misuse of public funds, and transparency issues', 'description_hi' => 'रिश्वतखोरी, सार्वजनिक धन का दुरुपयोग और पारदर्शिता के मुद्दे', 'sort_order' => 5],
            ['name' => 'Transportation', 'name_hi' => 'परिवहन', 'slug' => 'transportation', 'icon' => 'bus', 'description' => 'Public transport, traffic management, and connectivity', 'description_hi' => 'सार्वजनिक परिवहन, यातायात प्रबंधन और कनेक्टिविटी', 'sort_order' => 6],
            ['name' => 'Law & Order', 'name_hi' => 'कानून व्यवस्था', 'slug' => 'law-order', 'icon' => 'scale', 'description' => 'Policing, crime, safety, and justice system', 'description_hi' => 'पुलिस व्यवस्था, अपराध, सुरक्षा और न्याय प्रणाली', 'sort_order' => 7],
            ['name' => 'Employment', 'name_hi' => 'रोज़गार', 'slug' => 'employment', 'icon' => 'briefcase', 'description' => 'Jobs, unemployment, skill development, and labor rights', 'description_hi' => 'नौकरियां, बेरोज़गारी, कौशल विकास और श्रमिक अधिकार', 'sort_order' => 8],
            ['name' => "Women's Safety", 'name_hi' => 'महिला सुरक्षा', 'slug' => 'womens-safety', 'icon' => 'shield-check', 'description' => 'Women\'s security, harassment, and gender equality', 'description_hi' => 'महिलाओं की सुरक्षा, उत्पीड़न और लैंगिक समानता', 'sort_order' => 9],
            ['name' => 'Agriculture', 'name_hi' => 'कृषि', 'slug' => 'agriculture', 'icon' => 'wheat', 'description' => 'Farming, crop prices, subsidies, and rural development', 'description_hi' => 'खेती, फसल मूल्य, सब्सिडी और ग्रामीण विकास', 'sort_order' => 10],
            ['name' => 'Digital Governance', 'name_hi' => 'डिजिटल शासन', 'slug' => 'digital-governance', 'icon' => 'monitor-smartphone', 'description' => 'E-governance, digital services, and technology initiatives', 'description_hi' => 'ई-गवर्नेंस, डिजिटल सेवाएं और प्रौद्योगिकी पहल', 'sort_order' => 11],
            ['name' => 'Housing', 'name_hi' => 'आवास', 'slug' => 'housing', 'icon' => 'home', 'description' => 'Affordable housing, slum rehabilitation, and urban planning', 'description_hi' => 'किफायती आवास, झुग्गी पुनर्वास और शहरी नियोजन', 'sort_order' => 12],
            ['name' => 'Energy', 'name_hi' => 'ऊर्जा', 'slug' => 'energy', 'icon' => 'zap', 'description' => 'Electricity, renewable energy, power cuts, and energy policy', 'description_hi' => 'बिजली, नवीकरणीय ऊर्जा, बिजली कटौती और ऊर्जा नीति', 'sort_order' => 13],
            ['name' => 'Disability Rights', 'name_hi' => 'दिव्यांग अधिकार', 'slug' => 'disability-rights', 'icon' => 'accessibility', 'description' => 'Accessibility, disability welfare, and inclusive policies', 'description_hi' => 'सुगम्यता, दिव्यांग कल्याण और समावेशी नीतियां', 'sort_order' => 14],
        ];

        foreach ($categories as &$category) {
            $category['created_at'] = $now;
            $category['updated_at'] = $now;
        }

        DB::table('categories')->insert($categories);
    }
}
