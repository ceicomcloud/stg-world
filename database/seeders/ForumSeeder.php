<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Forum\ForumCategory;
use App\Models\Forum\Forum;
use App\Models\Forum\ForumTopic;
use App\Models\Forum\ForumPost;
use App\Models\User;
use Carbon\Carbon;

class ForumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ===== CATÉGORIE GÉNÉRAL =====
        $generalCategory = ForumCategory::create([
            'name' => 'Général',
            'description' => 'Informations générales et règles',
            'sort_order' => 1,
            'is_active' => true,
            'is_locked' => true
        ]);

        // Forum : Annonces
        $announcesForum = Forum::create([
            'category_id' => $generalCategory->id,
            'name' => 'Annonces',
            'description' => 'Annonces officielles et informations importantes',
            'sort_order' => 1,
            'is_active' => true,
            'is_locked' => true
        ]);

        // Forum : Règlement
        $rulesForum = Forum::create([
            'category_id' => $generalCategory->id,
            'name' => 'Règlement',
            'description' => 'Règles du jeu et du forum',
            'sort_order' => 2,
            'is_active' => true,
            'is_locked' => true
        ]);

        // ===== CATÉGORIE DÉVELOPPEMENT =====
        $developmentCategory = ForumCategory::create([
            'name' => 'Développement',
            'description' => 'Suggestions et améliorations du jeu',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Forum : Suggestion
        $suggestionForum = Forum::create([
            'category_id' => $developmentCategory->id,
            'name' => 'Suggestion',
            'description' => 'Proposez vos idées pour améliorer le jeu',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Sous-forum : En attente
        $pendingSuggestionsForum = Forum::create([
            'category_id' => $developmentCategory->id,
            'parent_id' => $suggestionForum->id,
            'name' => 'En attente',
            'description' => 'Suggestions en cours d\'examen',
            'sort_order' => 1,
            'is_active' => true,
            'is_locked' => true
        ]);

        // Sous-forum : Ajoutées
        $addedSuggestionsForum = Forum::create([
            'category_id' => $developmentCategory->id,
            'parent_id' => $suggestionForum->id,
            'name' => 'Ajoutées',
            'description' => 'Suggestions acceptées et implémentées',
            'sort_order' => 2,
            'is_active' => true,
            'is_locked' => true
        ]);

        // Sous-forum : Refusées
        $rejectedSuggestionsForum = Forum::create([
            'category_id' => $developmentCategory->id,
            'parent_id' => $suggestionForum->id,
            'name' => 'Refusées',
            'description' => 'Suggestions refusées avec explications',
            'sort_order' => 3,
            'is_active' => true,
            'is_locked' => true
        ]);

        // ===== CATÉGORIE AIDE =====
        $helpCategory = ForumCategory::create([
            'name' => 'Aide',
            'description' => 'Support et assistance aux joueurs',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Forum : Questions
        $questionsForum = Forum::create([
            'category_id' => $helpCategory->id,
            'name' => 'Questions',
            'description' => 'Posez vos questions sur le jeu',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Forum : Tutoriels
        $tutorialsForum = Forum::create([
            'category_id' => $helpCategory->id,
            'name' => 'Tutoriels',
            'description' => 'Guides et tutoriels pour les joueurs',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // ===== CATÉGORIE BUG ET ERREURS =====
        $bugCategory = ForumCategory::create([
            'name' => 'Bug et erreurs',
            'description' => 'Signalement de bugs et problèmes techniques',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        // Forum : Bug d'affichage
        $displayBugsForum = Forum::create([
            'category_id' => $bugCategory->id,
            'name' => 'Bug d\'affichage',
            'description' => 'Problèmes d\'affichage et d\'interface',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Forum : Bug avec ou sans message d'erreurs
        $generalBugsForum = Forum::create([
            'category_id' => $bugCategory->id,
            'name' => 'Bug avec ou sans message d\'erreurs',
            'description' => 'Bugs fonctionnels et erreurs système',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // ===== CATÉGORIE COMMUNAUTÉ =====
        $communityCategory = ForumCategory::create([
            'name' => 'Communautée',
            'description' => 'Espace de discussion et d\'interaction entre joueurs',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        // Forum : Alliance
        $allianceForum = Forum::create([
            'category_id' => $communityCategory->id,
            'name' => 'Alliance',
            'description' => 'Recrutement et discussions d\'alliances',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Forum : Déclaration de guerre
        $warDeclarationForum = Forum::create([
            'category_id' => $communityCategory->id,
            'name' => 'Déclaration de guerre',
            'description' => 'Déclarations de guerre officielles',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Forum : Top choc
        $topChoiceForum = Forum::create([
            'category_id' => $communityCategory->id,
            'name' => 'Top choc',
            'description' => 'Classements et performances des joueurs',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Forum : Discussions
        $discussionsForum = Forum::create([
            'category_id' => $communityCategory->id,
            'name' => 'Discussions',
            'description' => 'Discussions générales entre joueurs',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        // Forum : Jeux
        $gamesForum = Forum::create([
            'category_id' => $communityCategory->id,
            'name' => 'Jeux',
            'description' => 'Mini-jeux et divertissements',
            'sort_order' => 5,
            'is_active' => true,
        ]);
    }
}
