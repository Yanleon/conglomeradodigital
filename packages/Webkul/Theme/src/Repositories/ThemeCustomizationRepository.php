<?php

namespace Webkul\Theme\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\Core\Eloquent\Repository;
use Webkul\Theme\Contracts\ThemeCustomization;

class ThemeCustomizationRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ThemeCustomization::class;
    }

    /**
     * Update the specified theme
     *
     * @param  array  $data
     * @param  int  $id
     */
    public function update($data, $id): ThemeCustomization
    {
        $locale = core()->getRequestedLocaleCode();

        if ($data['type'] == 'static_content') {
            $data[$locale]['options']['html'] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data[$locale]['options']['html']);
            $data[$locale]['options']['css'] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data[$locale]['options']['css']);
        }

        if (($data['type'] ?? '') === 'footer_content' && isset($data[$locale]['options'])) {
            // Ensure we never attempt to persist UploadedFile objects in translated options.
            $logoFile = $data[$locale]['options']['logo_file'] ?? null;

            if ($logoFile instanceof UploadedFile) {
                try {
                    $manager = new ImageManager();

                    $path = 'theme/'.$id.'/'.Str::random(40).'.webp';

                    Storage::put($path, $manager->make($logoFile)->encode('webp'));
                } catch (\Exception $e) {
                    // Keep previous logo (if any) on failure.
                    session()->flash('error', $e->getMessage());
                    unset($data[$locale]['options']['logo_file']);
                    $logoFile = null;
                }

                if ($logoFile instanceof UploadedFile) {
                    $data[$locale]['options']['logo'] = 'storage/'.$path;
                }
            }

            unset($data[$locale]['options']['logo_file']);
        }

        if (($data['type'] ?? '') === 'popup_widget' && isset($data[$locale]['options'])) {
            // Banner upload + keep options JSON safe.
            $bannerFile = $data[$locale]['options']['banner_file'] ?? null;

            if ($bannerFile instanceof UploadedFile) {
                try {
                    $manager = new ImageManager();

                    $path = 'theme/'.$id.'/'.Str::random(40).'.webp';

                    Storage::put($path, $manager->make($bannerFile)->encode('webp'));

                    $data[$locale]['options']['banner'] = 'storage/'.$path;
                } catch (\Exception $e) {
                    session()->flash('error', $e->getMessage());
                }
            }

            unset($data[$locale]['options']['banner_file']);

            // HTML images upload (multiple).
            $htmlImages = $data[$locale]['options']['html_images'] ?? [];
            if (! is_array($htmlImages)) {
                $htmlImages = [];
            }

            $htmlImageFiles = $data[$locale]['options']['html_image_files'] ?? null;
            if (is_array($htmlImageFiles)) {
                foreach ($htmlImageFiles as $file) {
                    if (! ($file instanceof UploadedFile)) {
                        continue;
                    }

                    try {
                        $manager = new ImageManager();

                        $path = 'theme/'.$id.'/'.Str::random(40).'.webp';

                        Storage::put($path, $manager->make($file)->encode('webp'));

                        $htmlImages[] = 'storage/'.$path;
                    } catch (\Exception $e) {
                        session()->flash('error', $e->getMessage());
                    }
                }
            }

            $data[$locale]['options']['html_images'] = array_values(array_unique(array_filter($htmlImages)));
            unset($data[$locale]['options']['html_image_files']);

            // Avoid script injections.
            if (isset($data[$locale]['options']['html']) && is_string($data[$locale]['options']['html'])) {
                $data[$locale]['options']['html'] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data[$locale]['options']['html']);
            }

            if (isset($data[$locale]['options']['css']) && is_string($data[$locale]['options']['css'])) {
                $data[$locale]['options']['css'] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data[$locale]['options']['css']);

                // If user pasted <style>...</style>, keep only inner CSS.
                $data[$locale]['options']['css'] = preg_replace('/^\s*<style\b[^>]*>|<\/style>\s*$/i', '', $data[$locale]['options']['css']);
            }
        }

        if (in_array($data['type'], ['image_carousel', 'services_content'])) {
            unset($data[$locale]['options']);
        }

        $theme = parent::update($data, $id);

        if (in_array($data['type'], ['image_carousel', 'services_content'])) {
            $this->uploadImage(request()->all(), $theme);
        }

        return $theme;
    }

    /**
     * Upload images
     *
     * @return void|string
     */
    public function uploadImage(array $data, ThemeCustomization $theme)
    {
        $locale = core()->getRequestedLocaleCode();

        if (isset($data[$locale]['deleted_sliders'])) {
            foreach ($data[$locale]['deleted_sliders'] as $slider) {
                Storage::delete(str_replace('storage/', '', $slider['image']));
            }
        }

        if (! isset($data[$locale]['options'])) {
            return;
        }

        $options = [];

        foreach ($data[$locale]['options'] as $image) {
            if (isset($image['service_icon'])) {
                $options['services'][] = [
                    'service_icon' => $image['service_icon'],
                    'description'  => $image['description'],
                    'title'        => $image['title'],
                ];
            } elseif ($image['image'] instanceof UploadedFile) {
                try {
                    $manager = new ImageManager();

                    $path = 'theme/'.$theme->id.'/'.Str::random(40).'.webp';

                    Storage::put($path, $manager->make($image['image'])->encode('webp'));
                } catch (\Exception $e) {
                    session()->flash('error', $e->getMessage());

                    return redirect()->back();
                }

                if (($data['type'] ?? '') == 'static_content') {
                    return Storage::url($path);
                }

                $options['images'][] = [
                    'image' => 'storage/'.$path,
                    'link'  => $image['link'],
                    'title' => $image['title'],
                ];
            } else {
                $options['images'][] = $image;
            }
        }

        $translatedModel = $theme->translate($locale);
        $translatedModel->options = $options ?? [];
        $translatedModel->theme_customization_id = $theme->id;
        $translatedModel->save();
    }
}
