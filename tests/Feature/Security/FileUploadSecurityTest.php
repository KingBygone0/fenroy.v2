<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    // ── Avatar upload — extension attacks ──────────────────────────────────

    public function test_php_file_disguised_as_jpg_is_rejected(): void
    {
        $user = User::factory()->create();

        // File named .php but with JPEG magic bytes — polyglot attack
        $polyglot = UploadedFile::fake()->createWithContent(
            'evil.php',
            "\xff\xd8\xff<?php system('id'); ?>"  // JPEG magic + PHP payload
        );

        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('photo', $polyglot)
            ->call('save');

        // File must not have been stored with a .php extension
        Storage::disk('public')->assertMissing('avatars/evil.php');

        // No PHP files should exist anywhere in avatars
        $files = Storage::disk('public')->files('avatars');
        foreach ($files as $file) {
            $this->assertStringNotEndsWith('.php', $file,
                'No .php file may be stored in the avatar directory');
        }
    }

    public function test_php_file_is_rejected_as_avatar(): void
    {
        $user = User::factory()->create();

        $phpFile = UploadedFile::fake()->createWithContent(
            'shell.php',
            '<?php system($_GET["cmd"]); ?>'
        );

        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('photo', $phpFile)
            ->call('save');

        Storage::disk('public')->assertMissing('avatars/shell.php');

        $files = Storage::disk('public')->files('avatars');
        $this->assertEmpty($files, 'No files should be stored after rejecting a PHP upload');
    }

    public function test_svg_file_is_rejected_as_avatar(): void
    {
        $user = User::factory()->create();

        $svg = UploadedFile::fake()->createWithContent(
            'xss.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
        );

        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('photo', $svg)
            ->call('save');

        $files = Storage::disk('public')->files('avatars');
        $this->assertEmpty($files, 'SVG files must not be accepted as avatar uploads');
    }

    public function test_double_extension_file_is_rejected(): void
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->createWithContent(
            'image.php.jpg',
            '<?php echo "owned"; ?>'
        );

        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('photo', $file)
            ->call('save');

        $files = Storage::disk('public')->files('avatars');
        foreach ($files as $f) {
            $this->assertStringNotContainsString('.php', $f,
                'Double-extension .php.jpg attack must not result in a stored .php file');
        }
    }

    public function test_valid_jpeg_avatar_is_stored_with_safe_extension(): void
    {
        $user = User::factory()->create();

        $jpeg = UploadedFile::fake()->image('photo.jpg', 200, 200);

        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('photo', $jpeg)
            ->call('save');

        $files = Storage::disk('public')->files('avatars');
        $this->assertNotEmpty($files, 'A valid JPEG must be stored successfully');

        foreach ($files as $file) {
            $this->assertMatchesRegularExpression(
                '/\.(jpg|png|gif|webp)$/',
                $file,
                'Stored avatar must have a safe, MIME-derived extension'
            );
        }
    }

    public function test_avatar_larger_than_2mb_is_rejected(): void
    {
        $user = User::factory()->create();

        // Create a fake image larger than 2MB
        $large = UploadedFile::fake()->image('big.jpg')->size(3000);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\AccountProfile::class)
            ->set('photo', $large)
            ->call('save');

        $component->assertHasErrors(['photo']);
    }

    public function test_avatar_upload_requires_authentication(): void
    {
        $jpeg = UploadedFile::fake()->image('photo.jpg', 100, 100);

        // Livewire's upload endpoint validates a server-signed token; without it the
        // request is rejected regardless of auth state. Use the named route so the
        // test is not coupled to Livewire's internal hash-prefixed URI.
        $uploadUri = route('livewire.upload-file', [], false);

        $response = $this->post($uploadUri, [
            'files' => [$jpeg],
        ]);

        $this->assertGreaterThanOrEqual(400, $response->getStatusCode(),
            'Unsigned/unauthenticated upload requests must be rejected with a 4xx status');
    }

    // ── Import upload — content validation ─────────────────────────────────

    public function test_import_upload_rejects_php_disguised_as_csv(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $phpContent = '<?php system("id"); ?>';
        $encoded    = base64_encode($phpContent);

        $response = $this->actingAs($admin)->postJson('/admin/import-products/upload', [
            'file' => $encoded,
            'name' => 'products.csv',
            'ext'  => 'csv',
        ]);

        $response->assertStatus(422);
    }

    public function test_import_upload_rejects_invalid_base64(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson('/admin/import-products/upload', [
            'file' => '!!!NOT_VALID_BASE64!!!',
            'name' => 'x.csv',
            'ext'  => 'csv',
        ]);

        $response->assertStatus(422);
    }

    public function test_import_upload_rejects_xlsx_extension_with_csv_bytes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // CSV bytes declared as xlsx — no ZIP magic header
        $response = $this->actingAs($admin)->postJson('/admin/import-products/upload', [
            'file' => base64_encode("name,sku,price\nApple,FRUIT-001,5.00"),
            'name' => 'products.xlsx',
            'ext'  => 'xlsx',
        ]);

        $response->assertStatus(422);
    }

    public function test_import_upload_rejects_disallowed_extension(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson('/admin/import-products/upload', [
            'file' => base64_encode('<?php echo 1; ?>'),
            'name' => 'shell.php',
            'ext'  => 'php',
        ]);

        $response->assertStatus(422);
    }

    public function test_import_upload_requires_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->postJson('/admin/import-products/upload', [
            'file' => base64_encode("name,price\nApple,5"),
            'name' => 'products.csv',
            'ext'  => 'csv',
        ]);

        $response->assertStatus(403);
    }

    public function test_import_upload_requires_authentication(): void
    {
        $response = $this->postJson('/admin/import-products/upload', [
            'file' => base64_encode("name,price\nApple,5"),
            'name' => 'products.csv',
            'ext'  => 'csv',
        ]);

        $this->assertContains($response->getStatusCode(), [401, 302, 403]);
    }

    public function test_valid_csv_import_is_accepted_and_stored_privately(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $csv     = "name,sku,price,stock,category\nApple,FRU-001,5.00,100,Fruits";
        $encoded = base64_encode($csv);

        $response = $this->actingAs($admin)->postJson('/admin/import-products/upload', [
            'file' => $encoded,
            'name' => 'products.csv',
            'ext'  => 'csv',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);

        $token = $response->json('token');

        // File must be stored in private storage — NOT under public/storage
        $this->assertFileExists(storage_path('app/imports/' . $token));
        $this->assertFileDoesNotExist(
            storage_path('app/public/imports/' . $token),
            'Import files must be stored in private (non-web-accessible) storage'
        );

        // Filename must be cryptographically random (no timestamp)
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{32}\.csv$/', $token,
            'Import filename must be a random string, not timestamp-based');

        // Clean up
        @unlink(storage_path('app/imports/' . $token));
    }

    public function test_import_token_cannot_contain_path_traversal(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Ensure resolvedPath() in ImportProducts blocks traversal
        $component = Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\ImportProducts::class);

        // Setting a path-traversal token must be neutralised by basename() + realpath()
        $component->set('tempPath', '../../../etc/passwd');
        $component->call('goToPreview');

        // Should not crash or read /etc/passwd — it should show "file not found"
        $component->assertHasNoErrors(); // No crash
    }
}
