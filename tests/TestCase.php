<?php

namespace mPhpMaster\Translatable\Tests;

use mPhpMaster\Translatable\TranslatableServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();

        $this->withFactories(realpath(__DIR__.'/factories'));
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('laravel-translatable.locales', ['el', 'en', 'fr', 'de', 'id', 'en-GB', 'en-US', 'de-DE', 'de-CH']);
    }

    protected function getPackageProviders($app)
    {
        return [
            TranslatableServiceProvider::class,
        ];
    }

    protected function createTables(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('country_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained();
            $table->string('name');
            $table->string('locale')->index();

            $table->unique(['country_id', 'locale']);
        });

        Schema::create('vegetables', function (Blueprint $table) {
            $table->increments('identity');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        Schema::create('vegetable_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vegetable_identity')->constrained();
            $table->string('name')->nullable();
            $table->string('locale')->index();

            $table->unique(['vegetable_identity', 'locale']);
        });

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('person_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained();
            $table->string('name');
            $table->string('locale')->index();

            $table->unique(['person_id', 'locale']);
        });
    }
}
