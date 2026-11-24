<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_default_1gb_storage_limit(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(1024, $user->storage_limit_mb);
        $this->assertEquals(1024 * 1024 * 1024, $user->limiteEspacioBytes());
        $this->assertEquals('1 GB', $user->limiteEspacioTexto());
    }

    public function test_panel_displays_assigned_storage_info(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('panel'));

        $response->assertStatus(200);
        $response->assertSee('Almacenamiento asignado');
        $response->assertSee('1 GB');
    }

    public function test_upload_blocked_when_storage_limit_exceeded(): void
    {
        Storage::fake('nas');

        $user = User::factory()->create([
            'storage_limit_mb' => 1, // 1 MB limit
        ]);

        // Create a 2 MB file (exceeds 1 MB limit)
        $file = UploadedFile::fake()->create('large_doc.pdf', 2048); // 2048 KB = 2 MB

        $response = $this->actingAs($user)->post(route('archivos.subir'), [
            'archivo' => $file,
            'espacio' => 'privado',
        ]);

        $response->assertSessionHasErrors('archivo');
        Storage::disk('nas')->assertMissing('usuario_'.$user->id.'/large_doc.pdf');
    }

    public function test_upload_succeeds_when_within_storage_limit(): void
    {
        Storage::fake('nas');

        $user = User::factory()->create([
            'storage_limit_mb' => 1024,
        ]);

        $file = UploadedFile::fake()->create('doc.pdf', 100);

        $response = $this->actingAs($user)->post(route('archivos.subir'), [
            'archivo' => $file,
            'espacio' => 'privado',
        ]);

        $response->assertSessionHas('success');
        Storage::disk('nas')->assertExists('usuario_'.$user->id.'/doc.pdf');
    }

    public function test_upload_blocked_when_single_file_exceeds_50mb(): void
    {
        Storage::fake('nas');

        $user = User::factory()->create([
            'storage_limit_mb' => 1024,
        ]);

        // 51201 KB = 50.001 MB (exceeds 50 MB single-file limit)
        $file = UploadedFile::fake()->create('huge_file.zip', 52000);

        $response = $this->actingAs($user)->post(route('archivos.subir'), [
            'archivo' => $file,
            'espacio' => 'privado',
        ]);

        $response->assertSessionHasErrors('archivo');
        Storage::disk('nas')->assertMissing('usuario_'.$user->id.'/huge_file.zip');
    }

    public function test_get_on_archivos_subir_redirects_to_panel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/archivos/subir');

        $response->assertRedirect(route('panel'));
    }

    public function test_upload_pptx_file_succeeds_and_renders_custom_icon(): void
    {
        Storage::fake('nas');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('presentacion.pptx', 2048);

        $response = $this->actingAs($user)->post(route('archivos.subir'), [
            'archivo' => $file,
            'espacio' => 'privado',
        ]);

        $response->assertSessionHas('success');
        Storage::disk('nas')->assertExists('usuario_'.$user->id.'/presentacion.pptx');

        $panelResponse = $this->actingAs($user)->get(route('panel'));
        $panelResponse->assertStatus(200);
        $panelResponse->assertSee('presentacion.pptx');
        $panelResponse->assertSee('bi-file-earmark-ppt-fill');
        $panelResponse->assertSee('text-warning');
        $panelResponse->assertSee('pptx');
    }

    public function test_delete_file_flashes_eliminado_message(): void
    {
        Storage::fake('nas');

        $user = User::factory()->create();

        Storage::disk('nas')->put('usuario_'.$user->id.'/test.txt', 'contenido de prueba');

        $response = $this->actingAs($user)->delete(route('archivos.eliminar'), [
            'archivo' => 'test.txt',
            'espacio' => 'privado',
        ]);

        $response->assertSessionHas('eliminado');
        Storage::disk('nas')->assertMissing('usuario_'.$user->id.'/test.txt');
    }
}
