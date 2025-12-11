<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;


class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ========================================
            // BASIC PLANS
            // ========================================
            [
                'title' => 'Basic Monthly Plan',
                'description' => 'Perfect for individuals who want to explore our platform. Includes access to basic features, email support, and up to 5 projects. Ideal for freelancers and small teams getting started.',
                'price' => 9.99,
                'status' => 'active',
                'duration_days' => 30,
            ],
            [
                'title' => 'Basic Yearly Plan',
                'description' => 'Save 20% with our annual basic plan! All basic features for a full year. Includes priority email support and unlimited projects storage.',
                'price' => 95.88,
                'status' => 'active',
                'duration_days' => 365,
            ],

            // ========================================
            // PROFESSIONAL PLANS
            // ========================================
            [
                'title' => 'Professional Monthly Plan',
                'description' => 'Designed for growing businesses. Includes advanced analytics, API access, team collaboration tools, priority support, and up to 50 projects. Best for scaling teams.',
                'price' => 29.99,
                'status' => 'active',
                'duration_days' => 30,
            ],
            [
                'title' => 'Professional Yearly Plan',
                'description' => 'Our most popular plan! Save 25% annually. Full professional features, dedicated account manager, advanced integrations, and unlimited team members.',
                'price' => 269.88,
                'status' => 'active',
                'duration_days' => 365,
            ],

            // ========================================
            // ENTERPRISE PLANS
            // ========================================
            [
                'title' => 'Enterprise Monthly Plan',
                'description' => 'Complete enterprise solution. Custom integrations, SSO authentication, advanced security features, SLA guarantee, 24/7 phone support, and unlimited everything.',
                'price' => 99.99,
                'status' => 'active',
                'duration_days' => 30,
            ],
            [
                'title' => 'Enterprise Yearly Plan',
                'description' => 'Premium enterprise package with maximum savings. Includes all enterprise features, custom development hours, training sessions, and dedicated success manager.',
                'price' => 899.88,
                'status' => 'active',
                'duration_days' => 365,
            ],

            // ========================================
            // SPECIAL OFFERS
            // ========================================
            [
                'title' => 'Starter Trial Plan',
                'description' => 'Try our platform risk-free for 7 days! Full access to professional features. No credit card required. Perfect for evaluating our solution.',
                'price' => 0.00,
                'status' => 'active',
                'duration_days' => 7,
            ],
            [
                'title' => 'Premium Lifetime Access',
                'description' => 'One-time payment for lifetime access! Includes all current and future features, lifetime updates, priority support forever. Limited availability.',
                'price' => 499.99,
                'status' => 'active',
                'duration_days' => 36500, // Lifetime (100 years)
            ],
            [
                'title' => 'Student Discount Plan',
                'description' => 'Special pricing for students and educators. Full professional features at 50% off. Valid student ID required for verification.',
                'price' => 14.99,
                'status' => 'active',
                'duration_days' => 30,
            ],
            [
                'title' => 'Startup Bundle',
                'description' => 'Special package for registered startups. Includes enterprise features at startup-friendly pricing. Apply with your startup credentials.',
                'price' => 49.99,
                'status' => 'active',
                'duration_days' => 30,
            ],

            // ========================================
            // COURSE PRODUCTS
            // ========================================
            [
                'title' => 'Laravel Mastery Course',
                'description' => 'Complete Laravel development course. 50+ hours of video content, real-world projects, certificate of completion. From basics to advanced concepts.',
                'price' => 199.99,
                'status' => 'active',
                'duration_days' => 365,
            ],
            [
                'title' => 'Vue.js Complete Guide',
                'description' => 'Master Vue.js 3 with Composition API. 30+ hours of content, 10 projects, source code included. Build modern reactive applications.',
                'price' => 149.99,
                'status' => 'active',
                'duration_days' => 365,
            ],
            [
                'title' => 'Full Stack Developer Bundle',
                'description' => 'Complete web development bundle. Laravel + Vue.js + TailwindCSS + Docker. 100+ hours of content. Become a full-stack developer.',
                'price' => 299.99,
                'status' => 'active',
                'duration_days' => 365,
            ],

            // ========================================
            // INACTIVE / DISCONTINUED PRODUCTS
            // ========================================
            [
                'title' => 'Legacy Basic Plan',
                'description' => 'Our original basic plan. This product has been discontinued and replaced with the new Basic Monthly Plan with improved features.',
                'price' => 7.99,
                'status' => 'inactive',
                'duration_days' => 30,
            ],
            [
                'title' => 'Beta Access Program',
                'description' => 'Early access to beta features. This program has ended as all features are now in the main platform.',
                'price' => 4.99,
                'status' => 'inactive',
                'duration_days' => 30,
            ],
            [
                'title' => 'Black Friday Special 2023',
                'description' => 'Limited time Black Friday offer from 2023. 70% discount on yearly plans. This promotion has expired.',
                'price' => 79.99,
                'status' => 'inactive',
                'duration_days' => 365,
            ],

            // ========================================
            // ADD-ONS
            // ========================================
            [
                'title' => 'Extra Storage Add-on',
                'description' => 'Need more storage? Add 100GB of additional cloud storage to any plan. Perfect for teams with large file requirements.',
                'price' => 9.99,
                'status' => 'active',
                'duration_days' => 30,
            ],
            [
                'title' => 'Priority Support Add-on',
                'description' => 'Get priority support with guaranteed 2-hour response time. Dedicated support channel and screen sharing assistance.',
                'price' => 19.99,
                'status' => 'active',
                'duration_days' => 30,
            ],
            [
                'title' => 'API Premium Access',
                'description' => 'Unlock advanced API features. 100,000 API calls/month, webhooks, custom integrations, and developer support.',
                'price' => 39.99,
                'status' => 'active',
                'duration_days' => 30,
            ],
            [
                'title' => 'White Label Solution',
                'description' => 'Remove our branding and add yours. Custom domain, branded emails, and customizable interface. Enterprise add-on.',
                'price' => 149.99,
                'status' => 'active',
                'duration_days' => 30,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}
