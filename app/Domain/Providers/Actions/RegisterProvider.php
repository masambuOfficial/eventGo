<?php

namespace App\Domain\Providers\Actions;

use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class RegisterProvider
{
    /**
     * Common suffixes stripped before duplicate-name matching, since MySQL has
     * no pg_trgm and normalised_name is the whole detection mechanism (architecture §11.3).
     */
    private const SUFFIXES = ['ltd', 'limited', 'llc', 'co', 'company', 'uganda', 'ug'];

    public function __invoke(User $user, array $data): Provider
    {
        if (Provider::where('owner_user_id', $user->id)->exists()) {
            throw new RuntimeException('This user already has a provider profile.');
        }

        $provider = new Provider([
            'business_name' => $data['business_name'],
            'normalised_name' => $this->normalise($data['business_name']),
            'slug' => $this->uniqueSlug($data['business_name']),
            'about' => $data['about'] ?? null,
            'base_district_id' => $data['base_district_id'] ?? null,
            'primary_phone_e164' => $data['primary_phone_e164'] ?? null,
        ]);

        $provider->owner_user_id = $user->id;
        $provider->save();

        return $provider;
    }

    private function normalise(string $name): string
    {
        $words = explode(' ', Str::of($name)->lower()->replaceMatches('/[^a-z0-9\s]/', '')->squish());

        $words = array_filter($words, fn (string $word) => ! in_array($word, self::SUFFIXES, true));

        return implode(' ', $words);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Provider::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
