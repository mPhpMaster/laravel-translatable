<?php

namespace mPhpMaster\Translatable;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use mPhpMaster\Translatable\Traits\Relationship;
use mPhpMaster\Translatable\Traits\Scopes;

/**
 * @property-read null|Model         $translation
 * @property-read Collection|Model[] $translations
 * @property-read string             $translationModel
 * @property-read string             $translationForeignKey
 * @property-read string             $localeKey
 * @property-read bool               $useTranslationFallback
 * @mixin Model
 */
trait Translatable
{
    use Scopes, Relationship;

    /**
     * @var null
     */
    protected static $autoload_translations = null;

    /**
     * @var bool
     */
    protected static $delete_translations_cascade = false;

    /**
     * @var
     */
    protected $default_locale;

    public static function bootTranslatable(): void
    {
        static::saved(function (Model $model) {
            /* @var Translatable $model */
            return $model->saveTranslations();
        });

        static::deleting(function (Model $model) {
            /* @var Translatable $model */
            if ( self::$delete_translations_cascade === true ) {
                return $model->deleteTranslations();
            }
        });
    }

    protected function saveTranslations(): bool
    {
        $saved = true;

        if ( !$this->relationLoaded('translations') ) {
            return $saved;
        }

        foreach ($this->translations as $translation) {
            if ( $saved && $this->isTranslationDirty($translation) ) {
                if ( !empty($connectionName = $this->getConnectionName()) ) {
                    $translation->setConnection($connectionName);
                }

                $translation->setAttribute($this->getTranslationRelationKey(), $this->getKey());
                $saved = $translation->save();
            }
        }

        return $saved;
    }

    protected function isTranslationDirty(Model $translation): bool
    {
        $dirtyAttributes = $translation->getDirty();
        unset($dirtyAttributes[ $this->getLocaleKey() ]);

        return count($dirtyAttributes) > 0;
    }

    /**
     * @internal will change to protected
     */
    public function getLocaleKey(): string
    {
        return $this->localeKey ?: config('laravel-translatable.locale_key', 'locale');
    }

    /**
     * @param string|array|null $locales The locales to be deleted
     */
    public function deleteTranslations($locales = null): void
    {
        if ( $locales === null ) {
            $translations = $this->translations()->get();
        } else {
            $locales = (array)$locales;
            $translations = $this->translations()->whereIn($this->getLocaleKey(), $locales)->get();
        }

        $translations->each->delete();

        // we need to manually "reload" the collection built from the relationship
        // otherwise $this->translations()->get() would NOT be the same as $this->translations
        $this->load('translations');
    }

    public static function defaultAutoloadTranslations(): void
    {
        self::$autoload_translations = null;
    }

    public static function disableAutoloadTranslations(): void
    {
        self::$autoload_translations = false;
    }

    public static function enableAutoloadTranslations(): void
    {
        self::$autoload_translations = true;
    }

    public static function disableDeleteTranslationsCascade(): void
    {
        self::$delete_translations_cascade = false;
    }

    public static function enableDeleteTranslationsCascade(): void
    {
        self::$delete_translations_cascade = true;
    }

    /**
     * @return mixed
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        if (
            self::$autoload_translations === false ||
            (!$this->relationLoaded('translations') && !$this->loadTranslationsWhenToArray() && is_null(self::$autoload_translations))
        ) {
            return $attributes;
        }

        $hiddenAttributes = $this->getHidden();

        foreach ($this->translatedAttributes as $field) {
            if ( in_array($field, $hiddenAttributes) ) {
                continue;
            }

            $attributes[ $field ] = $this->getAttributeOrFallback(null, $field);
        }

        return $attributes;
    }

    protected function loadTranslationsWhenToArray(): bool
    {
        return config('laravel-translatable.load_translations_when_to_array', true);
    }

    /**
     * @param string|null $locale
     * @param string      $attribute
     *
     * @return null
     */
    protected function getAttributeOrFallback(?string $locale, string $attribute)
    {
        $translation = $this->getTranslation($locale);

        if (
            (
                !$translation instanceof Model
                || $this->isEmptyTranslatableAttribute($attribute, $translation->$attribute)
            )
            && $this->usePropertyFallback()
        ) {
            $translation = $this->getTranslation($this->getFallbackLocale(), false);
        }

        if ( $translation instanceof Model ) {
            return $translation->$attribute;
        }

        return null;
    }

    /**
     * @param string|null $locale
     * @param bool|null   $withFallback
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getTranslation(?string $locale = null, bool $withFallback = null): ?Model
    {
        $configFallbackLocale = $this->getFallbackLocale();
        $locale = $locale ?: $this->locale();
        $withFallback = $withFallback ?? $this->useFallback();
        $fallbackLocale = $this->getFallbackLocale($locale);

        if ( $translation = $this->getTranslationByLocaleKey($locale) ) {
            return $translation;
        }

        if ( $withFallback && $fallbackLocale ) {
            if ( $translation = $this->getTranslationByLocaleKey($fallbackLocale) ) {
                return $translation;
            }

            if (
                is_string($configFallbackLocale)
                && $fallbackLocale !== $configFallbackLocale
                && $translation = $this->getTranslationByLocaleKey($configFallbackLocale)
            ) {
                return $translation;
            }
        }

        if ( $withFallback && $configFallbackLocale === null ) {
            $configuredLocales = $this->getLocalesHelper()->all();

            foreach ($configuredLocales as $configuredLocale) {
                if (
                    $locale !== $configuredLocale
                    && $fallbackLocale !== $configuredLocale
                    && $translation = $this->getTranslationByLocaleKey($configuredLocale)
                ) {
                    return $translation;
                }
            }
        }

        return null;
    }

    protected function getFallbackLocale(?string $locale = null): ?string
    {
        if ( $locale && $this->getLocalesHelper()->isLocaleCountryBased($locale) ) {
            if ( $fallback = $this->getLocalesHelper()->getLanguageFromCountryBasedLocale($locale) ) {
                return $fallback;
            }
        }

        return config('laravel-translatable.fallback_locale');
    }

    protected function getLocalesHelper(): Locales
    {
        return app(Locales::class);
    }

    protected function locale(): string
    {
        if ( $this->getDefaultLocale() ) {
            return $this->getDefaultLocale();
        }

        return $this->getLocalesHelper()->current();
    }

    public function getDefaultLocale(): ?string
    {
        return $this->default_locale;
    }

    /**
     * @param string|null $locale
     *
     * @return $this
     */
    public function setDefaultLocale(?string $locale)
    {
        $this->default_locale = $locale;

        return $this;
    }

    protected function useFallback(): bool
    {
        if ( isset($this->useTranslationFallback) && is_bool($this->useTranslationFallback) ) {
            return $this->useTranslationFallback;
        }

        return (bool)config('laravel-translatable.use_fallback');
    }

    protected function getTranslationByLocaleKey(string $key): ?Model
    {
        if (
            $this->relationLoaded('translation')
            && $this->translation
            && $this->translation->getAttribute($this->getLocaleKey()) == $key
        ) {
            return $this->translation;
        }

        return $this->translations->firstWhere($this->getLocaleKey(), $key);
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return bool
     */
    protected function isEmptyTranslatableAttribute(string $key, $value): bool
    {
        return empty($value);
    }

    protected function usePropertyFallback(): bool
    {
        return $this->useFallback() && config('laravel-translatable.use_property_fallback', false);
    }

    /**
     * @param array $attributes
     *
     * @return mixed
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $values) {
            if (
                is_array($values)
                && $this->getLocalesHelper()->has($key)
            ) {
                $this->getTranslationOrNew($key)->fill($values);
                unset($attributes[ $key ]);
            } else {
                [$attribute, $locale] = $this->getAttributeAndLocale($key);

                if (
                    $this->getLocalesHelper()->has($locale)
                    && $this->isTranslationAttribute($attribute)
                ) {
                    $this->getTranslationOrNew($locale)->fill([$attribute => $values]);
                    unset($attributes[ $key ]);
                }
            }
        }

        return parent::fill($attributes);
    }

    public function getTranslationOrNew(?string $locale = null): Model
    {
        $locale = $locale ?: $this->locale();

        if ( ($translation = $this->getTranslation($locale, false)) === null ) {
            $translation = $this->getNewTranslation($locale);
        }

        return $translation;
    }

    public function getNewTranslation(string $locale): Model
    {
        $modelName = $this->getTranslationModelName();

        /** @var Model $translation */
        $translation = new $modelName();
        $translation->setAttribute($this->getLocaleKey(), $locale);
        $this->translations->add($translation);

        return $translation;
    }

    protected function getAttributeAndLocale(string $key): array
    {
        if ( Str::contains($key, ':') ) {
            return explode(':', $key);
        }

        return [$key, $this->locale()];
    }

    public function isTranslationAttribute(string $key): bool
    {
        return in_array($key, $this->translatedAttributes);
    }

    /**
     * @param $key
     *
     * @return null
     */
    public function getAttribute($key)
    {
        [$attribute, $locale] = $this->getAttributeAndLocale($key);

        if ( $this->isTranslationAttribute($attribute) ) {
            if ( $this->getTranslation($locale) === null ) {
                return $this->getAttributeValue($attribute);
            }

            // If the given $attribute has a mutator, we push it to $attributes and then call getAttributeValue
            // on it. This way, we can use Eloquent's checking for Mutation, type casting, and
            // Date fields.
            if ( $this->hasGetMutator($attribute) ) {
                $this->attributes[ $attribute ] = $this->getAttributeOrFallback($locale, $attribute);

                return $this->getAttributeValue($attribute);
            }

            return $this->getAttributeOrFallback($locale, $attribute);
        }

        return parent::getAttribute($key);
    }

    public function getTranslationsArray(): array
    {
        $translations = [];

        foreach ($this->translations as $translation) {
            foreach ($this->translatedAttributes as $attr) {
                $translations[ $translation->{$this->getLocaleKey()} ][ $attr ] = $translation->{$attr};
            }
        }

        return $translations;
    }

    public function hasTranslation(?string $locale = null): bool
    {
        $locale = $locale ?: $this->locale();

        foreach ($this->translations as $translation) {
            if ( $translation->getAttribute($this->getLocaleKey()) == $locale ) {
                return true;
            }
        }

        return false;
    }

    public function replicateWithTranslations(array $except = null): Model
    {
        $newInstance = $this->replicate($except);

        unset($newInstance->translations);
        foreach ($this->translations as $translation) {
            $newTranslation = $translation->replicate();
            $newInstance->translations->add($newTranslation);
        }

        return $newInstance;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        [$attribute, $locale] = $this->getAttributeAndLocale($key);

        if ( $this->isTranslationAttribute($attribute) ) {
            $this->getTranslationOrNew($locale)->$attribute = $value;

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    public function translate(?string $locale = null, bool $withFallback = false): ?Model
    {
        return $this->getTranslation($locale, $withFallback);
    }

    public function translateOrDefault(?string $locale = null): ?Model
    {
        return $this->getTranslation($locale, true);
    }

    public function translateOrNew(?string $locale = null): Model
    {
        return $this->getTranslationOrNew($locale);
    }

    public function translateOrFail(string $locale): Model
    {
        return $this->getTranslationOrFail($locale);
    }

    public function getTranslationOrFail(string $locale): Model
    {
        if ( ($translation = $this->getTranslation($locale, false)) === null ) {
            throw (new ModelNotFoundException)->setModel($this->getTranslationModelName(), $locale);
        }

        return $translation;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->isTranslationAttribute($key) || parent::__isset($key);
    }
}
