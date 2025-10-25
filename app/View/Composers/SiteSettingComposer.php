<?php

namespace App\View\Composers;

use App\Settings\SettingsRepositoryInterface;
use App\Models\Wave;
use Illuminate\View\View;

class SiteSettingComposer
{
    public function __construct(private readonly SettingsRepositoryInterface $settings)
    {
    }

    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $settingsRepo = $this->settings;

        $settings = (object) [
            'hero_title_text' => $settingsRepo->get('hero_title', 'Penerimaan Peserta Didik Baru Online 2025/2026'),
            'hero_subtitle_text' => $settingsRepo->get('hero_subtitle', 'Membuka pendaftaran siswa baru tahun ajaran 2025/2026'),
            'hero_image_path' => $settingsRepo->get('hero_image', ''),
            'cta_button_label' => $settingsRepo->get('cta_button_label', 'Daftar Sekarang'),
            'cta_button_url' => $settingsRepo->get('cta_button_url', '/daftar'),
            'requirements_markdown' => $settingsRepo->get('requirements_text', "1. Mengisi formulir pendaftaran\n2. Pas foto ukuran 3x4 (2 lembar)"),
            'faq_items_json' => json_decode($settingsRepo->get('faq_items', '[]'), true) ?: [],
            'timeline_items_json' => json_decode($settingsRepo->get('timeline_items', '[]'), true) ?: [],
        ];
        
        // Get all waves and categorize them
        $waves = Wave::orderBy('start_datetime', 'asc')->get();
        $now = now();
        
        $categorizedWaves = [
            'closed' => [],    // Gelombang yang sudah selesai atau tidak aktif
            'active' => [],    // Gelombang yang sedang berlangsung dan aktif
            'upcoming' => [],  // Gelombang yang akan datang dan aktif
        ];
        
        foreach ($waves as $wave) {
            // Priority: Check is_active first
            if (!$wave->is_active || $now->gt($wave->end_datetime)) {
                // Tidak aktif atau sudah lewat tanggal akhir
                $categorizedWaves['closed'][] = $wave;
            } elseif ($wave->is_active && $now->lt($wave->start_datetime)) {
                // Aktif tapi belum dimulai
                $categorizedWaves['upcoming'][] = $wave;
            } elseif ($wave->is_active && $now->gte($wave->start_datetime) && $now->lte($wave->end_datetime)) {
                // Aktif dan sedang berlangsung
                $categorizedWaves['active'][] = $wave;
            }
        }
        
        $view->with([
            'settings' => $settings,
            'waves' => $waves,
            'categorizedWaves' => $categorizedWaves,
        ]);
    }
}
