<?php

namespace App\View\Composers;

use App\Models\SiteSetting;
use Illuminate\View\View;

class SiteSettingComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $settings = SiteSetting::first();
        
        // If no settings exist, create default or use fallback
        if (!$settings) {
            $settings = new SiteSetting([
                'hero_title_text' => 'Penerimaan Peserta Didik Baru Online 2025/2026',
                'hero_subtitle_text' => 'SMK Muhammadiyah 1 Sangatta Utara membuka pendaftaran siswa baru.',
                'hero_image_path' => 'hero-bg.jpg',
                'cta_button_label' => 'Daftar Sekarang',
                'cta_button_url' => '/daftar',
                'requirements_markdown' => "1. Mengisi formulir pendaftaran\n2. Pas foto ukuran 3x4 (2 lembar)",
                'faq_items_json' => [],
                'timeline_items_json' => [],
            ]);
        }
        
        $view->with('settings', $settings);
    }
}
