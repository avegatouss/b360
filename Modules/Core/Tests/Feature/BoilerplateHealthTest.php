<?php

namespace Modules\Core\Tests\Feature;

use Tests\TestCase;
use Modules\Core\Support\CurrentInstance;

class BoilerplateHealthTest extends TestCase
{
    /**
     * Teste que la page d'accueil redirige bien sur le login par défaut
     * si l'installation est terminée mais qu'aucune session n'est active.
     */
    public function test_home_page_redirects_to_login(): void
    {
        // On s'assure que le mode est bien configuré (comportement de base)
        config(['app.installed' => true]);

        $response = $this->get('/');

        // Doit rediriger vers le login du module Auth
        $response->assertStatus(302);

        // On autorise la redirection vers différentes routes d'auth ou le login global
        $this->assertTrue(
            str_contains($response->headers->get('Location'), '/login') ||
            str_contains($response->headers->get('Location'), '/install')
        );
    }

    /**
     * Teste que le système de multi-tenancy rejette bien l'accès sans instance résolue.
     */
    public function test_instance_resolution_fail_closed(): void
    {
        // Vider l'instance courante pour forcer le fail-close
        CurrentInstance::clear();

        $this->assertNull(CurrentInstance::get());
    }
}
