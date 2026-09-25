<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UploadApiTest extends TestCase
{
    use RefreshDatabase;

    /** A 1x1 PNG, so tests need no image extension (GD is not installed here). */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private const PDF = "%PDF-1.4\n1 0 obj << >> endobj\ntrailer << >>\n%%EOF\n";

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    /** A real file, sniffed by its bytes; UploadedFile::fake() would report a MIME type from the name. */
    private function file(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function upload(string $name, string $content): array
    {
        return $this->post('/api/v1/uploads', ['files' => [$this->file($name, $content)]], ['Accept' => 'application/json'])
            ->assertCreated()
            ->json('data.0');
    }

    public function test_an_image_and_a_pdf_are_uploaded_together(): void
    {
        $response = $this->post('/api/v1/uploads', ['files' => [
            $this->file('Board Photo.png', base64_decode(self::PNG)),
            $this->file('datasheet.pdf', self::PDF),
        ]], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.original_name', 'Board Photo.png')
            ->assertJsonPath('data.0.type', 'image')
            ->assertJsonPath('data.0.extension', 'png')
            ->assertJsonPath('data.0.in_use', false)
            ->assertJsonPath('data.1.type', 'document')
            ->assertJsonPath('data.1.extension', 'pdf');

        $image = Upload::findOrFail($response->json('data.0.id'));
        $response->assertJsonPath('data.0.url', $image->url())
            ->assertJsonPath('data.0.size_bytes', $image->file_size)
            ->assertJsonPath('data.0.size', $image->humanSize());
        Storage::disk('uploads')->assertExists(substr($image->file_name, strlen('uploads/')));
    }

    public function test_svg_and_a_disguised_script_are_refused(): void
    {
        $before = Upload::count();

        $this->post('/api/v1/uploads', ['files' => [
            $this->file('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            $this->file('photo.jpg', '<?php system($_GET["c"]); ?>'),
        ]], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['files.0', 'files.1']);

        $this->postJson('/api/v1/uploads', [])->assertUnprocessable()->assertJsonValidationErrors('files');

        $this->assertSame($before, Upload::count());
    }

    public function test_the_list_filters_sorts_and_marks_files_in_use(): void
    {
        $pdf = $this->upload('wiring-guide.pdf', self::PDF);

        $this->getJson('/api/v1/uploads?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', Upload::count())
            ->assertJsonPath('data.0.id', $pdf['id']) // newest first by default
            ->assertJsonStructure(['data' => [['id', 'original_name', 'url', 'type', 'extension', 'size_bytes', 'size', 'in_use', 'created_at']], 'links', 'meta']);

        $this->getJson('/api/v1/uploads?search=wiring')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.original_name', 'wiring-guide.pdf');

        $documents = $this->getJson('/api/v1/uploads?type=document&per_page=100')->assertOk()->json('data');
        $this->assertSame(['document'], array_values(array_unique(array_column($documents, 'type'))));

        $this->getJson('/api/v1/uploads?sort=oldest')->assertJsonPath('data.0.id', Upload::min('id'));

        $thumb = Product::whereNotNull('thumbnail_img')->value('thumbnail_img');
        $inUse = collect($this->getJson('/api/v1/uploads?per_page=100')->json('data'))->pluck('in_use', 'id');
        $this->assertTrue($inUse[$thumb]);
        $this->assertFalse($inUse[$pdf['id']]);
    }

    public function test_show_lists_where_a_file_is_used(): void
    {
        $thumb = Product::whereNotNull('thumbnail_img')->value('thumbnail_img');

        $usages = $this->getJson("/api/v1/uploads/{$thumb}")
            ->assertOk()
            ->assertJsonPath('data.id', $thumb)
            ->assertJsonPath('data.in_use', true)
            ->json('data.usages');

        $this->assertContains('product thumbnail', array_column($usages, 'label'));

        $spare = $this->upload('spare.png', base64_decode(self::PNG));
        $this->getJson("/api/v1/uploads/{$spare['id']}")
            ->assertOk()
            ->assertJsonPath('data.in_use', false)
            ->assertJsonPath('data.usages', []);
    }

    public function test_a_file_in_use_cannot_be_deleted(): void
    {
        $thumb = Product::whereNotNull('thumbnail_img')->value('thumbnail_img');

        $this->deleteJson("/api/v1/uploads/{$thumb}")
            ->assertStatus(409)
            ->assertJsonPath('message', fn (string $m) => str_contains($m, 'product thumbnail') && str_contains($m, 'Replace it there first'));

        $this->assertDatabaseHas('uploads', ['id' => $thumb]);
    }

    public function test_an_unused_file_is_deleted_from_the_library_and_the_disk(): void
    {
        $spare = $this->upload('spare.png', base64_decode(self::PNG));
        $path = substr(Upload::findOrFail($spare['id'])->file_name, strlen('uploads/'));

        $this->deleteJson("/api/v1/uploads/{$spare['id']}")->assertNoContent();

        $this->assertDatabaseMissing('uploads', ['id' => $spare['id']]);
        Storage::disk('uploads')->assertMissing($path);
        $this->getJson("/api/v1/uploads/{$spare['id']}")->assertNotFound();
    }
}
