<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Choice;

class AdminControllerTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_edit()
    {
        $questionId = 1;
        // 適切なダミーデータの作成
        $faker = Factory::create();
        $normal = [
            'name0' => $faker->word,
            'name1' => $faker->word,
            'name2' => $faker->word,
            'valid' => rand(0, 2),
        ];

        // 適切な入力内容の場合、/adminへのリダイレクトが行われる
        $response = $this->post("/admin/edit/{$questionId}", $normal);
        $response->assertRedirect('/admin');

        // DBに正常に書き込まれている事を確認
        foreach (range(0, 2) as $key) {
            $this->assertDatabaseHas(app(Choice::class)->getTable(), [
                'question_id' => $questionId,
                'name'        => $normal['name'.$key],
                'valid'       => $key == $normal['valid'] ? 1 : 0,
            ]);
        }


        // DBに存在しないIDが指定されたURLの場合、404を返す
        $response = $this->post('/admin/edit/99999');
        $response->assertStatus(404);


        // すべての項目が入力必須(必須ではない場合、エラーが出る)
        $response = $this->post('/admin/edit/1');
        $response->assertSessionHasErrors([
            'name0',
            'name1',
            'name2',
            'valid',
        ]);

        // 選択肢が20文字を超えていた場合エラー
        $response = $this->post('/admin/edit/1', array_merge($normal, [
            'name0' => Str::random('21'), // 21文字の文字列
            'name1' => Str::random('21'),
            'name2' => Str::random('21'),
        ]));
        $response->assertSessionHasErrors([
            'name0',
            'name1',
            'name2',
        ]);

        // validが0〜2以外の場合、エラー
        $response = $this->post('/admin/edit/1', array_merge($normal, ['valid' => 4]));
        $response->assertSessionHasErrors([
            'valid',
        ]);
    }
}