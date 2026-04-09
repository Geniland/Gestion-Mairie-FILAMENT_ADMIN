<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table etat_civil_requests
        if (Schema::hasTable('etat_civil_requests')) {
            Schema::table('etat_civil_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('etat_civil_requests', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('etat_civil_requests', 'commentaire_admin')) {
                    $table->text('commentaire_admin')->nullable()->after('status');
                }
            });
        } else {
            Schema::create('etat_civil_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reference')->unique();
                $table->string('nom');
                $table->string('telephone');
                $table->string('email');
                $table->enum('type', ['naissance', 'deces', 'mariage', 'rectification']);
                $table->text('details')->nullable();
                $table->json('files')->nullable();
                $table->enum('status', ['en_attente', 'validé', 'rejeté'])->default('en_attente');
                $table->text('commentaire_admin')->nullable();
                $table->string('document_url')->nullable();
                $table->timestamps();
            });
        }

        // Table services
        if (!Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table) {
                $table->id();
                $table->string('titre');
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->timestamps();
            });
        }

        // Table actualites
        if (!Schema::hasTable('actualites')) {
            Schema::create('actualites', function (Blueprint $table) {
                $table->id();
                $table->string('titre');
                $table->text('resume')->nullable();
                $table->longText('contenu')->nullable();
                $table->string('image')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        // Table public_taxes
        if (Schema::hasTable('public_taxes')) {
            Schema::table('public_taxes', function (Blueprint $table) {
                if (!Schema::hasColumn('public_taxes', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('public_taxes', 'commentaire_admin')) {
                    $table->text('commentaire_admin')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actualites');
        Schema::dropIfExists('services');
        // On ne supprime pas les colonnes des autres tables pour éviter de perdre des données existantes
    }
};
