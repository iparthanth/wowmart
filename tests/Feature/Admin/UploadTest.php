<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        $this->admin = User::factory()->admin()->create();
    }

    /** A 1x1 PNG, so tests need no image extension (GD is not installed here). */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    /**
     * A real file on disk. Laravel's UploadedFile::fake() reports a MIME type
     * guessed from the file NAME, which would let these tests pass without the
     * content check ever running; a real file is sniffed by its bytes.
     */
    private function file(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function png(string $name = 'photo.png', int $padBytes = 0): UploadedFile
    {
        return $this->file($name, base64_decode(self::PNG).str_repeat("\0", $padBytes));
    }

    public function test_every_column_pointing_at_uploads_is_known_to_the_delete_guard(): void
    {
        $inSchema = [];

        foreach (Schema::getTableListing(schemaQualified: false) as $table) {
            foreach (Schema::getForeignKeys($table) as $fk) {
                if ($fk['foreign_table'] === 'uploads') {
                    $inSchema[] = $table.'.'.$fk['columns'][0];
                }
            }
        }

        $known = array_map(fn ($ref) => implode('.', $ref), array_values(Upload::REFERENCES));

        // If this fails, a migration added a column referencing uploads:
        // add it to Upload::REFERENCES or deleting a file will blank it silently.
        $this->assertEqualsCanonicalizing($inSchema, $known);
    }

    public function test_an_image_is_stored_under_a_random_name(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/uploaded-files', ['files' => [$this->png('My Product Photo.png')]])
            ->assertRedirect('/admin/uploaded-files')
            ->assertSessionHas('status', '1 file added to the library.');

        $upload = Upload::sole();

        $this->assertSame('My Product Photo.png', $upload->file_original_name);
        $this->assertMatchesRegularExpression('#^uploads/all/[A-Za-z0-9]{40}\.png$#', $upload->file_name);
        $this->assertSame('image', $upload->type);
        $this->assertSame($this->admin->id, $upload->user_id);
        Storage::disk('uploads')->assertExists(substr($upload->file_name, strlen('uploads/')));
    }

    public function test_a_pdf_is_stored_as_a_document(): void
    {
        $this->actingAs($this->admin)->post('/admin/uploaded-files', [
            'files' => [$this->file('datasheet.pdf', "%PDF-1.4\n1 0 obj << >> endobj\ntrailer << >>\n%%EOF\n")],
        ])->assertSessionHasNoErrors();

        $this->assertSame('document', Upload::sole()->type);
    }

    public function test_svg_is_refused_because_it_can_carry_script(): void
    {
        $svg = $this->file('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $this->actingAs($this->admin)
            ->post('/admin/uploaded-files', ['files' => [$svg]])
            ->assertSessionHasErrors('files.0');

        $this->assertSame(0, Upload::count());
    }

    public function test_a_script_renamed_to_jpg_is_refused(): void
    {
        $fake = $this->file('photo.jpg', '<?php system($_GET["c"]); ?>');

        $this->actingAs($this->admin)
            ->post('/admin/uploaded-files', ['files' => [$fake]])
            ->assertSessionHasErrors('files.0');

        $this->assertSame(0, Upload::count());
    }

    public function test_a_file_over_the_limit_is_refused(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/uploaded-files', ['files' => [$this->png('big.png', 3 * 1024 * 1024)]])
            ->assertSessionHasErrors('files.0');
    }

    public function test_a_file_in_use_cannot_be_deleted(): void
    {
        $this->seed();
        $used = Upload::whereIn('id', Product::whereNotNull('thumbnail_img')->select('thumbnail_img'))->firstOrFail();

        $this->actingAs($this->admin)
            ->from('/admin/uploaded-files')
            ->delete("/admin/uploaded-files/{$used->id}")
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'product thumbnail'));

        $this->assertModelExists($used);
    }

    public function test_an_unused_file_is_deleted_from_the_library_and_the_disk(): void
    {
        $this->actingAs($this->admin)->post('/admin/uploaded-files', ['files' => [$this->png('spare.png')]]);
        $upload = Upload::sole();
        $path = substr($upload->file_name, strlen('uploads/'));

        $this->actingAs($this->admin)
            ->from('/admin/uploaded-files')
            ->delete("/admin/uploaded-files/{$upload->id}")
            ->assertSessionHas('status');

        $this->assertModelMissing($upload);
        Storage::disk('uploads')->assertMissing($path);
    }

    public function test_the_library_marks_which_files_are_in_use(): void
    {
        $this->seed();
        $this->actingAs($this->admin)->post('/admin/uploaded-files', ['files' => [$this->png('spare.png')]]);

        $this->actingAs($this->admin)->get('/admin/uploaded-files')
            ->assertOk()
            ->assertSee('spare.png')
            ->assertSee('Unused')
            ->assertSee('In use');
    }
}
