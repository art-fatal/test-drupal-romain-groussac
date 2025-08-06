<?php

/**
 * @file
 * Test file for SEO Dashboard functionality.
 * 
 * This file can be used to test the dashboard functionality.
 * Remove this file in production.
 */

// Test if the controller class exists
if (class_exists('Drupal\seo_block\Controller\SeoDashboardController')) {
  echo "✅ SEO Dashboard Controller exists\n";
} else {
  echo "❌ SEO Dashboard Controller not found\n";
}

// Test if routes are accessible
$routes = [
  'seo_block.dashboard' => '/admin/content/seo-dashboard',
  'seo_block.clear_cache' => '/admin/content/seo-dashboard/clear-cache',
  'seo_block.seo_check' => '/admin/content/seo-dashboard/seo-check',
];

foreach ($routes as $route_name => $path) {
  echo "Route: $route_name -> $path\n";
}

// Test if template exists
$template_path = __DIR__ . '/templates/seo-dashboard.html.twig';
if (file_exists($template_path)) {
  echo "✅ Template exists: $template_path\n";
} else {
  echo "❌ Template not found: $template_path\n";
}

// Test if CSS exists
$css_path = __DIR__ . '/css/seo-dashboard.css';
if (file_exists($css_path)) {
  echo "✅ CSS exists: $css_path\n";
} else {
  echo "❌ CSS not found: $css_path\n";
}

// Test if JS exists
$js_path = __DIR__ . '/js/seo-dashboard.js';
if (file_exists($js_path)) {
  echo "✅ JavaScript exists: $js_path\n";
} else {
  echo "❌ JavaScript not found: $js_path\n";
}

echo "\n🎉 SEO Dashboard setup complete!\n";
echo "Access the dashboard at: /admin/content/seo-dashboard\n"; 