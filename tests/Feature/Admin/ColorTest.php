<?php

namespace Tests\Feature\Admin;

use App\Models\Color;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ColorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function color(string $name): Color
    {
        return Color::where('name', $name)->firstOrFail();
    }

    public function test_the_list_shows_each_colour_with_a_swatch(): void
    {
        $this->actingAs($this->admin)->get('/admin/colors')
            ->assertOk()
            ->assertSee('Add a colour')
            ->assertSee('style="background: #D7263D"', false)
            ->assertSeeInOrder(['Black', 'Blue', 'Green', 'Red', 'White'])
            ->assertViewHas('colors', fn ($page) => $page->total() === 5);
    }

    public function test_a_colour_code_is_stored_uppercase(): void
    {
        $this->actingAs($this->admin)->post('/admin/colors', ['name' => 'Teal', 'code' => '#0a9396'])
            ->assertRedirect('/admin/colors')->assertSessionHasNoErrors();

        $this->assertSame('#0A9396', $this->color('Teal')->code);
    }

    public static function badCodes(): array
    {
        return [
            'no hash' => ['0A9396'],
            'short form' => ['#09F'],
            'not hex' => ['#GG9396'],
            'with alpha' => ['#0A939680'],
            'named colour' => ['teal'],
            'array' => [['#0A9396']],
        ];
    }

    #[DataProvider('badCodes')]
    public function test_a_code_that_is_not_six_hex_digits_is_refused(mixed $code): void
    {
        $this->actingAs($this->admin)->post('/admin/colors', ['name' => 'Teal', 'code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertSame(5, Color::count());
    }

    public function test_a_code_is_unique_in_any_case(): void
    {
        $this->actingAs($this->admin)->post('/admin/colors', ['name' => 'Jet', 'code' => '#1b1b1b'])
            ->assertSessionHasErrors('code');

        $black = $this->color('Black');
        $this->actingAs($this->admin)->put("/admin/colors/{$black->id}", ['name' => 'Jet black', 'code' => '#1b1b1b'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Jet black', $black->fresh()->name);
    }

    public function test_a_colour_in_use_cannot_be_deleted_or_recoded(): void
    {
        $red = $this->color('Red');
        Product::orderBy('id')->firstOrFail()->forceFill(['colors' => ['#d7263d']])->save(); // any case counts

        $this->actingAs($this->admin)->from('/admin/colors')->delete("/admin/colors/{$red->id}")
            ->assertRedirect('/admin/colors')
            ->assertSessionHas('error', '1 product offers this colour; remove it from them first.');
        $this->assertNotNull($red->fresh());

        $this->actingAs($this->admin)->from("/admin/colors/{$red->id}/edit")
            ->put("/admin/colors/{$red->id}", ['name' => 'Red', 'code' => '#FF0000'])
            ->assertSessionHas('error');
        $this->assertSame('#D7263D', $red->fresh()->code);

        // Renaming keeps the code, so products are unaffected.
        $this->actingAs($this->admin)->put("/admin/colors/{$red->id}", ['name' => 'Crimson', 'code' => '#D7263D'])
            ->assertSessionHasNoErrors()->assertSessionMissing('error');
        $this->assertSame('Crimson', $red->fresh()->name);
    }

    public function test_an_unused_colour_is_deleted(): void
    {
        Product::orderBy('id')->firstOrFail()->forceFill(['colors' => ['#D7263D']])->save();
        $blue = $this->color('Blue');

        $this->actingAs($this->admin)->delete("/admin/colors/{$blue->id}")
            ->assertRedirect('/admin/colors')->assertSessionHas('status');

        $this->assertNull($blue->fresh());
        $this->assertSame(4, Color::count());
    }

    public function test_the_edit_page_opens(): void
    {
        $this->actingAs($this->admin)->get("/admin/colors/{$this->color('Green')->id}/edit")
            ->assertOk()->assertSee('value="#2e8b57"', false);
    }
}
